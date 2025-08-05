document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality with better error handling
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async function() {
            const originalText = this.textContent;
            this.textContent = 'Adding...';
            this.disabled = true;

            try {
                const menuItem = this.closest('.menu-item');
                const itemData = {
                    id: String(menuItem.getAttribute('data-id')), // Ensure string ID
                    name: menuItem.querySelector('h2').textContent,
                    price: parseFloat(menuItem.querySelector('.price').textContent.replace(/[^0-9.]/g, '')),
                    category: menuItem.getAttribute('data-category')
                };

                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(itemData)
                });

                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to add item');
                }

                // Update cart count display
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.cartCount;
                });

                // Visual feedback
                this.textContent = '✓ Added!';
                this.style.backgroundColor = '#4CAF50';
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.style.backgroundColor = '';
                    this.disabled = false;
                }, 2000);

                // Debug: log cart contents
                console.log('Cart updated:', data.cartItems);

            } catch (error) {
                console.error('Error adding to cart:', error);
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

    // Initialize cart count with error handling
    async function updateCartCount() {
        try {
            const response = await fetch('get_cart_count.php');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count || 0;
            });
        } catch (error) {
            console.error('Error loading cart count:', error);
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = '0';
            });
        }
    }

    updateCartCount();
});