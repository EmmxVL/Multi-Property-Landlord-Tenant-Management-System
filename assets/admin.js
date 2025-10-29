document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('add-landlord-modal');
  const modalContent = modal.querySelector('div.bg-white'); // target inner content
  const openBtn = document.getElementById('add-landlord-btn'); // trigger button
  const closeBtn = document.getElementById('close-modal-btn');
  const cancelBtn = document.getElementById('cancel-btn');
  const form = document.getElementById('landlord-form');
  const phoneInput = document.getElementById('phone');
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('toggle-password');
  const eyeIcon = document.getElementById('eye-icon');
  const submitBtn = document.getElementById('submit-btn');

  // ✅ Show modal
  function showModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modalContent.classList.add('transition', 'transform', 'duration-300', 'ease-out');
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  // ✅ Hide modal
  function hideModal() {
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      if (form) form.reset();
    }, 250);
  }

  // Open & Close triggers
  if (openBtn) openBtn.addEventListener('click', showModal);
  if (closeBtn) closeBtn.addEventListener('click', hideModal);
  if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

  // Close on backdrop click
  modal.addEventListener('click', (e) => {
    if (e.target === modal) hideModal();
  });

  // ✅ Phone format: +63 9XX XXX XXXX
  if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 11) value = value.slice(0, 11);
      if (value.length >= 7)
        value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
      else if (value.length >= 4)
        value = value.slice(0, 3) + ' ' + value.slice(3);
      e.target.value = value;
    });
  }

  // ✅ Password toggle
  if (togglePassword && passwordInput && eyeIcon) {
    togglePassword.addEventListener('click', () => {
      const type =
        passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      eyeIcon.innerHTML =
        type === 'text'
          ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
               d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97
               9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242
               4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>`
          : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
               d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
               d="M2.458 12C3.732 7.943 7.523 5 12
               5c4.478 0 8.268 2.943 9.542 7-1.274
               4.057-5.064 7-9.542 7-4.477
               0-8.268-2.943-9.542-7z"/>`;
    });
  }

  // ✅ Form validation & fake submit
  if (form && submitBtn) {
    form.addEventListener('submit', (e) => {

      const fullName = document.getElementById('full-name').value.trim();
      const phone = phoneInput.value.replace(/\D/g, '');
      const password = passwordInput.value;

      if (!fullName)
        return showNotification('Please enter a full name', 'error');
      if (password.length < 6)
        return showNotification('Password must be at least 6 characters', 'error');

      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10"
                  stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
                d="m4 12a8 8 0 018-8V0C5.373 0
                0 5.373 0 12h4zm2 5.291A7.962
                7.962 0 014 12H0c0 3.042
                1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Creating...`;

      setTimeout(() => {
        showNotification('Landlord account created successfully!', 'success');
        hideModal();
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
          <svg class="w-5 h-5" fill="none" stroke="currentColor"
               viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6
                  0H6"/>
          </svg> Create Account`;
      }, 2000);
    });
  }

  // ✅ Toast Notification
  function showNotification(message, type) {
    const note = document.createElement('div');
    note.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
      type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    note.textContent = message;
    document.body.appendChild(note);

    setTimeout(() => note.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
      note.classList.add('translate-x-full');
      setTimeout(() => note.remove(), 300);
    }, 3000);
  }

  // Global access
  window.showAddLandlordModal = showModal;
  window.hideAddLandlordModal = hideModal;
});
