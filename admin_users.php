<?php
require_once 'config.php';
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_POST['user_id']]);
            $success = "User deleted successfully!";
        } catch (PDOException $e) {
            $error = "Error deleting user: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_user'])) {
        try {
            $name = sanitizeInput($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
            $userType = in_array($_POST['user_type'], ['customer', 'staff']) ? $_POST['user_type'] : 'customer';
            
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, user_type = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $userType, $_POST['user_id']]);
            $success = "User updated successfully!";
        } catch (PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    }
}

// Get all users
try {
    $stmt = $db->query("SELECT id, name, email, phone, user_type, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
    <style>
        .user-type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        .user-type-customer {
            background-color: #d4edda;
            color: #155724;
        }
        .user-type-staff {
            background-color: #cce5ff;
            color: #004085;
        }
        .action-form {
            display: inline-block;
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
                    <li><a href="admin_reservations.php"><i class="ri-calendar-line"></i> Reservations</a></li>
                    <li><a href="admin_reports.php"><i class="ri-bar-chart-line"></i> Reports</a></li>
                    <li class="active"><a href="admin_users.php"><i class="ri-user-line"></i> Users</a></li>
                    <li><a href="admin_logout.php"><i class="ri-logout-box-line"></i> Log Out</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>User Management</h1>
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
            
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['phone']) ?></td>
                            <td>
                                <span class="user-type-badge user-type-<?= $user['user_type'] ?>">
                                    <?= ucfirst($user['user_type']) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button class="btn-edit" data-id="<?= $user['id'] ?>"
                                        data-name="<?= htmlspecialchars($user['name']) ?>"
                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-phone="<?= htmlspecialchars($user['phone']) ?>"
                                        data-user-type="<?= $user['user_type'] ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                <form class="action-form" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn-small btn-delete">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Edit User Modal -->
            <div id="edit-user-modal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Edit User</h2>
                    <form id="edit-user-form" method="POST">
                        <input type="hidden" name="user_id" id="edit-user-id">
                        <input type="hidden" name="update_user" value="1">
                        <div class="form-group">
                            <label for="edit-user-name">Name</label>
                            <input type="text" id="edit-user-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-user-email">Email</label>
                            <input type="email" id="edit-user-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-user-phone">Phone</label>
                            <input type="text" id="edit-user-phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-user-type">User Type</label>
                            <select id="edit-user-type" name="user_type" required>
                                <option value="customer">Customer</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Edit user functionality
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit-user-id').value = this.dataset.id;
                document.getElementById('edit-user-name').value = this.dataset.name;
                document.getElementById('edit-user-email').value = this.dataset.email;
                document.getElementById('edit-user-phone').value = this.dataset.phone;
                document.getElementById('edit-user-type').value = this.dataset.userType;
                
                document.getElementById('edit-user-modal').style.display = 'block';
            });
        });

        // Modal close functionality
        document.querySelector('#edit-user-modal .close-modal').addEventListener('click', function() {
            document.getElementById('edit-user-modal').style.display = 'none';
        });

        document.querySelector('#edit-user-modal .btn-cancel').addEventListener('click', function() {
            document.getElementById('edit-user-modal').style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === document.getElementById('edit-user-modal')) {
                document.getElementById('edit-user-modal').style.display = 'none';
            }
        });
    </script>
</body>
</html>