<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Enter 5‑Digit PIN — Unitly</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/script.js" defer></script>
    <script src="assets/otp_enter.js" defer></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-900">OTP</h1>
        </div>
    </header>

    <!-- Notification container (place near top so notifications appear top-right) -->
    <div id="notification-container"></div>

    <!-- OTP Form -->
    <main class="container d-flex align-items-center justify-content-center" style="min-height: 76vh;">
        <div class="w-100" style="max-width: 480px;">
            <div class="text-center mb-6">
                <img src="photos/logo.png" alt="Unitly Logo" class="h-20 w-20 rounded-full shadow mx-auto">
            </div>

            <!-- PHONE INPUT (shown first) -->
            <div id="phone-container" class="bg-white rounded-lg p-6 shadow-sm">
                <p class="text-center text-sm text-slate-600 mb-4">
                    Enter your phone number. We'll send a 5‑digit PIN to that number.
                </p>
                <form id="phone-form" class="text-center">
                    <div class="form-group mb-3">
                        <input id="phone-number" type="tel" inputmode="tel" class="form-control text-center" placeholder="+63 906 581 6503" required>
                    </div>
                    <button type="submit" id="send-code-btn" class="btn btn-primary btn-block">Send PIN</button>
                </form>
            </div>

            <!-- OTP FORM (hidden until phone submitted) -->
            <div id="otp-container" class="bg-white rounded-lg p-6 shadow-sm mt-4 hidden">
                <p class="text-center text-sm text-slate-600 mb-4">We sent a 5‑digit PIN. Enter it below to continue.</p>

                <form id="otp-form" class="text-center" onsubmit="return false;">
                    <div class="flex justify-center gap-3 mb-4">
                        <input inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input form-control text-center" aria-label="Digit 1" />
                        <input inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input form-control text-center" aria-label="Digit 2" />
                        <input inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input form-control text-center" aria-label="Digit 3" />
                        <input inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input form-control text-center" aria-label="Digit 4" />
                        <input inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input form-control text-center" aria-label="Digit 5" />
                    </div>

                    <button id="verify-btn" class="btn btn-primary btn-block mb-2" disabled>Verify PIN</button>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="#" id="resend-link" class="text-blue-600">Resend PIN</a>
                    </div>
                </form>
            </div>

            <div class="text-center mt-4">
                <a href="login.html" class="text-sm text-slate-600">Back to Login</a>
            </div>
        </div>
    </main>

    <!-- Footer (kept from existing layout) -->
    <footer class="bg-blue-900 text-white">
        <div class="max-w-7xl mx-auto px-6 py-8 text-center text-sm text-blue-200">
            © 2024 Unitly. All rights reserved.
        </div>
    </footer>
     <script src="assets/opt_enter.js"></script>
   <script src="assets/styles.css"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9881021ba47cb9f0',t:'MTc1OTM3NDY0My4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
</body>
</html>