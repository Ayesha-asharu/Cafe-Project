<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Fetch menu items from database
// Remove the duplicate query
try {
    $stmt = $db->query("SELECT *, (price * (1 - discount/100)) as discounted_price FROM menu_items");
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading menu: " . $e->getMessage());
}
// Get current cart count (sum of quantities)
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Menu | Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="menu.css">
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
            <i class="ri-cup-line"> <h1> Eat & Enjoy </h1> </i>
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
                <?php if (isset($_SESSION['user'])): ?>
                    <span id="user-greeting">Hello, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                    <button id="logout-btn">Log Out</button>
                <?php else: ?>
                    <button id="login-btn">Log In</button>
                    <button id="signup-btn">Sign Up</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="topmenu">
        <h1>Our Menu</h1>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="cuisine">Cuisine</button>
            <button class="filter-btn" data-filter="desserts">Desserts</button>
            <button class="filter-btn" data-filter="beverage">Beverages</button>
        </div>
    </div>
    
    <main class="menu-container">
        <div class="menu-items">
            <?php if (empty($menuItems)): ?>
                <p class="no-items">No menu items available at the moment.</p>
            <?php else: ?>
                <?php foreach ($menuItems as $item): ?>
                    <div class="menu-item" data-category="<?= htmlspecialchars($item['category']) ?>" data-id="<?= $item['id'] ?>">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="menu-item-image">
                        <?php else: ?>
                            <div class="menu-item-image placeholder">No Image</div>
                        <?php endif; ?>
                        <h2><?= htmlspecialchars($item['name']) ?></h2>
                        <p><?= htmlspecialchars($item['description']) ?></p>
                        <div class="price-container">
                            <?php if ($item['discount'] > 0): ?>
                                <span class="original-price">₹<?= number_format($item['price'], 2) ?></span>
                                <span class="discounted-price">₹<?= number_format($item['discounted_price'], 2) ?></span>
                                <span class="discount-badge"><?= $item['discount'] ?>% OFF</span>
                            <?php else: ?>
                                <span class="price">₹<?= number_format($item['price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="add-to-cart"
                            data-id="<?= $item['id'] ?>"
                            data-name="<?= htmlspecialchars($item['name']) ?>"
                            data-price="<?= $item['discount'] > 0 ? $item['discounted_price'] : $item['price'] ?>">
                        + Add to Cart
                    </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

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
    // Enhanced Add to Cart functionality with better error handling
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async function() {
            const originalText = this.textContent;
            this.textContent = 'Adding...';
            this.disabled = true;

            try {
                const item = {
                    id: this.dataset.id,
                    name: this.dataset.name,
                    price: this.dataset.price,
                    quantity: 1
                };

                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(item)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to add item');
                }

                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.cartCount;
                });

                this.textContent = '✓ Added!';
                this.style.backgroundColor = '#4CAF50';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.backgroundColor = '';
                    this.disabled = false;
                }, 2000);

            } catch (error) {
                console.error('Add to cart error:', error);
                this.textContent = 'Error!';
                this.style.backgroundColor = '#f44336';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.backgroundColor = '';
                    this.disabled = false;
                }, 2000);
                
                alert('Failed to add item: ' + error.message);
            }
        });
    });

    // Filter functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            const items = document.querySelectorAll('.menu-item');
            
            items.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'block';
                } else {
                    const category = item.dataset.category.toLowerCase();
                    if (category.includes(filter)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });

    // Close popup button
    if (popupCloseBtn) {
        popupCloseBtn.addEventListener('click', function() {
            orderConfirmationPopup.style.display = 'none';
            window.location.href = 'index.php';
        });
    }
</script>
    
    <script src="auth.js"></script>
 
</body>
</html>