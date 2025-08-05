<?php
require_once 'config.php';
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$loggedIn = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>About Us - Eat & Enjoy</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="about.css">
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
                <a href="cart.php"><i class="ri-shopping-cart-2-line"></i><li>Cart (<span class="cart-count"><?= $cartCount ?></span>)</li></a>
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
    
    <div class="container">
        <section class="about-section">
            <h1>About Us</h1>
            <p>Welcome to Eat & Enjoy, where we combine passion for great food with a warm, inviting atmosphere. <br>
               Our restaurant has been serving delicious meals and creating memorable dining experiences since 2020. <br><br>

               We take pride in using only the freshest ingredients and preparing each dish with care and attention to detail. <br>
               Our expert chefs bring years of culinary experience to create both traditional favorites and innovative new dishes.</p>
            <!-- Rest of your about content -->
        </section>
    </div>

    <footer>
        <div class="address">
            <section>
            <h1> Address </h1>
            <p> Koramangla, 20th main road

                11th cross, Bangalore-560034 </p>
            </section>
        </div>
        <div class="contact"> 
            <section>
            <h1> Contact  </h1>  
            <p> Phone: 0123456789 <br>
                <a href="mailto:eat&enjoy@fake.com"> Email: eat&enjoy@gmail.com</a>
            </p>  
            </section>    
        </div>
        <div class="hours">
            <section>
            <h1> Hours </h1>
            <p> Monday - Friday: 10:00 AM - 10:00 PM <br>
                Saturday - Sunday: 10:00 AM - 12:00 PM
            </p>
            </section>
        </div>
        <div class="socials">
            <section>
            <h1> Follow Us </h1>
            <i class="ri-facebook-circle-fill"></i>
            <i class="ri-instagram-fill"></i>
            <i class="ri-twitter-x-fill"></i>
            </section>
        </div>
        <hr>
        <div class="copy">
            <p> &copy; 2020 Eat & Enjoy. All rights reserved. </p>
        </div>
    </footer>

    <script src="auth.js"></script>
</body>
</html>