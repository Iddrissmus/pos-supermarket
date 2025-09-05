import './bootstrap';

// Sidebar Navigation Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar item click handling
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger for logout button
            if (this.querySelector('button[type="submit"]')) {
                return;
            }
            
            // Remove active class from all items
            sidebarItems.forEach(i => i.classList.remove('active'));
            // Add active class to clicked item
            this.classList.add('active');
            
            // Handle navigation (you can add actual navigation logic here)
            const text = this.textContent.trim();
            if (text.includes('Products')) {
                window.location.href = '/products';
            } else if (text.includes('Dashboard')) {
                window.location.href = '/dashboard';
            }
        });
    });

    // Table row selection
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.classList.add('bg-blue-50');
            } else {
                row.classList.remove('bg-blue-50');
            }
        });
    });

    // Search functionality
    const searchInput = document.querySelector('input[placeholder="Search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Sales dropdown functionality
    const salesItem = document.querySelector('.sidebar-item:has(.fa-dollar-sign)');
    if (salesItem) {
        const dropdownArrow = salesItem.querySelector('.fa-chevron-down');
        if (dropdownArrow) {
            salesItem.addEventListener('click', function() {
                dropdownArrow.classList.toggle('rotate-180');
                // Add submenu logic here if needed
            });
        }
    }

    // Mobile menu toggle (for responsive design)
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuButton && sidebar) {
        mobileMenuButton.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
        });
    }
});

// Utility functions
window.ProductManagement = {
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    },
    
    // Format numbers with commas
    formatNumber: function(number) {
        return new Intl.NumberFormat('en-US').format(number);
    },
    
    // Show notification
    showNotification: function(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
};
