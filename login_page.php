<?php
session_start();
$loginError = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/script.js" defer></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-blue-100">
        
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-center 2xl font-bold text-blue-900">Good day!</h1>
        </div>
    </header>


    <!-- Login Form -->
<div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="w-100" style="max-width: 400px;">
        <div class="flex justify-center mb-4">
            <img src="photos/logo.png" alt="Unitly Logo" class="h-20 w-20 rounded-full shadow">
        </div>
        <h2 class="text-center mb-4">Welcome to Unitly</h2>

        <!-- IMPORTANT: form posts to the PHP handler -->
        <form method="POST" action="PHP/login.php">
            <div class="form-group">
                <label for="phone">Phone</label>
                <!-- name changed to "phone" to match PHP -->
                <input type="tel" class="form-control" id="phone" name="phone" maxlength="11" placeholder="Phone number" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <!-- name matches PHP ("password") -->
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>

            <div class="text-center mt-2">
                <small>Forgot Password? <a href="otp_enter.html">Click here</a></small>
            </div>
        </form>
    </div>
</div>
<!-- Login Form -->
 <!-- Beautiful Footer -->
    <footer class="bg-blue-900 text-white">
        <!-- Main Footer Content -->
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- Company Info & Vision -->
                <div class="lg:col-span-1">
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">CompanyName</h3>
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Vision</h4>
                        <p class="text-blue-100 leading-relaxed text-sm">
                            To revolutionize property management by fostering seamless connections between landlords and tenants in every community.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Mission</h4>
                        <p class="text-blue-100 leading-relaxed text-sm">
                            We provide smart, transparent, and user-friendly solutions that simplify property management, enhance tenant experience, and empower landlords to manage with ease.
                        </p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="lg:col-span-1">
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Contact Us</h4>
                    <div class="space-y-4">
                        <div class="contact-item">
                           
                            <div>
                                <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
                                
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            
                            <p class="text-blue-100 text-sm">+63 (0906) 581-6503</p>
                        </div>
                        
                        <div class="contact-item">
                            
                            <p class="text-blue-100 text-sm">Unitlyph@gmail.com</p>
                        </div>
                        
                        <div class="contact-item">
                            
                            <p class="text-blue-100 text-sm">www.unitly.com</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="lg:col-span-1">
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Developers</a></li>
                        <li><a href="#" class="footer-link">Opportunities</a></li>
                        <li><a href="#" class="footer-link">News & Updates</a></li>
                        <li><a href="#" class="footer-link">Support</a></li>
                    </ul>
                </div>

                <!-- Social Media & Newsletter -->
                <div class="lg:col-span-1">
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Stay Connected</h4>
                    
                    <!-- Social Media Icons -->
                    <div class="flex space-x-4 mb-6">
                        <a href="#" class="social-icon">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.097.118.112.221.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.748-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24c6.624 0 11.99-5.367 11.99-11.987C24.007 5.367 18.641.001.012.001z.017-.001z"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Newsletter Signup -->
                    <div>
                        <h5 class="text-lg font-medium mb-3 text-blue-200">Newsletter</h5>
                        <p class="text-blue-100 text-sm mb-4">Subscribe to get updates on our latest news and offers.</p>
                        <form id="newsletter-form" class="space-y-3">
                            <input 
                                type="email" 
                                id="newsletter-email"
                                placeholder="Enter your email"
                                class="newsletter-input"
                                required
                            >
                            <button 
                                type="submit"
                                class="newsletter-btn"
                            >
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-blue-200 text-sm">
                        © 2024 Untily. All rights reserved.
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="footer-bottom-link">Privacy Policy</a>
                        <a href="#" class="footer-bottom-link">Terms of Service</a>
                        <a href="#" class="footer-bottom-link">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
<script src="assets/script.js"></script>
    
    <script>
    <?php if ($loginError): ?>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: <?php echo json_encode($loginError); ?>,
            confirmButtonText: 'Try Again'
        });
    <?php endif; ?>
    </script>
    <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'98718e843152bc4b',t:'MTc1OTIxMjYyMS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script>
</body>
</html>