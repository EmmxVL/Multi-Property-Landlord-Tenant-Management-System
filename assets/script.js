// Add click functionality to buttons
document.querySelectorAll('.action-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Simple feedback animation
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 150);
        
        // Show action feedback
        const buttonText = this.textContent.trim();
        console.log(`${buttonText} clicked!`);
        
        // You could add more specific functionality here
        if (buttonText === 'Create New Project') {
            alert('New project creation would open here!');
        } else if (buttonText === 'Invite Team Member') {
            alert('Team member invitation would open here!');
        } else if (buttonText === 'View Reports') {
            alert('Reports dashboard would open here!');
        } else if (buttonText === 'Settings') {
            alert('Settings panel would open here!');
        }
    });
});

// Add hover effects to project cards
document.querySelectorAll('.project-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#dbeafe';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '#eff6ff';
    });
});

// Add stats card hover effects
document.querySelectorAll('.bg-white.rounded-xl.shadow-sm.p-6.border.border-blue-100').forEach(card => {
    card.classList.add('stats-card');
});

// Add pulse animation to activity dots
document.querySelectorAll('.w-2.h-2.rounded-full').forEach(dot => {
    dot.classList.add('activity-dot');
});

// Newsletter form handling
function handleNewsletterSignup(event) {
    event.preventDefault();
    
    const emailInput = document.getElementById('newsletter-email');
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const email = emailInput.value;
    
    // Add success animation to button
    submitBtn.classList.add('newsletter-success');
    submitBtn.textContent = 'Subscribed!';
    
    // Show success message
    alert(`Thank you for subscribing with email: ${email}`);
    
    // Reset form after delay
    setTimeout(() => {
        emailInput.value = '';
        submitBtn.classList.remove('newsletter-success');
        submitBtn.textContent = 'Subscribe';
    }, 2000);
}

// Smooth scroll behavior for internal links (if any are added later)
function smoothScroll(target) {
    document.querySelector(target).scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Newsletter form submission
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSignup);
    }
    
    // Prevent default behavior for placeholder links
    const placeholderLinks = document.querySelectorAll('a[href="#"]');
    placeholderLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add click feedback
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
    
    // Enhanced hover effects for social icons
    const socialIcons = document.querySelectorAll('.social-icon');
    socialIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.1)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add ripple effect to newsletter button
    const newsletterBtn = document.querySelector('.newsletter-btn');
    if (newsletterBtn) {
        newsletterBtn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }
});

// Console log for development
console.log('Footer JavaScript loaded successfully!');
 // Global variables
        let currentView = 'list';
        
        // Utility functions
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            document.getElementById('notification-container').appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
        
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // SMS OTP functionality
        document.getElementById('sms-verify-btn').addEventListener('click', function() {
            openModal('sms-modal');
            showNotification('SMS OTP sent to your registered phone number', 'info');
        });
        
        document.getElementById('close-sms-modal').addEventListener('click', function() {
            closeModal('sms-modal');
        });
        
        // OTP input handling
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });
        
        document.getElementById('verify-otp-btn').addEventListener('click', function() {
            const otpCode = Array.from(otpInputs).map(input => input.value).join('');
            if (otpCode.length === 6) {
                showNotification('SMS verification successful!', 'success');
                closeModal('sms-modal');
                // Clear OTP inputs
                otpInputs.forEach(input => input.value = '');
            } else {
                showNotification('Please enter the complete 6-digit code', 'error');
            }
        });
        
        // Property view toggle
        document.getElementById('map-view-btn').addEventListener('click', function() {
            if (currentView === 'list') {
                document.querySelector('.bg-white.rounded-xl.shadow-sm.border.border-slate-200.fade-in').style.display = 'none';
                document.getElementById('property-map').style.display = 'block';
                currentView = 'map';
                showNotification('Switched to map view', 'info');
            }
        });
        
        document.getElementById('list-view-btn').addEventListener('click', function() {
            if (currentView === 'map') {
                document.getElementById('property-map').style.display = 'none';
                document.querySelector('.bg-white.rounded-xl.shadow-sm.border.border-slate-200.fade-in').style.display = 'block';
                currentView = 'list';
                showNotification('Switched to list view', 'info');
            }
        });
        
        // Receipt upload functionality
        document.getElementById('receipt-upload-btn').addEventListener('click', function() {
            openModal('receipt-modal');
        });
        
        document.getElementById('close-receipt-modal').addEventListener('click', function() {
            closeModal('receipt-modal');
        });
        
        // File upload handling
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('receipt-file');
        
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });
        
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
        
        function handleFiles(files) {
            if (files.length > 0) {
                const fileNames = Array.from(files).map(file => file.name).join(', ');
                showNotification(`Selected files: ${fileNames}`, 'info');
            }
        }
        
        document.getElementById('upload-receipt-btn').addEventListener('click', function() {
            const files = fileInput.files;
            if (files.length > 0) {
                showNotification('Receipt uploaded successfully!', 'success');
                closeModal('receipt-modal');
                // Reset form
                fileInput.value = '';
            } else {
                showNotification('Please select files to upload', 'warning');
            }
        });
        
        // Calendar functionality
        document.getElementById('calendar-btn').addEventListener('click', function() {
            openModal('calendar-modal');
        });
        
        document.getElementById('close-calendar-modal').addEventListener('click', function() {
            closeModal('calendar-modal');
        });
        
        // Calendar day click handling
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                if (this.textContent && !isNaN(this.textContent)) {
                    showNotification(`Selected date: December ${this.textContent}, 2024`, 'info');
                }
            });
        });
        
        // Property card interactions
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('click', function() {
                const propertyName = this.querySelector('h3').textContent;
                showNotification(`Viewing details for ${propertyName}`, 'info');
            });
        });
        
        // Action button interactions
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.querySelector('span').textContent;
                if (!['Upload Receipt', 'Schedule & Calendar'].includes(action)) {
                    showNotification(`${action} feature activated`, 'info');
                }
            });
        });
        
        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('Welcome to Unitly Multi-Property Management System', 'success');
            
            // Simulate real-time updates
            setInterval(() => {
                const activities = [
                    'New payment receipt uploaded',
                    'Maintenance request submitted',
                    'Tenant SMS verification completed',
                    'Property inspection scheduled'
                ];
                const randomActivity = activities[Math.floor(Math.random() * activities.length)];
                // Uncomment to show periodic notifications
                // showNotification(randomActivity, 'info');
            }, 30000);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
        });
      
