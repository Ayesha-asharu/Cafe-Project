<?php
require_once 'config.php';
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Get stats for dashboard
try {
    // Total menu items
    $stmt = $db->query("SELECT COUNT(*) FROM menu_items");
    $menuItemCount = $stmt->fetchColumn();
    
    // Total orders
    $stmt = $db->query("SELECT COUNT(*) FROM orders");
    $orderCount = $stmt->fetchColumn();
    
    // Today's reservations
    $stmt = $db->query("SELECT COUNT(*) FROM bookings WHERE DATE(date) = CURDATE()");
    $todaysReservations = $stmt->fetchColumn();
    
    // Registered users (if you have a users table)
    $userCount = 0;
    if ($db->query("SHOW TABLES LIKE 'users'")->rowCount() > 0) {
        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
    }
    
    // Get recent orders (limit to 5)
    $stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
    
    // Get order items for each order
    foreach ($recentOrders as &$order) {
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
    }
    unset($order);
    
    // Get recent reservations (limit to 5)
    $stmt = $db->query("SELECT * FROM bookings ORDER BY date DESC LIMIT 5");
    $recentReservations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 1rem;
        }
        .stat-card p {
            font-size: 2rem;
            margin: 0.5rem 0;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-card i {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 2rem;
            color: rgba(0, 0, 0, 0.1);
        }
        .recent-data {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .data-section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .data-section h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 500;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-badge.processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-badge.completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-badge.confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .item-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .item-list li {
            margin-bottom: 0.25rem;
        }
        .view-all {
            text-align: right;
            margin-top: 1rem;
        }
        .view-all a {
            color: #4a6bff;
            text-decoration: none;
        }
        .view-all a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2><i class="ri-cup-line"></i> Admin Panel</h2>
            <nav>
                <ul>
                    <li class="active"><a href="admin_dashboard.php"><i class="ri-dashboard-line"></i> Dashboard</a></li>
                    <li><a href="admin_menu.php"><i class="ri-restaurant-line"></i> Menu Management</a></li>
                    <li><a href="admin_orders.php"><i class="ri-shopping-bag-line"></i> Orders</a></li>
                    <li><a href="admin_reservations.php"><i class="ri-calendar-line"></i> Reservations</a></li>
                    <li><a href="admin_reports.php"><i class="ri-bar-chart-line"></i> Reports</a></li>
                    <li><a href="admin_users.php"><i class="ri-user-line"></i> Users</a></li>
                    <li><a href="admin_logout.php"><i class="ri-logout-box-line"></i> Log Out</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Menu Items</h3>
                    <p><?= $menuItemCount ?></p>
                    <i class="ri-restaurant-line"></i>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?= $orderCount ?></p>
                    <i class="ri-shopping-bag-line"></i>
                </div>
                <div class="stat-card">
                    <h3>Today's Reservations</h3>
                    <p><?= $todaysReservations ?></p>
                    <i class="ri-calendar-line"></i>
                </div>
                <div class="stat-card">
                    <h3>Registered Users</h3>
                    <p><?= $userCount ?></p>
                    <i class="ri-user-line"></i>
                </div>
            </div>
            
            <div class="recent-data">
                <section class="data-section">
                    <h2>Recent Orders</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge <?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="admin_orders.php">View All Orders →</a>
                    </div>
                </section>
                
                <section class="data-section">
                    <h2>Recent Reservations</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>People</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReservations as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><?= htmlspecialchars($booking['name']) ?></td>
                                <td><?= date('M j, Y', strtotime($booking['date'])) ?></td>
                                <td><?= $booking['quantity'] ?></td>
                                <td>
                                    <span class="status-badge confirmed">
                                        Confirmed
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="view-all">
                        <a href="admin_reservations.php">View All Reservations →</a>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>