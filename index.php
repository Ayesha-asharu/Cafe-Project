<!DOCTYPE html>
<html>
<style>
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
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
        text-align: center;
        position: relative;
    }
    
    .close-modal {
        position: absolute;
        right: 15px;
        top: 5px;
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: black;
    }
</style>
<head>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="index.css">
    <title>cafe</title>
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
                <a href="cart.php"><i class="ri-shopping-cart-2-line"></i> <li>Cart (<span class="cart-count">0</span>)</li></a>
            </ul>
            <div class="auth-buttons">
                <button id="login-btn">Log In</button>
                <button id="signup-btn">Sign Up</button>
                <button id="logout-btn" style="display: none;">Log Out</button>
                <span id="user-greeting" style="display: none; color: white; margin-right: 10px;"></span>
            </div>
        </div>
    </header> 
   <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Eat & Enjoy</h1>
            <p>Experience the finest dining in a cozy atmosphere</p>
            <div class="hero-buttons">
                <a href="#booking-form" class="btn">Make a Reservation</a>
                <a href="menu.php" class="btn" style="margin-left: 100px;">Order Now</a>
            </div>
        </div>
   </section>

    <section class="features">
        <div class="feature">
            <i class="fas fa-utensils"></i>
            <i class="ri-bread-line"></i>
            <h3>Expert Chefs</h3>
            <p>Crafting delicious meals with passion</p>
        </div>
        <div class="feature">
            <i class="fas fa-leaf"></i>
            <i class="ri-restaurant-2-line"></i>
            <h3>Quality Cuisine</h3>
            <p>Fresh ingredients, authentic flavors</p>
        </div>
        <div class="feature">
            <i class="fas fa-home"></i>
            <i class="ri-bowl-line"></i>
            <h3>Cozy Atmosphere</h3>
            <p>Perfect ambiance for every occasion</p>
        </div>
    </section>

    <section class="cta">
        <h2>Ready for a memorable dining experience?</h2>

    <div class="form">
        <div class="title">
            <h1> Book a Table </h1>
        </div>
        <form id="booking-form" method="post">
            <label for="name"> Name </label> <br>
            <input type="text" id="name" name="name" required placeholder="Enter your name" maxlength="20" minlength="3"> <br><br>
            <label for="contact"> Phone Number </label> <br>
            <input type="tel" id="contact" name="contact" required placeholder="Enter your 10 digit phone number" maxlength="10"> <br><br>
            <label for="email"> Email </label> <br>
            <input type="email" id="email" name="email" required placeholder="Enter your valid email" maxlength="40"> <br><br>
            <label for="quantity"> Number Of People </label> <br>
            <select id="quantity" name="quantity">
                <option value="1 Person"> 1 Person </option>
                <option value="2 people"> 2 People </option>
                <option value="3 people"> 3 People </option>
                <option value="4 people"> 4 People </option>
                <option value="5 people"> 5 People </option>
                <option value="6 people"> 6 People </option>
                <option value="7 people"> 7 People </option>
                <option value="8 people"> 8 People </option>
            </select><br><br>
            <label for="date"> Date </label> <br>
            <input type="date" id="date" name="date" required> <br> <br>
            <input type="submit" value="Book Now">
        </form>
    </div>
    <div id="booking-confirmation" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Thank You!</h2>
        <p>Thank you for booking a table in our cafe, your booking is confirmed!</p>
    </div>
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