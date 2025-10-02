// Admin dashboard logic
document.addEventListener('DOMContentLoaded', function() {
    const { showNotification, openModal, closeModal } = window.UnitlyUtils;

    // Modal triggers
    document.getElementById('sms-verify-btn')?.addEventListener('click', () => openModal('sms-modal'));
    document.getElementById('add-landlord-btn')?.addEventListener('click', () => openModal('landlord-modal'));
    document.getElementById('manage-passwords-btn')?.addEventListener('click', () => openModal('password-modal'));
    document.getElementById('bulk-actions-btn')?.addEventListener('click', () => openModal('bulk-modal'));

    // Modal close buttons
    ['close-sms-modal', 'close-landlord-modal', 'close-password-modal', 'close-bulk-modal', 'close-calendar-modal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => {
            document.querySelectorAll('.modal.active').forEach(modal => modal.classList.remove('active'));
        });
    });

    // View switching
    document.getElementById('tenant-view-btn')?.addEventListener('click', () => {
        document.getElementById('landlord-management').style.display = 'none';
        document.getElementById('tenant-management').style.display = 'block';
        showNotification('Switched to Tenant Management View', 'success');
    });
    document.getElementById('landlord-view-btn')?.addEventListener('click', () => {
        document.getElementById('tenant-management').style.display = 'none';
        document.getElementById('landlord-management').style.display = 'block';
        showNotification('Switched to Landlord Management View', 'success');
    });

    // OTP verification
    document.getElementById('verify-otp-btn')?.addEventListener('click', () => {
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpValue = Array.from(otpInputs).map(input => input.value).join('');
        if (otpValue.length === 6) {
            showNotification('SMS verification successful!', 'success');
            closeModal('sms-modal');
            otpInputs.forEach(input => input.value = '');
        } else {
            showNotification('Please enter the complete 6-digit code', 'error');
        }
    });

    // Landlord creation
    document.getElementById('save-landlord-btn')?.addEventListener('click', () => {
        const form = document.querySelector('#landlord-modal form') || document.querySelector('#landlord-modal');
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
            closeModal('landlord-modal');
            inputs.forEach(input => input.value = '');
        } else {
            showNotification('Please fill in all required fields', 'error');
        }
    });

    // Account actions (edit, suspend, reactivate, delete)
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button');
        if (!button) return;
        const title = button.getAttribute('title');
        const accountCard = button.closest('.property-card');
        if (accountCard && title) {
            const accountName = accountCard.querySelector('h3').textContent;
            let message = '';
            let type = 'success';
            switch(title.toLowerCase()) {
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
    });

    // Welcome message
    setTimeout(() => showNotification('Welcome to Unitly Admin Dashboard!', 'success'), 1000);
});// Admin dashboard logic
document.addEventListener('DOMContentLoaded', function() {
    const { showNotification, openModal, closeModal } = window.UnitlyUtils;

    // Modal triggers
    document.getElementById('sms-verify-btn')?.addEventListener('click', () => openModal('sms-modal'));
    document.getElementById('add-landlord-btn')?.addEventListener('click', () => openModal('landlord-modal'));
    document.getElementById('manage-passwords-btn')?.addEventListener('click', () => openModal('password-modal'));
    document.getElementById('bulk-actions-btn')?.addEventListener('click', () => openModal('bulk-modal'));

    // Modal close buttons
    ['close-sms-modal', 'close-landlord-modal', 'close-password-modal', 'close-bulk-modal', 'close-calendar-modal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => {
            document.querySelectorAll('.modal.active').forEach(modal => modal.classList.remove('active'));
        });
    });

    // View switching
    document.getElementById('tenant-view-btn')?.addEventListener('click', () => {
        document.getElementById('landlord-management').style.display = 'none';
        document.getElementById('tenant-management').style.display = 'block';
        showNotification('Switched to Tenant Management View', 'success');
    });
    document.getElementById('landlord-view-btn')?.addEventListener('click', () => {
        document.getElementById('tenant-management').style.display = 'none';
        document.getElementById('landlord-management').style.display = 'block';
        showNotification('Switched to Landlord Management View', 'success');
    });

    // OTP verification
    document.getElementById('verify-otp-btn')?.addEventListener('click', () => {
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpValue = Array.from(otpInputs).map(input => input.value).join('');
        if (otpValue.length === 6) {
            showNotification('SMS verification successful!', 'success');
            closeModal('sms-modal');
            otpInputs.forEach(input => input.value = '');
        } else {
            showNotification('Please enter the complete 6-digit code', 'error');
        }
    });

    // Landlord creation
    document.getElementById('save-landlord-btn')?.addEventListener('click', () => {
        const form = document.querySelector('#landlord-modal form') || document.querySelector('#landlord-modal');
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
            closeModal('landlord-modal');
            inputs.forEach(input => input.value = '');
        } else {
            showNotification('Please fill in all required fields', 'error');
        }
    });

    // Account actions (edit, suspend, reactivate, delete)
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button');
        if (!button) return;
        const title = button.getAttribute('title');
        const accountCard = button.closest('.property-card');
        if (accountCard && title) {
            const accountName = accountCard.querySelector('h3').textContent;
            let message = '';
            let type = 'success';
            switch(title.toLowerCase()) {
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
    });

    // Welcome message
    setTimeout(() => showNotification('Welcome to Unitly Admin Dashboard!', 'success'), 1000);
});