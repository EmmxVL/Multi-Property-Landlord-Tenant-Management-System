<?php
session_start();

// Redirect if not logged in as Landlord
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php"); // Redirect to login page
    exit;
}

// Get session messages from the backend
$landlordSuccess = $_SESSION['landlord_success'] ?? null;
$landlordError = $_SESSION['landlord_error'] ?? null;

// Clear them so they don't show again on refresh
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Unitly - Landlord Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/script.js" defer></script>
    <script src="assets/landlord.js" defer></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    <div id="notification-container"></div>

    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly</h1>
                    <p class="text-xs text-slate-500">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?>!</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
    
                <div class="flex items-center space-x-2">
                     <span class="text-slate-700 text-sm hidden sm:inline"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?></span>
                     <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                         <?php // Initials
                             $fullName = $_SESSION['full_name'] ?? 'LU'; $names = explode(' ', $fullName);
                             $initials = ($names[0][0] ?? '') . ($names[1][0] ?? ''); echo htmlspecialchars(strtoupper($initials) ?: 'U');
                         ?>
                     </div>
                     <a href="PHP/logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                     </a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-slate-800">LandLord Dashboard Overview</h2>
            <button id="open-add-tenant-modal-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                 <span>Add New Tenant</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">My Properties</h3>
                <div class="space-y-3">
                 
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">Property 1 Name - Address</div>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">Property 2 Name - Address</div>
                </div>
                 <button class="mt-4 w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-2 px-4 rounded-lg transition-colors text-sm">Manage Properties</button>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">My Tenants</h3>
                <div class="space-y-3">
                    
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">Tenant A Name - Property 1</div>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">Tenant B Name - Property 2</div>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">Tenant C Name - Property 1</div>
                </div>
                 <button class="mt-4 w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-2 px-4 rounded-lg transition-colors text-sm">Manage Tenants</button>
            </section>

        </div>
        </main>
    <div id="add-tenant-modal" class="modal">
        <div class="modal-content">
            <form method="POST" action="PHP/landlord.php">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-800 mb-2">Add New Tenant</h3>
                    <p class="text-slate-600 text-sm">Create a new tenant account</p>
                </div>

                <div class="space-y-4 mb-6">
                    <div>
                        <label for="tenant-full-name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <input type="text" id="tenant-full-name" name="full_name" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter tenant's full name">
                    </div>

                    <div>
                        <label for="tenant-phone" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="tel" id="tenant-phone" name="phone" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="+639...">
                    </div>

                    <div>
                        <label for="tenant-password" class="block text-sm font-medium text-slate-700 mb-1">Temporary Password</label>
                        <input type="password" id="tenant-password" name="password" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter temporary password">
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition-colors">Create Tenant Account</button>
                 
                    <button type="button" id="close-add-tenant-modal-btn" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 py-3 rounded-lg transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <footer class="bg-blue-900 text-white mt-12">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">CompanyName</h3>
                    <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Vision</h4>
                    <p class="text-blue-100 leading-relaxed text-sm">To revolutionize property management by fostering seamless connections between landlords and tenants.</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Contact Us</h4>
                    <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
                    <p class="text-blue-100 text-sm">+63 (0906) 581-6503</p>
                    <p class="text-blue-100 text-sm">Unitlyph@gmail.com</p>
                    <p class="text-blue-100 text-sm">www.unitly.com</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Developers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Stay Connected</h4>
                    <div class="flex space-x-4 mb-6">
                   
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M...Z"/></svg></a>
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M...Z"/></svg></a>
                    </div>
                    <h5 class="text-lg font-medium mb-3 text-blue-200">Newsletter</h5>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="email" id="newsletter-email" placeholder="Enter your email" class="newsletter-input w-full p-2 rounded bg-blue-800 border border-blue-700 focus:outline-none focus:border-blue-500 text-sm" required>
                        <button type="submit" class="newsletter-btn w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-sm transition-colors">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center text-center md:text-left">
                <div class="text-blue-200 text-sm mb-4 md:mb-0">© <?php echo date("Y"); ?> Unitly. All rights reserved.</div>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="footer-bottom-link hover:text-white">Privacy Policy</a>
                    <a href="#" class="footer-bottom-link hover:text-white">Terms of Service</a>
                    <a href="#" class="footer-bottom-link hover:text-white">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        const addTenantModal = document.getElementById('add-tenant-modal');
        const openAddTenantBtn = document.getElementById('open-add-tenant-modal-btn');
        const closeAddTenantBtn = document.getElementById('close-add-tenant-modal-btn');

        if (openAddTenantBtn && addTenantModal) {
            openAddTenantBtn.addEventListener('click', () => {
                addTenantModal.style.display = 'flex';
            });
        }
        if (closeAddTenantBtn && addTenantModal) {
            closeAddTenantBtn.addEventListener('click', () => {
                addTenantModal.style.display = 'none';
            });
        }
        // Optional: Close modal if clicking outside the content
        window.addEventListener('click', (event) => {
            if (event.target == addTenantModal) {
                addTenantModal.style.display = 'none';
            }
        });
    </script>

    <script>
        <?php if ($landlordSuccess): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: <?php echo json_encode($landlordSuccess); ?>,
                timer: 3000, // Auto close after 3 seconds
                showConfirmButton: false
            });
        <?php elseif ($landlordError): ?>
            Swal.fire({
                icon: 'error',
                title: 'Operation Failed',
                text: <?php echo json_encode($landlordError); ?>,
                confirmButtonText: 'Okay'
            });
        <?php endif; ?>
    </script>
    </body>
</html>