document.addEventListener('DOMContentLoaded', function() {
    const { showNotification, openModal, closeModal } = window.UnitlyUtils;

    // --- Modal Management ---
    // SMS Verification Modal
    document.getElementById('sms-verify-btn')?.addEventListener('click', () => openModal('sms-modal'));
    document.getElementById('close-sms-modal')?.addEventListener('click', () => closeModal('sms-modal'));

    // Receipt Upload Modal
    document.getElementById('receipt-upload-btn')?.addEventListener('click', () => openModal('receipt-modal'));
    document.getElementById('close-receipt-modal')?.addEventListener('click', () => closeModal('receipt-modal'));

    // Calendar Modal
    document.getElementById('calendar-btn')?.addEventListener('click', () => openModal('calendar-modal'));
    document.getElementById('close-calendar-modal')?.addEventListener('click', () => closeModal('calendar-modal'));

    // --- OTP Input Handling ---
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, idx) => {
        input.addEventListener('input', function() {
            if (this.value.length === 1 && idx < otpInputs.length - 1) {
                otpInputs[idx + 1].focus();
            }
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && idx > 0) {
                otpInputs[idx - 1].focus();
            }
        });
    });

    document.getElementById('verify-otp-btn')?.addEventListener('click', function() {
        const otpCode = Array.from(otpInputs).map(input => input.value).join('');
        if (otpCode.length === 6) {
            showNotification('SMS verification successful!', 'success');
            closeModal('sms-modal');
            otpInputs.forEach(input => input.value = '');
        } else {
            showNotification('Please enter the complete 6-digit code', 'error');
        }
    });

    // --- Receipt Upload Handling ---
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('receipt-file');

    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', () => fileInput.click());
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
    }

    function handleFiles(files) {
        if (files.length > 0) {
            const fileNames = Array.from(files).map(file => file.name).join(', ');
            showNotification(`Selected files: ${fileNames}`, 'info');
        }
    }

    // Upload button inside modal
    document.querySelectorAll('#upload-receipt-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (fileInput && fileInput.files.length > 0) {
                showNotification('Receipt uploaded successfully!', 'success');
                closeModal('receipt-modal');
                fileInput.value = '';
            } else {
                showNotification('Please select files to upload', 'warning');
            }
        });
    });

    // --- Property List & Map View Toggle ---
    let currentView = 'list';
    document.getElementById('map-view-btn')?.addEventListener('click', function() {
        document.getElementById('property-list')?.style.display = 'none';
        document.getElementById('property-map')?.style.display = 'block';
        currentView = 'map';
        showNotification('Switched to map view', 'info');
    });
    document.getElementById('list-view-btn')?.addEventListener('click', function() {
        document.getElementById('property-map')?.style.display = 'none';
        document.getElementById('property-list')?.style.display = 'block';
        currentView = 'list';
        showNotification('Switched to list view', 'info');
    });

    // --- Calendar Day Click ---
    document.querySelectorAll('.calendar-day').forEach(day => {
        day.addEventListener('click', function() {
            if (this.textContent && !isNaN(this.textContent)) {
                showNotification(`Selected date: December ${this.textContent}, 2024`, 'info');
            }
        });
    });

    // --- Property Card Click ---
    document.querySelectorAll('.property-card').forEach(card => {
        card.addEventListener('click', function() {
            const propertyName = this.querySelector('h3')?.textContent || 'Property';
            showNotification(`Viewing details for ${propertyName}`, 'info');
        });
    });

    // --- Action Button Feedback ---
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.querySelector('span')?.textContent;
            if (action && !['Upload Receipt', 'Schedule & Calendar'].includes(action)) {
                showNotification(`${action} feature activated`, 'info');
            }
        });
    });

    // --- Welcome Message ---
    setTimeout(() => showNotification('Welcome to Unitly Landlord Dashboard!', 'success'), 1000);
});

const addTenantModal = document.getElementById('add-tenant-modal');
const openAddTenantBtn = document.getElementById('open-add-tenant-modal-btn');
const closeAddTenantBtns = addTenantModal.querySelectorAll('[data-modal-close="add-tenant-modal"]');

if (openAddTenantBtn && addTenantModal) {
    openAddTenantBtn.addEventListener('click', () => {
        addTenantModal.style.display = 'flex'; // Or your modal show class/logic
    });
}

closeAddTenantBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        addTenantModal.style.display = 'none'; // Or your modal hide class/logic
    });
});