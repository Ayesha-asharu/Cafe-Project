document.addEventListener('DOMContentLoaded', function() {
    // Order Confirmation Popup elements
    const checkoutForm = document.getElementById('checkout-form');
    const orderConfirmationPopup = document.getElementById('order-confirmation-popup');
    const popupCloseBtn = document.getElementById('popup-close-btn');

    // Show popup function
    function showPopup() {
        orderConfirmationPopup.style.display = 'flex';
    }

    // Hide popup function
    function hidePopup() {
        orderConfirmationPopup.style.display = 'none';
    }

    // Checkout form submission handler
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            submitBtn.textContent = 'Processing...';
            submitBtn.disabled = true;

            try {
                // Get cart items
                const cartResponse = await fetch('get_cart_items.php');
                const cartData = await cartResponse.json();
                
                if (!cartData.items || cartData.items.length === 0) {
                    throw new Error('Your cart is empty');
                }

                // Validate form inputs
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const address = document.getElementById('address').value.trim();

                if (!name) throw new Error('Please enter your name');
                if (!email) throw new Error('Please enter your email');
                if (!phone) throw new Error('Please enter your phone number');
                if (!address) throw new Error('Please enter your delivery address');

                // More specific validation
                if (!/^\S+@\S+\.\S+$/.test(email)) {
                    throw new Error('Please enter a valid email address');
                }

                if (!/^\d{10,15}$/.test(phone)) {
                    throw new Error('Please enter a valid phone number (10-15 digits)');
                }

                // Payment method validation
                const paymentMethod = document.querySelector('input[name="payment"]:checked');
                if (!paymentMethod) {
                    throw new Error('Please select a payment method');
                }

                // Prepare order data
                const formData = new FormData(checkoutForm);
                const orderData = {
                    customer: Object.fromEntries(formData.entries()),
                    items: cartData.items,
                    totalAmount: parseFloat(document.getElementById('checkout-total').textContent.replace(/[^0-9.]/g, ''))
                };

                // Send to backend
                const response = await fetch('process_checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                });

                const responseData = await response.json();

                if (response.ok) {
                    // Clear the cart
                    await fetch('clear_cart.php', { method: 'POST' });
                    
                    // Update UI
                    document.querySelectorAll('.cart-count').forEach(el => {
                        el.textContent = '0';
                    });
                    // Show success popup
                    showPopup();
                    checkoutForm.reset();
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 3000);
                } else {
                    throw new Error(responseData.message || 'Order processing failed');
                }

            } catch (error) {
                console.error('Order Error:', error);
                
                // Show error message in a user-friendly way
                const errorDisplay = document.getElementById('error-display') || 
                                    document.createElement('div');
                errorDisplay.id = 'error-display';
                errorDisplay.className = 'error-message';
                errorDisplay.textContent = error.message;
                
                if (!document.getElementById('error-display')) {
                    checkoutForm.prepend(errorDisplay);
                }
                
                // Scroll to error
                errorDisplay.scrollIntoView({ behavior: 'smooth', block: 'center' });

            } finally {
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }

    // Close popup button
    if (popupCloseBtn) {
        popupCloseBtn.addEventListener('click', function() {
            hidePopup();
            window.location.href = 'index.php';
        });
    }

    // Close popup when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === orderConfirmationPopup) {
            hidePopup();
            window.location.href = 'index.php';
        }
    });

    // Show/hide payment details based on selected method
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all payment details first
            document.querySelectorAll('.payment-details').forEach(details => {
                details.style.display = 'none';
            });
            
            // Show selected payment details
            const detailsId = this.id + '-details';
            const detailsElement = document.getElementById(detailsId);
            if (detailsElement) {
                detailsElement.style.display = 'block';
            }
        });
    });

    // Initialize by showing the default selected payment method details
    const defaultPayment = document.querySelector('input[name="payment"]:checked');
    if (defaultPayment) {
        const detailsId = defaultPayment.id + '-details';
        const detailsElement = document.getElementById(detailsId);
        if (detailsElement) {
            detailsElement.style.display = 'block';
        }
    }
});
