<?php
// Add this at the very top to catch any output
ob_start();
if (headers_sent($filename, $linenum)) {
    die("Headers already sent in $filename on line $linenum");
}

require_once 'config.php';
session_start();

// Redirect if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Handle status updates - this should be the first thing after authentication
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for both POST parameter and AJAX header
    $isStatusUpdate = isset($_POST['update_status']) || 
                     (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && 
                      isset($_POST['order_id']) && 
                      isset($_POST['status']));

    if ($isStatusUpdate) {
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        error_log("Received status update request: " . print_r($_POST, true));
        
        try {
            // Validate inputs
            $required = ['status', 'order_id'];
            foreach ($required as $field) {
                if (!isset($_POST[$field])) {
                    throw new Exception("Missing $field parameter");
                }
            }

            $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($_POST['status'], $allowed_statuses)) {
                throw new Exception('Invalid status value');
            }

            $order_id = (int)$_POST['order_id'];
            $status = $_POST['status'];
            
            // Verify order exists
            $stmt = $db->prepare("SELECT id FROM orders WHERE id = ? LIMIT 1");
            $stmt->execute([$order_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Order not found');
            }

            // Update status
            $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            if (!$stmt->execute([$status, $order_id])) {
                throw new Exception('Database update failed');
            }
            
            // Set proper headers before output
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Status updated',
                'new_status' => $status
            ]);
            exit;
            
        } catch (Exception $e) {
            // Clean any potential output before error response
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    } // This closes the if ($isStatusUpdate) block
}

// Then handle other AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unsupported AJAX request']);
    exit;
}



// Get all orders
try {
    $stmt = $db->query("SELECT id, user_id, customer_name, customer_email, customer_phone, 
                   delivery_address, special_instructions, payment_method, total_amount, 
                   status, payment_status, created_at 
                   FROM orders ORDER BY created_at DESC");
    $orders = $stmt->fetchAll();
    
    // Get order items for each order
    foreach ($orders as &$order) {
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $order['items'] = $stmt->fetchAll();
    }
    unset($order);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Management - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
    <style>
        .status-select {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: white;
            margin-right: 8px;
            font-size: 14px;
        }

        .btn-small {
            padding: 6px 12px;
            background-color: #4a6bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-small:hover {
            background-color: #3a5bef;
        }

        .btn-small:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .btn-view {
            padding: 6px 12px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-view:hover {
            background-color: #5a6268;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 600px;
        }
        
        .close-modal {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            text-transform: capitalize;
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
        
        .status-badge.cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                    <li class="active"><a href="admin_orders.php"><i class="ri-shopping-bag-line"></i> Orders</a></li>
                    <li><a href="admin_reservations.php"><i class="ri-calendar-line"></i> Reservations</a></li>
                    <li><a href="admin_reports.php"><i class="ri-bar-chart-line"></i> Reports</a></li>
                    <li><a href="admin_users.php"><i class="ri-user-line"></i> Users</a></li>
                    <li><a href="admin_logout.php"><i class="ri-logout-box-line"></i> Log Out</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-content">
            <header class="admin-header">
                <h1>Order Management</h1>
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
            
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?><br>
                                <small><?= htmlspecialchars($order['customer_email']) ?></small>
                            </td>
                            <td>
                                <ul class="item-list">
                                    <?php foreach ($order['items'] as $item): ?>
                                    <li><?= htmlspecialchars($item['item_name']) ?> × <?= $item['quantity'] ?> (₹<?= number_format($item['item_price'], 2) ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <form class="status-form" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn-small">Update</button>
                                </form>
                            </td>
                            <td><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <button class="btn-view" data-id="<?= $order['id'] ?>">View</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // AJAX status updates
        document.querySelectorAll('.status-form').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Create FormData object and explicitly append all fields
                const formData = new FormData(form);
                const submitBtn = form.querySelector('[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                try {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i>';
                    
                    // Manually create the URL-encoded string
                    const params = new URLSearchParams();
                    params.append('order_id', formData.get('order_id'));
                    params.append('status', formData.get('status'));
                    params.append('update_status', '1'); // Explicitly add this parameter
                    
                    const response = await fetch('admin_orders.php', {
                        method: 'POST',
                        body: params,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    });
                    
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => null);
                        throw new Error(errorData?.error || `HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.error || 'Update failed');
                    }
                    
                    // Visual feedback
                    form.closest('tr').style.backgroundColor = '#e8f5e9';
                    setTimeout(() => form.closest('tr').style.backgroundColor = '', 1000);
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
        // View order details - implement a modal for this
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', async () => {
                const orderId = btn.dataset.id;
                btn.disabled = true;
                btn.innerHTML = '<i class="ri-loader-4-line spin"></i>';
                
                try {
                    // Fetch order details
                    const response = await fetch(`get_order_details.php?id=${orderId}`);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Create and show a modal with order details
                        const modal = document.createElement('div');
                        modal.className = 'modal';
                        modal.innerHTML = `
                            <div class="modal-content">
                                <span class="close-modal">&times;</span>
                                <h2>Order #${orderId} Details</h2>
                                <div class="order-details">
                                    <p><strong>Customer:</strong> ${data.order.customer_name}</p>
                                    <p><strong>Email:</strong> ${data.order.customer_email}</p>
                                    <p><strong>Phone:</strong> ${data.order.customer_phone}</p>
                                    <p><strong>Address:</strong> ${data.order.delivery_address}</p>
                                    <p><strong>Date:</strong> ${new Date(data.order.created_at).toLocaleString()}</p>
                                    <p><strong>Status:</strong> <span class="status-badge ${data.order.status}">${data.order.status}</span></p>
                                    <p><strong>Payment:</strong> ${data.order.payment_method} (${data.order.payment_status})</p>
                                    ${data.order.special_instructions ? `<p><strong>Instructions:</strong> ${data.order.special_instructions}</p>` : ''}
                                    <h3>Items:</h3>
                                    <ul class="item-list">
                                        ${data.order.items.map(item => `
                                            <li>${item.item_name} × ${item.quantity} (₹${item.item_price.toFixed(2)})</li>
                                        `).join('')}
                                    </ul>
                                    <p><strong>Total:</strong> ₹${data.order.total_amount.toFixed(2)}</p>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(modal);
                        modal.style.display = 'block';
                        
                        // Close modal handler
                        modal.querySelector('.close-modal').addEventListener('click', () => {
                            modal.remove();
                        });
                        
                        // Close when clicking outside
                        modal.addEventListener('click', (e) => {
                            if (e.target === modal) {
                                modal.remove();
                            }
                        });
                    } else {
                        throw new Error(data.message || 'Failed to load order details');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error loading order details: ' + error.message);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = 'View';
                }
            });
        });
    </script>
</body>
</html>