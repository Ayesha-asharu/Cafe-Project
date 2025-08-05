<?php
require_once 'config.php';
session_start();

// At the very top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add debug logging for session validation
error_log('Session validation check: ' . print_r([
    'logged_in' => isset($_SESSION['admin_logged_in']),
    'ip_match' => ($_SESSION['admin_ip'] === $_SERVER['REMOTE_ADDR']),
    'ua_match' => ($_SESSION['admin_user_agent'] === $_SERVER['HTTP_USER_AGENT'])
], true));

// Check if admin is logged in with proper validation
if (!isset($_SESSION['admin_logged_in']) || 
    $_SESSION['admin_logged_in'] !== true ||
    $_SESSION['admin_ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['admin_user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    
    // Redirect to login if not valid
    header('Location: admin_login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set default JSON header
    header('Content-Type: application/json');
    
    if (isset($_POST['add_item'])) {
        // Add new menu item
        // In the POST handling section for add_item:
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0.00;
        $category = in_array($_POST['category'], ['beverage', 'cuisine', 'desserts']) 
                ? $_POST['category'] 
                : 'cuisine';
        $image_url = !empty($_POST['image_url']) ? filter_var($_POST['image_url'], FILTER_VALIDATE_URL) : '';

        try {
            $stmt = $db->prepare("INSERT INTO menu_items (name, description, price, discount, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $discount, $category, $image_url]);
            
            $newItemId = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
            $stmt->execute([$newItemId]);
            $newItem = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'item' => $newItem,
                'message' => 'Item added successfully!'
            ]);
            exit;
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error adding menu item: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    elseif (isset($_POST['delete_item'])) {
        $id = $_POST['id'] ?? 0;
        
        try {
            $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item deleted successfully!',
                'id' => $id
            ]);
            exit;
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error deleting menu item: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    elseif (isset($_POST['edit_item'])) {
        $id = $_POST['id'] ?? 0;
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $price = (float)$_POST['price'];
        $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0.00;
        $category = in_array($_POST['category'], ['beverage', 'cuisine', 'desserts']) 
                ? $_POST['category'] 
                : 'cuisine';
        $image_url = !empty($_POST['image_url']) ? filter_var($_POST['image_url'], FILTER_VALIDATE_URL) : '';

        try {
            $stmt = $db->prepare("UPDATE menu_items SET name = ?, description = ?, price = ?, discount = ?, category = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $discount, $category, $image_url, $id]);
                    
            $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $updatedItem = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'item' => $updatedItem,
                'message' => 'Item updated successfully!'
            ]);
            exit;
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating menu item: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    // If no valid action found
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

// Get all menu items for non-AJAX requests
try {
    $stmt = $db->query("SELECT * FROM menu_items ORDER BY id, name");
    $menuItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading menu items: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Menu Management - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: absolute;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color:rgba(0, 0, 0, 0.09);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: black;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .btn-primary {
            background-color: #4a6bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #3a5bef;
        }
        
        .btn-cancel {
            background-color: #f0f0f0;
            color: #333;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn-cancel:hover {
            background-color: #e0e0e0;
        }
        
        /* Item thumbnail */
        .item-thumbnail {
            max-width: 50px;
            max-height: 50px;
            border-radius: 4px;
        }
        
        /* Delete button styles */
        .btn-delete {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        
        .btn-delete i {
            pointer-events: none;
        }
        
        .delete-form {
            display: inline;
        }
        
        /* Alert styles */
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 300px;
            box-shadow: 0 4px 12px #000000;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        }
        .alert-success { background-color: #4CAF50; }
        .alert-error { background-color: #F44336; }
        .close-alert {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-left: 15px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Edit modal specific */
        #edit-item-modal {
            display: none;
            position: absolute;
            z-index: 1001;
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
                    <li class="active"><a href="admin_menu.php"><i class="ri-restaurant-line"></i> Menu Management</a></li>
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
                <h1>Menu Management</h1>
                <div class="admin-user">
                    <span>Welcome, Admin</span>
                </div>
            </header>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <div class="admin-actions">
                <button id="add-item-btn" class="btn-primary">
                    <i class="ri-add-line"></i> Add New Item
                </button>
            </div>
            
            <!-- Add Item Modal -->
            <div id="add-item-modal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Add New Menu Item</h2>
                    <form id="add-item-form" method="POST">
                        <div class="form-group">
                            <label for="item-name">Item Name *</label>
                            <input type="text" id="item-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="item-description">Description *</label>
                            <textarea id="item-description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="item-price">Price (₹) *</label>
                            <input type="number" id="item-price" name="price" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="item-discount">Discount (₹) *</label>
                            <input type="number" id="item-discount" name="discount" step="0.01" min="0" max="<?= $price ?>" value="0">
                        </div>
                        <div class="form-group">
                            <label for="item-category">Category *</label>
                            <select id="item-category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="beverage">Beverage</option>
                                <option value="cuisine">Cuisine</option>
                                <option value="desserts">Desserts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="item-image">Image URL (Optional)</label>
                            <input type="text" id="item-image" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel">Cancel</button>
                            <button type="submit" name="add_item" class="btn-primary">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Edit Item Modal -->
            <div id="edit-item-modal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h2>Edit Menu Item</h2>
                    <form id="edit-item-form" method="POST">
                        <input type="hidden" name="id" id="edit-item-id">
                        <input type="hidden" name="edit_item" value="1">
                        <div class="form-group">
                            <label for="edit-item-name">Item Name *</label>
                            <input type="text" id="edit-item-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-item-description">Description *</label>
                            <textarea id="edit-item-description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit-item-price">Price (₹) *</label>
                            <input type="number" id="edit-item-price" name="price" step="0.01" min="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-item-discount">Discount (₹) *</label>
                            <input type="number" id="edit-item-discount" name="discount" step="0.01" min="0" max="<?= $price ?>" value="0">
                        </div>
                        <div class="form-group">
                            <label for="edit-item-category">Category *</label>
                            <select id="edit-item-category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="beverage">Beverage</option>
                                <option value="cuisine">Cuisine</option>
                                <option value="desserts">Desserts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-item-image">Image URL (Optional)</label>
                            <input type="text" id="edit-item-image" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-cancel">Cancel</button>
                            <button type="submit" class="btn-primary">Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="menu-items-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuItems as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td>₹<?= number_format($item['discount'], 2) ?></td>
                            <td><?= ucfirst($item['category']) ?></td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-thumbnail">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button class="btn-edit" data-id="<?= $item['id'] ?>" 
                                        data-name="<?= htmlspecialchars($item['name']) ?>" 
                                        data-description="<?= htmlspecialchars($item['description']) ?>" 
                                        data-price="<?= $item['price'] ?>" 
                                        data-category="<?= $item['category'] ?>" 
                                        data-image="<?= htmlspecialchars($item['image_url']) ?>">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                <form method="POST" class="delete-form">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="delete_item" class="btn-delete">
                                        <i class="ri-delete-bin-line"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('add-item-modal');
        const addBtn = document.getElementById('add-item-btn');
        const closeModal = document.querySelector('.close-modal');
        const cancelBtn = document.querySelector('.btn-cancel');
        const form = document.getElementById('add-item-form');
        const tbody = document.querySelector('.menu-items-table tbody');

        // Show/hide modal
        addBtn.addEventListener('click', () => modal.style.display = 'block');
        closeModal.addEventListener('click', closeModalHandler);
        cancelBtn.addEventListener('click', closeModalHandler);
        window.addEventListener('click', (e) => e.target === modal && closeModalHandler());

        function closeModalHandler() {
            modal.style.display = 'none';
            form.reset();
        }

        // Function to add a new row to the table
        function addTableRow(item) {
    // Convert price and discount to numbers
    const price = typeof item.price === 'string' ? parseFloat(item.price) : item.price;
    const discount = item.discount !== undefined && item.discount !== null ? 
                    Number(item.discount) : 0;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${item.id}</td>
        <td>${escapeHtml(item.name)}</td>
        <td>${escapeHtml(item.description)}</td>
        <td>₹${price.toFixed(2)}</td>
        <td>₹${discount.toFixed(2)}</td>
        <td>${capitalize(item.category)}</td>
        <td>
            ${item.image_url ? `<img src="${escapeHtml(item.image_url)}" alt="${escapeHtml(item.name)}" class="item-thumbnail">` : 'No Image'}
        </td>
        <td class="actions">
            <button class="btn-edit" data-id="${item.id}" 
                    data-name="${escapeHtml(item.name)}" 
                    data-description="${escapeHtml(item.description)}" 
                    data-price="${price}" 
                    data-discount="${discount}"
                    data-category="${item.category}" 
                    data-image="${escapeHtml(item.image_url || '')}">
                <i class="ri-edit-line"></i> Edit
            </button>
            <form method="POST" class="delete-form">
                <input type="hidden" name="id" value="${item.id}">
                <button type="submit" name="delete_item" class="btn-delete">
                    <i class="ri-delete-bin-line"></i> Delete
                </button>
            </form>
        </td>
    `;
    
    // Add event listeners
    row.querySelector('.btn-edit').addEventListener('click', editItemHandler);
    row.querySelector('.delete-form').addEventListener('submit', deleteItemHandler);
    
    tbody.appendChild(row);
}
        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Adding...';
                
                const formData = new FormData(form);
                formData.append('add_item', '1');
                
                const response = await fetch('admin_menu.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
        
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error('Server returned unexpected response: ' + text);
                }
                
                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to add item');
                }
                
                // Add new row to table
                addTableRow(data.item);
                
                // Show success and reset
                showAlert('success', data.message);
                closeModalHandler();
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('error', error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Edit modal functionality
        const editModal = document.getElementById('edit-item-modal');
        const editForm = document.getElementById('edit-item-form');
        
        // Edit item handler
        function editItemHandler() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;
            const price = this.dataset.price;
            const category = this.dataset.category;
            const image = this.dataset.image;
            
            // Populate the edit form
            document.getElementById('edit-item-id').value = id;
            document.getElementById('edit-item-name').value = name;
            document.getElementById('edit-item-description').value = description;
            document.getElementById('edit-item-price').value = price;
            document.getElementById('edit-item-category').value = category;
            document.getElementById('edit-item-image').value = image || '';
            
            // Show the edit modal
            editModal.style.display = 'block';
        }
        
        // Edit form submission
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = editForm.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Updating...';
                
                const formData = new FormData(editForm);
                
                const response = await fetch('admin_menu.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
        
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error('Server returned unexpected response: ' + text);
                }
                
                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to update item');
                }
                
                // Update the row in the table
                updateTableRow(data.item);
                
                // Show success and reset
                showAlert('success', data.message);
                editModal.style.display = 'none';
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('error', error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        // Function to update a row in the table
        function updateTableRow(item) {
        // Convert price and discount to numbers if they're strings
        const price = typeof item.price === 'string' ? parseFloat(item.price) : item.price;
        const discount = item.discount !== undefined && item.discount !== null ? 
                (typeof item.discount === 'string' ? parseFloat(item.discount) : item.discount) : 
                0.00;
        
        // Find the row with matching ID
        const rows = document.querySelectorAll('.menu-items-table tbody tr');
        for (const row of rows) {
            if (row.cells[0].textContent == item.id) {
                // Update the row data
                row.cells[1].textContent = item.name;
                row.cells[2].textContent = item.description;
                row.cells[3].textContent = '₹' + price.toFixed(2);
                row.cells[4].textContent = '₹' + discount.toFixed(2);
                row.cells[5].textContent = capitalize(item.category);
                
                // Update image
                const imgCell = row.cells[6]; // Changed from 5 to 6 to target the correct cell
                if (item.image_url) {
                    imgCell.innerHTML = `<img src="${escapeHtml(item.image_url)}" alt="${escapeHtml(item.name)}" class="item-thumbnail">`;
                } else {
                    imgCell.textContent = 'No Image';
                }
                
                // Update the edit button data attributes
                const editBtn = row.querySelector('.btn-edit');
                editBtn.dataset.name = item.name;
                editBtn.dataset.description = item.description;
                editBtn.dataset.price = price;
                editBtn.dataset.discount = discount;
                editBtn.dataset.category = item.category;
                editBtn.dataset.image = item.image_url || '';
                
                break;
            }
        }
    }
        // Delete item handler
        function deleteItemHandler(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this item?')) {
                return;
            }
            
            const form = e.target;
            const formData = new FormData(form);
            formData.append('delete_item', '1');
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Deleting...';
            
            fetch('admin_menu.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned unexpected response: ' + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to delete item');
                }
                
                // Remove the row from the table
                const row = form.closest('tr');
                if (row) {
                    row.remove();
                }
                
                showAlert('success', data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        // Helper functions
        function showAlert(type, message) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <span>${message}</span>
                <button class="close-alert">&times;</button>
            `;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
            alert.querySelector('.close-alert').addEventListener('click', () => alert.remove());
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function capitalize(text) {
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        // Close edit modal when clicking X or cancel button
        editModal.querySelector('.close-modal').addEventListener('click', () => {
            editModal.style.display = 'none';
        });
        
        editModal.querySelector('.btn-cancel').addEventListener('click', () => {
            editModal.style.display = 'none';
        });
        
        // Close edit modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === editModal) {
                editModal.style.display = 'none';
            }
        });

        // Attach event listeners to existing edit buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', editItemHandler);
        });

        // Attach event listeners to existing delete forms
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', deleteItemHandler);
        });
    </script>
</body>
</html>