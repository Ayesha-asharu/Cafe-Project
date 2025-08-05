<?php
require_once 'config.php';
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Initialize variables
$reservations = [];
$error = null;
$success = null;

// Handle status updates and deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_reservation'])) {
        try {
            $stmt = $db->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['reservation_id']]);
            
            $success = "Reservation updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating reservation: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_reservation'])) {
        try {
            $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$_POST['reservation_id']]);
            
            $success = "Reservation deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting reservation: " . $e->getMessage();
        }
    }
}

// Get all reservations
try {
    $stmt = $db->query("SELECT * FROM bookings ORDER BY date DESC, created_at DESC");
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reservation Management - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-completed {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .action-form {
            display: inline-block;
            margin-right: 5px;
        }
        .status-select {
            padding: 4px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-right: 5px;
        }
        .btn-small {
            padding: 4px 8px;
            font-size: 0.85rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-update {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .reservation-date {
            white-space: nowrap;
        }
        .no-reservations {
            padding: 20px;
            text-align: center;
            color: #666;
        }
    </style>
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
                    <li class="active"><a href="admin_reservations.php"><i class="ri-calendar-line"></i> Reservations</a></li>
                    <li><a href="admin_reports.php"><i class="ri-bar-chart-line"></i> Reports</a></li>
                    <li><a href="admin_users.php"><i class="ri-user-line"></i> Users</a></li>
                    <li><a href="admin_logout.php"><i class="ri-logout-box-line"></i> Log Out</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Reservation Management</h1>
                <div class="admin-user">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <div class="reservations-table">
                <?php if (empty($reservations)): ?>
                    <div class="no-reservations">
                        <i class="ri-calendar-todo-line" style="font-size: 48px; margin-bottom: 10px;"></i>
                        <p>No reservations found</p>
                    </div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>People</th>
                            <th>Date</th>
                            <th>Booked On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= $reservation['id'] ?></td>
                            <td><?= htmlspecialchars($reservation['name']) ?></td>
                            <td><?= htmlspecialchars($reservation['contact']) ?></td>
                            <td><?= htmlspecialchars($reservation['quantity']) ?></td>
                            <td class="reservation-date"><?= date('M j, Y', strtotime($reservation['date'])) ?></td>
                            <td><?= date('M j, Y H:i', strtotime($reservation['created_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $reservation['status'] ?? 'pending' ?>">
                                    <?= ucfirst($reservation['status'] ?? 'pending') ?>
                                </span>
                            </td>
                            <td>
                                <form class="action-form" method="POST">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?= ($reservation['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= ($reservation['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="cancelled" <?= ($reservation['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        <option value="completed" <?= ($reservation['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                    <button type="submit" name="update_reservation" class="btn-small btn-update">Update</button>
                                </form>
                                <form class="action-form" method="POST" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="delete_reservation" class="btn-small btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Add some interactive functionality
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const badge = this.closest('tr').querySelector('.status-badge');
                badge.textContent = this.options[this.selectedIndex].text;
                badge.className = 'status-badge status-' + this.value;
            });
        });
    </script>
</body>
</html>