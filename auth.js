// In your auth.js or any common JS file
console.log('Session Cookie:', document.cookie.match(/PHPSESSID=([^;]+)/));
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const loginBtn = document.getElementById('login-btn');
    const signupBtn = document.getElementById('signup-btn');
    const logoutBtn = document.getElementById('logout-btn');
    const userGreeting = document.getElementById('user-greeting');
    const welcomePopup = document.getElementById('welcome-popup');
    const adminLoginBtn = document.getElementById('admin-login-btn');
    const customerLoginBtn = document.getElementById('customer-login-btn');
    const loginPopup = document.getElementById('login-popup');
    const signupPopup = document.getElementById('signup-popup');
    const closeButtons = document.querySelectorAll('.close-modal');
    const switchToSignup = document.getElementById('switch-to-signup');
    const switchToLogin = document.getElementById('switch-to-login');
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const bookingForm = document.getElementById('booking-form');

    // Check if user is logged in
    let currentUser = null;
    try {
        const userData = localStorage.getItem('currentUser');
        currentUser = userData && userData !== 'undefined' && userData !== 'null' ? JSON.parse(userData) : null;
    } catch (e) {
        console.error("Error parsing user data:", e);
        localStorage.removeItem('currentUser');
        currentUser = null;
    }

    // Add this right here:
    if (currentUser) {
        autofillForms(currentUser);
    }

    // Welcome Popup Logic - Show on every refresh unless user is logged in
    if (!currentUser) {
        showModal(welcomePopup);
    }

    // Modal helper functions
    function showModal(modal) {
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function hideModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    // Event Listeners
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            document.getElementById('user-type').value = 'customer';
            showModal(loginPopup);
        });
    }
    
    if (signupBtn) {
        signupBtn.addEventListener('click', () => showModal(signupPopup));
    }
    
    if (logoutBtn) {
    logoutBtn.addEventListener('click', async function() {
        try {
            // Clear server session
            await fetch('logout.php', { method: 'POST' });
            
            // Clear client-side storage
            localStorage.removeItem('currentUser');
            
            // Update UI
            updateAuthUI();
            
            alert('You have been logged out.');
            // Show welcome popup after logout
            showModal(welcomePopup);
        } catch (error) {
            console.error('Logout error:', error);
            alert('Error during logout. Please try again.');
        }
    });
}

    if (closeButtons) {
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                hideModal(modal);
            });
        });
    }

    if (adminLoginBtn) {
        adminLoginBtn.addEventListener('click', function() {
            hideModal(welcomePopup);
            window.location.href = 'admin_login.php';
        });
    }

    if (customerLoginBtn) {
        customerLoginBtn.addEventListener('click', function() {
            hideModal(welcomePopup);
            document.getElementById('user-type').value = 'customer';
            showModal(loginPopup);
        });
    }

    if (switchToSignup) {
        switchToSignup.addEventListener('click', () => {
            hideModal(loginPopup);
            showModal(signupPopup);
        });
    }

    if (switchToLogin) {
        switchToLogin.addEventListener('click', () => {
            hideModal(signupPopup);
            showModal(loginPopup);
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            hideModal(e.target);
        }
    });

    // Handle Sign Up
    signupForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'register',
            name: document.getElementById('signup-name').value,
            email: document.getElementById('signup-email').value,
            phone: document.getElementById('signup-phone').value,
            password: document.getElementById('signup-password').value
        };

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            // First check if response is HTML (error page)
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
            }
            
            if (!response.ok) {
                throw new Error(data.message || 'Registration failed');
            }
            
            if (data.status === 'success') {
                localStorage.setItem('currentUser', JSON.stringify(data.user));
                updateAuthUI(data.user);
                hideModal(signupPopup);
                alert('Sign up successful!');
                this.reset();
            } else {
                throw new Error(data.message || 'Registration failed');
            }
        } catch (error) {
            console.error('Registration Error:', error);
            alert(`Registration failed: ${error.message}`);
        }
    });

    // Handle Log In
    loginForm?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        action: 'login',
        email: document.getElementById('login-email').value,
        password: document.getElementById('login-password').value,
        userType: document.getElementById('user-type').value
    };

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        // First check response status
        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.message || 'Login failed with status ' + response.status);
        }
        
        const data = await response.json();
        
        if (data.status === 'success') {
            localStorage.setItem('currentUser', JSON.stringify(data.user));
            updateAuthUI(data.user);
            hideModal(loginPopup);
            alert('Login successful!');
            this.reset();
            
            // Autofill booking form if exists
            autofillForms(data.user);
        } else {
            throw new Error(data.message || 'Login failed');
        }
        } catch (error) {
            console.error('Login Error:', error);
            alert(`Login failed: ${error.message}`);
        }
    });
        // Then update the autofillForms function to this:
    function autofillForms(user) {
        if (!user) return;
    
        console.log('Autofilling forms for user:', user);
        // Autofill booking form in index.php
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
            const nameField = bookingForm.querySelector('#name');
            const phoneField = bookingForm.querySelector('#contact');
            const emailField = bookingForm.querySelector('#email');
            
            if (nameField) nameField.value = user.name || '';
            if (phoneField) phoneField.value = user.phone || '';
            if (emailField) emailField.value = user.email || '';
        }
        
        // Autofill checkout form in checkout.php
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            const nameField = checkoutForm.querySelector('#name');
            const phoneField = checkoutForm.querySelector('#phone');
            const emailField = checkoutForm.querySelector('#email');
            
            if (nameField) nameField.value = user.name || '';
            if (phoneField) phoneField.value = user.phone || '';
            if (emailField) emailField.value = user.email || '';
        }
    }

      
     async function checkLoginStatus() {
        try {
            const response = await fetch('check_auth.php');
            const data = await response.json();
            return data.loggedIn ? data.user : null;
        } catch (error) {
            console.error('Error checking auth status:', error);
            return null;
        }

        
    } 

    async function updateAuthUI() {
    try {
        const response = await fetch('check_auth.php');
        const data = await response.json();
        
        if (data.loggedIn) {
            // User is logged in
            if (loginBtn) loginBtn.style.display = 'none';
            if (signupBtn) signupBtn.style.display = 'none';
            if (logoutBtn) logoutBtn.style.display = 'block';
            if (userGreeting) {
                userGreeting.style.display = 'block';
                userGreeting.textContent = `Hello, ${data.user.name.split(' ')[0]}!`;
            }
            // Store user in localStorage for client-side access
            localStorage.setItem('currentUser', JSON.stringify(data.user));
            // Autofill forms with user data
            autofillForms(data.user);
            } else {
                // User is logged out
                if (loginBtn) loginBtn.style.display = 'block';
                if (signupBtn) signupBtn.style.display = 'block';
                if (logoutBtn) logoutBtn.style.display = 'none';
                if (userGreeting) userGreeting.style.display = 'none';
                // Remove user from localStorage
                localStorage.removeItem('currentUser');
            }
        } catch (error) {
            console.error('Error checking auth status:', error);
        }
    }
    // Initialize UI
    updateAuthUI();

    // Enhanced Booking Form Submission
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = this.querySelector('[type="submit"]');
            const originalBtnText = submitBtn.value;
            submitBtn.value = 'Processing...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData(this);
                const formDataObj = Object.fromEntries(formData.entries());
                
                // Add user info if logged in
                if (currentUser) {
                    formDataObj.email = currentUser.email;
                }

                const response = await fetch('process_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formDataObj)
                });
                
                // First check if response is HTML (error page)
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
                }
                
                if (!response.ok) {
                    throw new Error(data.message || 'Booking failed');
                }
                
                if (data.status === 'success') {
                    // Show confirmation modal
                    const modal = document.getElementById('booking-confirmation');
                    if (modal) {
                        showModal(modal);
                        
                        // Reset form
                        this.reset();
                    }
                } else {
                    throw new Error(data.message || 'Booking failed');
                }
            } catch (error) {
                console.error('Booking Error:', error);
                alert(`Booking failed: ${error.message}`);
            } finally {
                // Reset button state
                submitBtn.value = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }
});