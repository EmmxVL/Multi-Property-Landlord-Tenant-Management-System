<?php
session_start();
require_once "dbConnect.php";
require_once "landlordManager.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$manager = new LandlordManager($db);

$userId = intval($_GET['user_id'] ?? 0);

if (!$userId) {
    $_SESSION['admin_error'] = "Invalid landlord ID.";
    header("Location: dashboard/admin_dashboard.php"); // Redirect to dashboard
    exit;
}

// Fetch landlord info
$landlords = $manager->getAllLandlords();
$landlord = null;
foreach ($landlords as $l) {
    if ($l['user_id'] == $userId) {
        $landlord = $l;
        break;
    }
}

if (!$landlord) {
    $_SESSION['admin_error'] = "Landlord not found.";
    header("Location: dashboard/admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Landlord</title>
        <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
   <script src="../assets/script.js" defer></script> 
    <script src="../assets/admin.js" defer></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly Admin</h1>
                    <p class="text-xs text-slate-500">
                        Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-slate-700 text-sm hidden sm:inline">
                    <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin User'); ?>
                </span>
                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-full flex items-center justify-center text-white font-semibold">
                    <?php
                        $fullName = $_SESSION['full_name'] ?? 'AU';
                        $names = explode(' ', $fullName);
                        $initials = ($names[0][0] ?? '') . ($names[1][0] ?? '');
                        echo htmlspecialchars(strtoupper($initials) ?: 'A');
                    ?>
                </div>
                <a href="logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Main -->
 <main class="flex-1 flex items-center justify-center px-4 py-12 bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
  <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-lg border border-slate-200">

    <!-- Header -->
    <div class="text-center mb-8">
      <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      </div>
      <h2 class="text-3xl font-bold text-slate-800 mb-2">Edit Landlord</h2>
      <p class="text-slate-600">Update landlord account information</p>
    </div>

    <!-- Account Info -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 mb-6 border border-blue-100">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
          <span class="text-white font-bold text-sm">
            <?= strtoupper(substr($landlord['full_name'], 0, 1)); ?>
          </span>
        </div>
        <div>
          <p class="font-semibold text-slate-800">Account ID: #<?= htmlspecialchars($landlord['user_id']); ?></p>
          <p class="text-sm text-slate-600">Last updated: <?= date('M d, Y'); ?></p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <form action="landlordManager.php" method="POST" class="space-y-6" id="editForm">
      <input type="hidden" name="user_id" value="<?= htmlspecialchars($landlord['user_id']); ?>">
      <input type="hidden" name="action" value="edit">

      <!-- Full Name -->
      <div>
        <label class="flex items-center text-slate-700 font-semibold mb-3">
          <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Full Name
        </label>
        <input type="text" name="full_name" required
          class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
          value="<?= htmlspecialchars($landlord['full_name']); ?>" placeholder="Enter full name">
      </div>

      <!-- Phone Number -->
      <div>
        <label class="flex items-center text-slate-700 font-semibold mb-3">
          <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
          Phone Number
        </label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <span class="text-slate-500 text-sm">+63</span>
          </div>
          <input type="tel" name="phone" maxlength="11" id="phoneInput"
            class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
            value="<?= htmlspecialchars($landlord['phone_no']); ?>" placeholder="9XX XXX XXXX">
        </div>
        <p class="text-xs text-slate-500 mt-1">Philippine mobile number format</p>
      </div>

      <!-- Password -->
      <div>
        <label class="flex items-center text-slate-700 font-semibold mb-3">
          <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          New Password <span class="text-xs text-slate-500 ml-2 font-normal">(optional)</span>
        </label>
        <div class="relative">
          <input type="password" name="password" id="passwordInput"
            class="w-full px-4 py-3 pr-12 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-slate-50 focus:bg-white"
            placeholder="Leave blank to keep current password">
          <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center">
            <svg class="w-5 h-5 text-slate-400 hover:text-slate-600" id="eyeIcon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
        </div>
        <p class="text-xs text-slate-500 mt-1">Leave empty to keep the current password unchanged</p>
      </div>

      <!-- Buttons -->
      <div class="flex flex-col sm:flex-row gap-3 pt-4">
        <button type="submit" id="submitBtn"
          class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>Update Landlord</span>
        </button>

        <a href="dashboard/admin_dashboard.php"
          class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-6 rounded-xl text-center transition-all duration-200 flex items-center justify-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span>Cancel</span>
        </a>
      </div>
    </form>

    <!-- Feedback Messages -->
    <div id="messageContainer" class="mt-4 hidden">
      <div id="successMessage"
        class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl hidden">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Landlord updated successfully!</span>
        </div>
      </div>

      <div id="errorMessage" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl hidden">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Please check your input and try again.</span>
        </div>
      </div>
    </div>
  </div>
</main>


    <!-- Footer -->
    <footer class="bg-blue-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">CompanyName</h3>
                    <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Vision</h4>
                    <p class="text-blue-100 text-sm leading-relaxed">
                        To revolutionize property management by fostering seamless connections between landlords and tenants.
                    </p>
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
                        <li><a href="#" class="hover:text-white text-blue-100">About Us</a></li>
                        <li><a href="#" class="hover:text-white text-blue-100">Our Services</a></li>
                        <li><a href="#" class="hover:text-white text-blue-100">Developers</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Stay Connected</h4>
                    <div class="flex space-x-4 mb-6">
                        <a href="#" class="hover:text-blue-300 text-white">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M...Z"/></svg>
                        </a>
                        <a href="#" class="hover:text-blue-300 text-white">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M...Z"/></svg>
                        </a>
                    </div>
                    <h5 class="text-lg font-medium mb-3 text-blue-200">Newsletter</h5>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="email" id="newsletter-email" placeholder="Enter your email"
                            class="w-full p-2 rounded bg-blue-800 border border-blue-700 focus:outline-none focus:border-blue-500 text-sm"
                            required>
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-sm transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center text-center md:text-left">
                <div class="text-blue-200 text-sm mb-4 md:mb-0">
                    © <?= date("Y"); ?> Unitly. All rights reserved.
                </div>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="hover:text-white text-blue-200">Privacy Policy</a>
                    <a href="#" class="hover:text-white text-blue-200">Terms of Service</a>
                    <a href="#" class="hover:text-white text-blue-200">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
<script>
<?php if (isset($_SESSION['admin_success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: <?php echo json_encode($_SESSION['admin_success']); ?>,
        timer: 3000,
        showConfirmButton: false
    });
    <?php unset($_SESSION['admin_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['admin_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: <?php echo json_encode($_SESSION['admin_error']); ?>
    });
    <?php unset($_SESSION['admin_error']); ?>
<?php endif; ?>
</script>

</body>
</html>
