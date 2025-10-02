document.addEventListener('DOMContentLoaded', function() {
    const { showNotification, openModal, closeModal } = window.UnitlyUtils;

    // Add landlord-specific logic here

    setTimeout(() => showNotification('Welcome to Unitly Landlord Dashboard!', 'success'), 1000);
});