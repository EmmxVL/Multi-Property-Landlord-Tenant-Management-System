document.addEventListener('DOMContentLoaded', function() {
    const { showNotification, openModal, closeModal } = window.UnitlyUtils;

    // Upload receipt modal
    document.getElementById('upload-receipt-btn')?.addEventListener('click', () => openModal('receipt-modal'));
    document.getElementById('upload-receipt-btn-2')?.addEventListener('click', () => openModal('receipt-modal'));
    document.getElementById('close-receipt-modal')?.addEventListener('click', () => closeModal('receipt-modal'));

    // Maintenance request modal
    document.getElementById('maintenance-request-btn')?.addEventListener('click', () => openModal('maintenance-modal'));
    document.getElementById('close-maintenance-modal')?.addEventListener('click', () => closeModal('maintenance-modal'));

    // Property details modal
    document.getElementById('view-property-details')?.addEventListener('click', () => openModal('property-modal'));
    document.getElementById('close-property-modal')?.addEventListener('click', () => closeModal('property-modal'));

    // Receipt upload logic (simplified)
    document.getElementById('save-receipt-btn')?.addEventListener('click', () => {
        showNotification('Receipt uploaded successfully!', 'success');
        closeModal('receipt-modal');
    });

    // Maintenance request submit
    document.getElementById('submit-maintenance-btn')?.addEventListener('click', () => {
        showNotification('Maintenance request submitted!', 'success');
        closeModal('maintenance-modal');
    });

    // Welcome message
    setTimeout(() => showNotification('Welcome to Unitly Tenant Dashboard!', 'success'), 1000);
});

document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sortBy');
    const filterSelect = document.getElementById('filterUnit');
    const tableBody = document.getElementById('paymentsTableBody');
    const showingCount = document.getElementById('showingCount');
    
    if (!sortSelect || !tableBody) return;
    
    let originalRows = Array.from(tableBody.querySelectorAll('tr'));
    
    function updateTable() {
        const sortBy = sortSelect.value;
        const filterUnit = filterSelect ? filterSelect.value : '';
        
        // Filter rows
        let filteredRows = originalRows.filter(row => {
            if (!filterUnit) return true;
            return row.dataset.unit === filterUnit;
        });
        
        // Sort rows
        filteredRows.sort((a, b) => {
            switch(sortBy) {
                case 'date-desc':
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
                case 'date-asc':
                    return new Date(a.dataset.date) - new Date(b.dataset.date);
                case 'unit-asc':
                    return a.dataset.unit.localeCompare(b.dataset.unit);
                case 'unit-desc':
                    return b.dataset.unit.localeCompare(a.dataset.unit);
                case 'amount-desc':
                    return parseFloat(b.dataset.amount) - parseFloat(a.dataset.amount);
                case 'amount-asc':
                    return parseFloat(a.dataset.amount) - parseFloat(b.dataset.amount);
                default:
                    return 0;
            }
        });
        
        // Clear and repopulate table
        tableBody.innerHTML = '';
        filteredRows.forEach(row => tableBody.appendChild(row));
        
        // Update count
        if (showingCount) {
            showingCount.textContent = filteredRows.length;
        }
    }
    
    sortSelect.addEventListener('change', updateTable);
    if (filterSelect) {
        filterSelect.addEventListener('change', updateTable);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const payFullButton = document.getElementById('payFullAmount');
    const summaryAmount = document.getElementById('summaryAmount');
    const remainingBalance = document.getElementById('remainingBalance');
    const paymentStatus = document.getElementById('paymentStatus');
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('receipt');
    const uploadContent = document.getElementById('uploadContent');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFileBtn = document.getElementById('removeFile');
    const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    
    const outstandingBalance =  $lease['balance'] || 0;
    
    // Payment method selection
    paymentMethodInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Remove selected class from all
            document.querySelectorAll('.payment-method-selected').forEach(el => {
                el.style.opacity = '0';
            });
            
            // Add selected class to current
            const selectedBorder = this.closest('label').querySelector('.payment-method-selected');
            selectedBorder.style.opacity = '1';
        });
    });
    
    // Initialize first payment method as selected
    if (paymentMethodInputs.length > 0) {
        paymentMethodInputs[0].checked = true;
        paymentMethodInputs[0].closest('label').querySelector('.payment-method-selected').style.opacity = '1';
    }
    
    // Pay full amount button
    payFullButton.addEventListener('click', function() {
        amountInput.value = outstandingBalance.toFixed(2);
        updateSummary();
    });
    
    // Amount input change
    amountInput.addEventListener('input', updateSummary);
    
    function updateSummary() {
        const amount = parseFloat(amountInput.value) || 0;
        const remaining = Math.max(0, outstandingBalance - amount);
        
        summaryAmount.textContent = '₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
        remainingBalance.textContent = '₱' + remaining.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        if (amount >= outstandingBalance) {
            paymentStatus.textContent = 'Paid in Full';
            paymentStatus.className = 'font-bold text-green-600';
        } else if (amount > 0) {
            paymentStatus.textContent = 'Partial Payment';
            paymentStatus.className = 'font-bold text-yellow-600';
        } else {
            paymentStatus.textContent = 'No Payment';
            paymentStatus.className = 'font-bold text-slate-600';
        }
    }
    
    // File upload functionality
    uploadArea.addEventListener('click', () => fileInput.click());
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-green-400', 'bg-green-50');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-green-400', 'bg-green-50');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-green-400', 'bg-green-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        uploadContent.classList.remove('hidden');
        filePreview.classList.add('hidden');
    });
    
    function handleFileSelect(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            alert('File size must be less than 10MB');
            return;
        }
        
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        
        uploadContent.classList.add('hidden');
        filePreview.classList.remove('hidden');
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Form validation
    const form = document.getElementById('paymentForm');
    form.addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value);
        
        if (!amount || amount <= 0) {
            e.preventDefault();
            alert('Please enter a valid payment amount');
            amountInput.focus();
            return;
        }
        
        if (amount > outstandingBalance) {
            e.preventDefault();
            alert('Payment amount cannot exceed the outstanding balance');
            amountInput.focus();
            return;
        }
        
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Please upload a receipt');
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Processing Payment...</span>
        `;
    });
});