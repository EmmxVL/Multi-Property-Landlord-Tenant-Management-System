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