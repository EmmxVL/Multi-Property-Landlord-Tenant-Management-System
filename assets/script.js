// Shared utility functions
function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    if (!container) return;
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    container.appendChild(notification);
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Modal open/close helpers
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
}
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}

// Keyboard shortcut: ESC closes all modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.active').forEach(modal => modal.classList.remove('active'));
    }
});

// Export for other modules
window.UnitlyUtils = { showNotification, openModal, closeModal };

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

        
document.addEventListener('DOMContentLoaded', function() {
    // Modal Management
    const modals = {
        sms: document.getElementById('sms-modal'),
        landlord: document.getElementById('landlord-modal'),
        password: document.getElementById('password-modal'),
        bulk: document.getElementById('bulk-modal'),
        calendar: document.getElementById('calendar-modal')
    };

    // Button Event Listeners
    const buttons = {
        smsVerify: document.getElementById('sms-verify-btn'),
        addLandlord: document.getElementById('add-landlord-btn'),
        managePasswords: document.getElementById('manage-passwords-btn'),
        bulkActions: document.getElementById('bulk-actions-btn'),
        tenantView: document.getElementById('tenant-view-btn'),
        landlordView: document.getElementById('landlord-view-btn'),
        verifyOtp: document.getElementById('verify-otp-btn'),
        saveLandlord: document.getElementById('save-landlord-btn')
    };

    // View Management
    const views = {
        landlordManagement: document.getElementById('landlord-management'),
        tenantManagement: document.getElementById('tenant-management')
    };

    // Modal Functions
    function openModal(modalName) {
        if (modals[modalName]) {
            modals[modalName].style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalName) {
        if (modals[modalName]) {
            modals[modalName].style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function closeAllModals() {
        Object.keys(modals).forEach(modalName => {
            closeModal(modalName);
        });
    }

    // Event Listeners for Opening Modals
    if (buttons.smsVerify) {
        buttons.smsVerify.addEventListener('click', () => openModal('sms'));
    }

    if (buttons.addLandlord) {
        buttons.addLandlord.addEventListener('click', () => openModal('landlord'));
    }

    if (buttons.managePasswords) {
        buttons.managePasswords.addEventListener('click', () => openModal('password'));
    }

    if (buttons.bulkActions) {
        buttons.bulkActions.addEventListener('click', () => openModal('bulk'));
    }

    // Event Listeners for Closing Modals
    const closeButtons = [
        'close-sms-modal',
        'close-landlord-modal', 
        'close-password-modal',
        'close-bulk-modal',
        'close-calendar-modal'
    ];

    closeButtons.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            button.addEventListener('click', closeAllModals);
        }
    });

    // Close modals when clicking outside
    Object.values(modals).forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAllModals();
                }
            });
        }
    });

    // View Switching
    if (buttons.tenantView) {
        buttons.tenantView.addEventListener('click', function() {
            if (views.landlordManagement) views.landlordManagement.style.display = 'none';
            if (views.tenantManagement) views.tenantManagement.style.display = 'block';
            showNotification('Switched to Tenant Management View', 'success');
        });
    }

    if (buttons.landlordView) {
        buttons.landlordView.addEventListener('click', function() {
            if (views.tenantManagement) views.tenantManagement.style.display = 'none';
            if (views.landlordManagement) views.landlordManagement.style.display = 'block';
            showNotification('Switched to Landlord Management View', 'success');
        });
    }

    // OTP Input Management
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    // OTP Verification
    if (buttons.verifyOtp) {
        buttons.verifyOtp.addEventListener('click', function() {
            const otpValue = Array.from(otpInputs).map(input => input.value).join('');
            if (otpValue.length === 6) {
                showNotification('SMS verification successful!', 'success');
                closeAllModals();
                // Clear OTP inputs
                otpInputs.forEach(input => input.value = '');
            } else {
                showNotification('Please enter the complete 6-digit code', 'error');
            }
        });
    }

    // Landlord Creation
    if (buttons.saveLandlord) {
        buttons.saveLandlord.addEventListener('click', function() {
            const form = document.querySelector('#landlord-modal form') || 
                        document.querySelector('#landlord-modal');
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="password"]');
            
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444';
                } else {
                    input.style.borderColor = '#e2e8f0';
                }
            });

            if (isValid) {
                showNotification('New landlord account created successfully!', 'success');
                closeAllModals();
                // Clear form
                inputs.forEach(input => input.value = '');
            } else {
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }

    // Account Action Handlers
    function handleAccountAction(action, accountName, accountType) {
        let message = '';
        let type = 'success';

        switch(action) {
            case 'edit':
                message = `Editing ${accountName} account details`;
                type = 'info';
                break;
            case 'suspend':
                message = `${accountName} account has been suspended`;
                type = 'warning';
                break;
            case 'reactivate':
                message = `${accountName} account has been reactivated`;
                type = 'success';
                break;
            case 'delete':
                if (confirm(`Are you sure you want to delete ${accountName}'s account? This action cannot be undone.`)) {
                    message = `${accountName} account has been deleted`;
                    type = 'error';
                } else {
                    return;
                }
                break;
        }

        showNotification(message, type);
    }

    // Add event listeners to all action buttons
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button');
        if (!button) return;

        const title = button.getAttribute('title');
        const accountCard = button.closest('.property-card');
        
        if (accountCard && title) {
            const accountName = accountCard.querySelector('h3').textContent;
            const accountType = accountCard.closest('#landlord-management') ? 'landlord' : 'tenant';
            
            switch(title.toLowerCase()) {
                case 'edit':
                    handleAccountAction('edit', accountName, accountType);
                    break;
                case 'suspend':
                    handleAccountAction('suspend', accountName, accountType);
                    break;
                case 'reactivate':
                    handleAccountAction('reactivate', accountName, accountType);
                    break;
                case 'delete':
                    handleAccountAction('delete', accountName, accountType);
                    break;
            }
        }
    });

    // Notification System
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const icon = getNotificationIcon(type);
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-800">${message}</p>
                </div>
                <button class="flex-shrink-0 text-slate-400 hover:text-slate-600" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    function getNotificationIcon(type) {
        const icons = {
            success: `<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>`,
            error: `<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>`,
            warning: `<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                      </svg>`,
            info: `<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                   </svg>`
        };
        return icons[type] || icons.info;
    }

    // Form Validation
    function validateForm(formElement) {
        const inputs = formElement.querySelectorAll('input[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500');
                input.classList.remove('border-slate-300');
            } else {
                input.classList.remove('border-red-500');
                input.classList.add('border-slate-300');
            }
        });

        return isValid;
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape key closes all modals
        if (e.key === 'Escape') {
            closeAllModals();
        }
        
        // Ctrl/Cmd + K opens search (if implemented)
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            // Implement search functionality here
            showNotification('Search functionality coming soon!', 'info');
        }
    });

    // Initialize tooltips and other UI enhancements
    function initializeUI() {
        // Add loading states to buttons
        const actionButtons = document.querySelectorAll('.action-btn');
        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 1000);
                }
            });
        });

        // Initialize fade-in animations for cards
        const cards = document.querySelectorAll('.property-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Calendar functionality
    function initializeCalendar() {
        const calendarDays = document.querySelectorAll('.calendar-day');
        calendarDays.forEach(day => {
            day.addEventListener('click', function() {
                if (this.classList.contains('has-event')) {
                    showNotification(`Event details for ${this.textContent}`, 'info');
                }
            });
        });
    }

    // Initialize everything
    initializeUI();
    initializeCalendar();

    // Show welcome message
    setTimeout(() => {
        showNotification('Welcome to Unitly Admin Dashboard!', 'success');
    }, 1000);
});

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(new Date(date));
}

function generateRandomId() {
    return Math.random().toString(36).substr(2, 9);
}

// Export functions for external use
window.AdminDashboard = {
    showNotification: function(message, type) {
        // This allows external scripts to show notifications
        const event = new CustomEvent('showNotification', {
            detail: { message, type }
        });
        document.dispatchEvent(event);
    }
};

window.TenantDashboard = {
    showNotification: function(message, type) {
        const event = new CustomEvent('showNotification', {
            detail: { message, type }
        });
        document.dispatchEvent(event);
    },
    
    uploadReceipt: function(receiptData) {
        // This would handle programmatic receipt uploads
        console.log('Receipt uploaded:', receiptData);
    },
    
    submitMaintenanceRequest: function(requestData) {
        // This would handle programmatic maintenance requests
        console.log('Maintenance request submitted:', requestData);
    }
};