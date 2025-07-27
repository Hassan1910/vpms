/**
 * Enhanced Sidebar Functionality
 * Adds smooth interactions and active state management
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get current page URL
    const currentUrl = window.location.pathname;
    const currentPage = currentUrl.split('/').pop();
    
    // Remove existing active states
    const allMenuItems = document.querySelectorAll('.navbar-nav li');
    allMenuItems.forEach(item => item.classList.remove('active'));
    
    // Set active state based on current page
    const menuLinks = document.querySelectorAll('.navbar-nav li a');
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.parentElement.classList.add('active');
        }
    });
    
    // Add hover effects
    const menuItems = document.querySelectorAll('.navbar-nav li a');
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (!this.parentElement.classList.contains('active')) {
                this.style.transform = 'translateX(8px)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.parentElement.classList.contains('active')) {
                this.style.transform = 'translateX(0)';
            }
        });
    });
    
    // Add click animation
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Create ripple effect
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.left = (e.clientX - rect.left - 10) + 'px';
            ripple.style.top = (e.clientY - rect.top - 10) + 'px';
            ripple.style.width = '20px';
            ripple.style.height = '20px';
            ripple.style.pointerEvents = 'none';
            
            this.style.position = 'relative';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add smooth scrolling for sidebar
    const sidebar = document.querySelector('.left-panel');
    if (sidebar) {
        sidebar.style.scrollBehavior = 'smooth';
    }
});

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .navbar-nav li a {
        position: relative;
        overflow: hidden;
    }
    
    .navbar-nav li a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background: rgba(102, 126, 234, 0.1);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
        z-index: -1;
    }
    
    .navbar-nav li a:hover::before {
        transform: scaleX(1);
    }
    
    .navbar-nav li.active a::before {
        background: rgba(255, 255, 255, 0.2);
        transform: scaleX(1);
    }
`;
document.head.appendChild(style);
