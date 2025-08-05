<?php
require_once 'config.php';

// Debug output
error_log('Session ID: ' . session_id());
error_log('Cart contents: ' . print_r($_SESSION['cart'] ?? [], true));

// Calculate total items in cart (summing quantities)
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

$loggedIn = isset($_SESSION['user']);
// In cart.php:
$cartTotal = 0;
$discountTotal = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $itemDiscount = (isset($item['discount']) ? $item['discount'] : 0) * $item['quantity'];
        $cartTotal += $itemTotal;
        $discountTotal += $itemDiscount;
    }
}

$subtotal = $cartTotal;
$finalTotal = $subtotal - $discountTotal + 50; // Adding delivery fee
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart | Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
     <!-- Welcome Popup -->
<div class="modal welcome-popup" id="welcome-popup">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Welcome to Eat & Enjoy!</h2>
        <p>Please select how you'd like to continue:</p>
        <div class="welcome-buttons">
            <button id="admin-login-btn">Admin Login</button>
            <button id="customer-login-btn">Customer Login</button>
        </div>
    </div>
</div>

<!-- Auth Popups -->
<div class="modal" id="login-popup">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Log In</h2>
        <form class="auth-form" id="login-form">
            <div class="form-group">
                <label for="login-email">Email</label>
                <input type="email" id="login-email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" required>
            </div>
            <input type="hidden" id="user-type" name="user-type" value="customer">
            <button type="submit">Log In</button>
            <div class="switch-auth">
                Don't have an account? <span id="switch-to-signup">Sign Up</span>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="signup-popup">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Sign Up</h2>
        <form class="auth-form" id="signup-form">
            <div class="form-group">
                <label for="signup-name">Full Name</label>
                <input type="text" id="signup-name" required>
            </div>
            <div class="form-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" required>
            </div>
            <div class="form-group">
                <label for="signup-phone">Phone Number</label>
                <input type="tel" id="signup-phone" required>
            </div>
            <div class="form-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" required minlength="6">
            </div>
            <button type="submit">Sign Up</button>
            <div class="switch-auth">
                Already have an account? <span id="switch-to-login">Log In</span>
            </div>
        </form>
    </div>
</div>
    
    <header>
        <div class="name">
            <i class="ri-cup-line"><h1>Eat & Enjoy</h1></i>
        </div>
        <div class="linkedpages">
            <ul>
                <a href="index.php"><li>Home</li></a>
                <a href="menu.php"><li>Menu</li></a>
                <a href="about.php"><li>About Us</li></a>
                <a href="cart.php">
                    <i class="ri-shopping-cart-2-line"></i>
                    <li>Cart (<span class="cart-count"><?= $cartCount ?></span>)</li>
                </a>
            </ul>
            <div class="auth-buttons">
                <?php if ($loggedIn): ?>
                    <span id="user-greeting">Hello, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    <button id="logout-btn">Log Out</button>
                <?php else: ?>
                    <button id="login-btn">Log In</button>
                    <button id="signup-btn">Sign Up</button>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <div class="cart-container">
        <h1>Your Cart</h1>
        <div class="cart-items" id="cart-items">
            <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                    <div class="cart-item" data-id="<?= $item['id'] ?>">
                        <div class="item-info">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="item-meta">
                                <span class="item-price">₹<?= number_format($item['price'], 2) ?></span>
                                <span class="item-quantity">× <?= $item['quantity'] ?></span>
                                <span class="item-subtotal">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="decrease-quantity" data-id="<?= $item['id'] ?>">-</button>
                            <button class="increase-quantity" data-id="<?= $item['id'] ?>">+</button>
                            <button class="remove-item" data-id="<?= $item['id'] ?>">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="ri-shopping-cart-line"></i>
                    <p>Your cart is empty</p>
                    <a href="menu.php" class="btn">Browse Menu</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($_SESSION['cart'])): ?>
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>₹<?= number_format($cartTotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Discount:</span>
                <span>-₹<?= number_format($discountTotal, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery Fee:</span>
                <span>₹50.00</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="cart-total">₹<?= number_format($cartTotal + 50, 2) ?></span>
            </div>
            <button id="checkout-btn" class="btn-primary">Proceed to Checkout</button>
            <button id="clear-cart" class="btn-secondary">Clear Cart</button>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="address">
            <section>
                <h1>Address</h1>
                <p>Koramangla, 20th main road<br>11th cross, Bangalore-560034</p>
            </section>
        </div>
        <div class="contact">
            <section>
                <h1>Contact</h1>
                <p>Phone: 0123456789<br>
                <a href="mailto:eat&enjoy@fake.com">Email: eat&enjoy@gmail.com</a></p>
            </section>
        </div>
        <div class="hours">
            <section>
                <h1>Hours</h1>
                <p>Monday - Friday: 10:00 AM - 10:00 PM<br>
                Saturday - Sunday: 10:00 AM - 12:00 PM</p>
            </section>
        </div>
        <div class="socials">
            <section>
                <h1>Follow Us</h1>
                <i class="ri-facebook-circle-fill"></i>
                <i class="ri-instagram-fill"></i>
                <i class="ri-twitter-x-fill"></i>
            </section>
        </div>
        <hr>
        <div class="copy">
            <p>&copy; 2020 Eat & Enjoy. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Replace the script in cart.php with this:
    document.addEventListener('DOMContentLoaded', function() {
        // Helper function to safely add event listeners
        function safeAddEventListener(selector, event, callback) {
            const element = document.querySelector(selector);
            if (element) {
                element.addEventListener(event, callback);
            }
        }

        // Update quantity
        function updateQuantity(id, change) {
            fetch('update_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    change: change
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating quantity');
            });
        }

        // Add event listeners only if elements exist
        document.querySelectorAll('.increase-quantity').forEach(btn => {
            btn.addEventListener('click', function() {
                updateQuantity(this.dataset.id, 1);
            });
        });

        document.querySelectorAll('.decrease-quantity').forEach(btn => {
            btn.addEventListener('click', function() {
                updateQuantity(this.dataset.id, -1);
            });
        });

        // Remove item
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Remove this item from cart?')) {
                    fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: this.dataset.id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                }
            });
        });

        // Clear cart - only add if element exists
        const clearCartBtn = document.getElementById('clear-cart');
        if (clearCartBtn) {
            clearCartBtn.addEventListener('click', function() {
                if (confirm('Clear all items from cart?')) {
                    fetch('clear_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                }
            });
        }

        // Checkout - only add if element exists
        const checkoutBtn = document.getElementById('checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function() {
                window.location.href = 'checkout.php';
            });
        }
    });
    </script>
    
    <script src="auth.js"></script>
</body>
</html>