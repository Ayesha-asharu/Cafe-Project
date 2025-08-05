<!DOCTYPE html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="checkout.css">
    <title>cafe</title>
</head>
<body>
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

    <div class="checkout-container">
        <h1>Checkout</h1>
        
        <div class="checkout-content">
            <div class="order-summary">
                <h2>Your Order</h2>
                <div id="checkout-items">
                    <!-- Order items will be loaded here -->
                </div>
                <div class="order-total">
                    <span>Total:</span>
                    <span id="checkout-total">₹0</span>
                </div>
            </div>
            
            <form class="customer-info" id="checkout-form">
                <h2>Customer Information</h2>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Delivery Address</label>
                    <textarea id="address" name="address" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="notes">Special Instructions (Optional)</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <h2>Payment Method</h2>
                
                <div class="payment-methods">
                   
                    <div class="payment-option">
                        <input type="radio" id="google-pay" name="payment" value="google-pay">
                        <label for="google-pay">Google Pay</label>
                        <div class="payment-details" id="google-pay-details">
                            <p>Pay securely using your Google Pay account</p>
                            <div class="upi-details">
                                <div class="form-group">
                                    <label for="google-pay-upi">UPI ID</label>
                                    <input type="text" id="google-pay-upi" name="google-pay-upi" placeholder="yourname@okhdfcbank">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-option">
                        <input type="radio" id="phonepe" name="payment" value="phonepe">
                        <label for="phonepe">PhonePe</label>
                        <div class="payment-details" id="phonepe-details">
                            <p>Pay securely using your PhonePe account</p>
                            <div class="upi-details">
                                <div class="form-group">
                                    <label for="phonepe-upi">UPI ID</label>
                                    <input type="text" id="phonepe-upi" name="phonepe-upi" placeholder="yourname@ybl">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="payment-option">
                        <input type="radio" id="online-payment" name="payment" value="online" checked>
                        <label for="online-payment">Online Payment</label>
                        <div class="payment-details" id="online-details">
                            <p>Pay securely using credit/debit card or UPI</p>
                            <div class="card-details">
                                <div class="form-group">
                                    <label for="card-number">Card Number</label>
                                    <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456">
                                </div>
                                <div class="form-group">
                                    <label for="card-expiry">Expiry Date</label>
                                    <input type="text" id="card-expiry" name="card-expiry" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label for="card-cvv">CVV</label>
                                    <input type="text" id="card-cvv" name="card-cvv" placeholder="123">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-option">
                        <input type="radio" id="cod" name="payment" value="cod">
                        <label for="cod">Cash on Delivery</label>
                        <div class="payment-details" id="cod-details">
                            <p>Pay with cash when your order arrives</p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load and display cart items from server
            fetch('get_cart_items.php')
                .then(response => response.json())
                .then(cartData => {
                    const checkoutItemsContainer = document.getElementById('checkout-items');
                    const checkoutTotalElement = document.getElementById('checkout-total');
                    
                    checkoutItemsContainer.innerHTML = '';
                    
                    if (cartData.items.length === 0) {
                        checkoutItemsContainer.innerHTML = '<p>Your cart is empty</p>';
                        checkoutTotalElement.textContent = '₹0';
                        return;
                    }
                    
                    let total = 0;
                    
                    cartData.items.forEach(item => {
                        const itemElement = document.createElement('div');
                        itemElement.className = 'checkout-item';
                        itemElement.innerHTML = `
                            <span>${item.name} × ${item.quantity}</span>
                            <span>₹${(item.price * item.quantity).toFixed(2)}</span>
                        `;
                        checkoutItemsContainer.appendChild(itemElement);
                        
                        total += item.price * item.quantity;
                    });
                    
                    checkoutTotalElement.textContent = `₹${total.toFixed(2)}`;
                });
        });
    </script>


    
             <!-- Custom Popup -->
             <div class="custom-popup" id="order-confirmation-popup">
                <div class="popup-content">
                    <i class="ri-checkbox-circle-fill success-icon"></i>
                    <h2>Order Confirmed!</h2>
                    <p>Your order is placed, thank you for ordering</p>
                    <button id="popup-close-btn">OK</button>
                </div>
            </div>

            
            <script src="auth.js"></script>
             <script src="checkout.js"></script>

            
        
        
</body>
</html>