// Handles phone -> send PIN -> show OTP inputs -> verify flow

document.addEventListener('DOMContentLoaded', function () {
    const phoneForm = document.getElementById('phone-form');
    const phoneInput = document.getElementById('phone-number');
    const sendBtn = document.getElementById('send-code-btn');

    const phoneContainer = document.getElementById('phone-container');
    const otpContainer = document.getElementById('otp-container');

    const inputs = Array.from(document.querySelectorAll('.otp-input'));
    const verifyBtn = document.getElementById('verify-btn');
    const resendLink = document.getElementById('resend-link');

    // safe access to shared notifier
    const notify = (msg, type = 'info') => (window.UnitlyUtils?.showNotification ?? ((m) => alert(m)))(msg, type);

    // Validate basic phone (digits and length) - tweak as needed
    function normalizePhone(value) {
        return (value || '').replace(/[^\d+]/g, '');
    }
    function isValidPhone(v) {
        const digits = normalizePhone(v).replace(/\D/g, '');
        return digits.length >= 7; // basic check
    }

    // When phone form submitted: send PIN (simulated), show notification, reveal OTP inputs
    phoneForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const raw = phoneInput.value.trim();
        if (!isValidPhone(raw)) {
            notify('Please enter a valid phone number', 'error');
            phoneInput.focus();
            return;
        }

        const formatted = raw;
        // simulate sending PIN
        notify(`We sent a 5‑digit PIN to ${formatted}`, 'info');

        // reveal OTP area
        phoneContainer.classList.add('hidden');
        otpContainer.classList.remove('hidden');

        // focus first OTP input after a short delay
        setTimeout(() => inputs[0]?.focus(), 120);
    });

    // OTP input behavior (navigation, paste)
    if (inputs.length) {
        inputs.forEach((input, idx) => {
            input.addEventListener('input', () => {
                input.value = (input.value || '').replace(/\D/g, '').slice(0, 1);
                if (input.value && idx < inputs.length - 1) inputs[idx + 1].focus();
                updateButtonState();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    inputs[idx - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, inputs.length);
                for (let i = 0; i < pasted.length; i++) {
                    inputs[i].value = pasted[i];
                }
                if (pasted.length) {
                    const next = Math.min(pasted.length, inputs.length - 1);
                    inputs[next].focus();
                }
                updateButtonState();
            });
        });
    }

    function updateButtonState() {
        const code = inputs.map(i => i.value).join('');
        verifyBtn.disabled = code.length !== inputs.length;
    }

    verifyBtn?.addEventListener('click', () => {
        const code = inputs.map(i => i.value).join('');
        if (code.length !== inputs.length) {
            notify(`Please enter the ${inputs.length}-digit PIN`, 'error');
            return;
        }
        // placeholder verification; replace with actual API call
        notify('PIN verified — redirecting...', 'success');
        setTimeout(() => {
            window.location.href = 'admin_dashboard.html';
        }, 700);
    });

    resendLink?.addEventListener('click', (e) => {
        e.preventDefault();
        // simulate resend
        notify('PIN resent. Check your messages.', 'info');
    });

    // if you want to allow direct access to OTP page (skip phone), focus first input
    if (!phoneContainer || phoneContainer.classList.contains('hidden')) {
        setTimeout(() => inputs[0]?.focus(), 120);
    } else {
        // otherwise focus phone input
        phoneInput?.focus();
    }

    updateButtonState();
});