<?php
require_once 'config.php';
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Initialize variables
$report_type = $_GET['type'] ?? 'orders';
$period = $_GET['period'] ?? 'daily';
$report_data = [];
$error = null;

// Validate inputs
$valid_types = ['orders', 'reservations'];
$valid_periods = ['daily', 'weekly', 'monthly', 'yearly'];

if (!in_array($report_type, $valid_types)) {
    $report_type = 'orders';
}

if (!in_array($period, $valid_periods)) {
    $period = 'daily';
}

try {
    if ($report_type === 'orders') {
        // Build base query for orders
        $query = "SELECT o.* FROM orders o WHERE ";
        
        switch ($period) {
            case 'daily':
                $query .= "DATE(o.created_at) = CURDATE()";
                break;
            case 'weekly':
                $query .= "YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'monthly':
                $query .= "YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())";
                break;
            case 'yearly':
                $query .= "YEAR(o.created_at) = YEAR(CURDATE())";
                break;
        }
        
        $query .= " ORDER BY o.created_at DESC";
        
        $stmt = $db->query($query);
        $report_data = $stmt->fetchAll();
        
        // Get order items for each order
        foreach ($report_data as &$order) {
            $stmt = $db->prepare("
                SELECT oi.item_name, oi.quantity, oi.item_price 
                FROM order_items oi 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order['id']]);
            $order['items'] = $stmt->fetchAll();
        }
        unset($order); // Break the reference
        
    } else {
        // Reservations report
        $query = "SELECT * FROM bookings WHERE ";
        
        switch ($period) {
            case 'daily':
                $query .= "DATE(date) = CURDATE()";
                break;
            case 'weekly':
                $query .= "YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'monthly':
                $query .= "YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())";
                break;
            case 'yearly':
                $query .= "YEAR(date) = YEAR(CURDATE())";
                break;
        }
        
        $query .= " ORDER BY date ASC";
        
        $stmt = $db->query($query);
        $report_data = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2><i class="ri-cup-line"></i> Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php"><i class="ri-dashboard-line"></i> Dashboard</a></li>
                    <li><a href="admin_menu.php"><i class="ri-restaurant-line"></i> Menu Management</a></li>
                    <li><a href="admin_orders.php"><i class="ri-shopping-bag-line"></i> Orders</a></li>
                    <li><a href="admin_reservations.php"><i class="ri-calendar-line"></i> Reservations</a></li>
                    <li class="active"><a href="admin_reports.php"><i class="ri-bar-chart-line"></i> Reports</a></li>
                    <li><a href="admin_users.php"><i class="ri-user-line"></i> Users</a></li>
                    <li><a href="admin_logout.php"><i class="ri-logout-box-line"></i> Log Out</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Reports</h1>
                <div class="admin-user">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="report-controls">
                <div class="report-type-tabs">
                    <a href="?type=orders&period=<?= $period ?>" class="<?= $report_type === 'orders' ? 'active' : '' ?>">
                        <i class="ri-shopping-bag-line"></i> Orders
                    </a>
                    <a href="?type=reservations&period=<?= $period ?>" class="<?= $report_type === 'reservations' ? 'active' : '' ?>">
                        <i class="ri-calendar-line"></i> Reservations
                    </a>
                </div>
                
                <div class="period-selector">
                    <a href="?type=<?= $report_type ?>&period=daily" class="<?= $period === 'daily' ? 'active' : '' ?>">Daily</a>
                    <a href="?type=<?= $report_type ?>&period=weekly" class="<?= $period === 'weekly' ? 'active' : '' ?>">Weekly</a>
                    <a href="?type=<?= $report_type ?>&period=monthly" class="<?= $period === 'monthly' ? 'active' : '' ?>">Monthly</a>
                    <a href="?type=<?= $report_type ?>&period=yearly" class="<?= $period === 'yearly' ? 'active' : '' ?>">Yearly</a>
                </div>
            </div>
            
            <div class="report-summary">
                <h2><?= ucfirst($report_type) ?> Report (<?= ucfirst($period) ?>)</h2>
                <p>Total <?= $report_type ?>: <?= is_countable($report_data) ? count($report_data) : 0 ?></p>
                
                <?php if ($report_type === 'orders'): ?>
                    <p>Total Revenue: ₹<?= 
                        number_format(array_reduce($report_data ?? [], function($sum, $order) {
                            return $sum + ($order['total_amount'] ?? 0);
                        }, 0), 2) 
                    ?></p>
                <?php endif; ?>
            </div>
            
            <div class="report-data">
                <?php if ($report_type === 'orders'): ?>
                    <!-- Orders Report Table -->
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($report_data ?? []) as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?? '' ?></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? '') ?></td>
                                <td>
                                    <?php foreach (($order['items'] ?? []) as $item): ?>
                                        <?= htmlspecialchars($item['item_name'] ?? '') ?> × <?= $item['quantity'] ?? 0 ?> (₹<?= number_format($item['item_price'] ?? 0, 2) ?>)<br>
                                    <?php endforeach; ?>
                                </td>
                                <td>₹<?= number_format($order['total_amount'] ?? 0, 2) ?></td>
                                <td><span class="status-badge <?= $order['status'] ?? '' ?>"><?= ucfirst($order['status'] ?? '') ?></span></td>
                                <td><?= isset($order['created_at']) ? date('M j, Y H:i', strtotime($order['created_at'])) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <!-- Reservations Report Table -->
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Date</th>
                                <th>People</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($report_data ?? []) as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?? '' ?></td>
                                <td><?= htmlspecialchars($booking['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($booking['contact'] ?? '') ?></td>
                                <td><?= isset($booking['date']) ? date('M j, Y', strtotime($booking['date'])) : '' ?></td>
                                <td><?= $booking['quantity'] ?? '' ?></td>
                                <td><span class="status-badge confirmed">Confirmed</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <?php if (empty($report_data)): ?>
                    <div class="no-data">
                        <i class="ri-information-line"></i>
                        <p>No <?= $report_type ?> data found for this <?= $period ?> period.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>