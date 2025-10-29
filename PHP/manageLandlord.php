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
<?php include '../assets/header.php'; ?>

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
      <input type="hidden" name="action" value="update">

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


     <!-- footer -->
<?php include '../assets/footer.php'; ?>
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
<script>
document.addEventListener("DOMContentLoaded", () => {
  const passwordInput = document.getElementById("passwordInput");
  const togglePassword = document.getElementById("togglePassword");
  const eyeIcon = document.getElementById("eyeIcon");

  if (!passwordInput || !togglePassword || !eyeIcon) return;

  togglePassword.addEventListener("click", () => {
    const showing = passwordInput.type === "text";
    passwordInput.type = showing ? "password" : "text";

    // Swap eye icon SVG when toggled
    eyeIcon.innerHTML = showing
      ? `
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M2.458 12C3.732 7.943 7.523 5 12 5
             c4.478 0 8.268 2.943 9.542 7
             -1.274 4.057-5.064 7-9.542 7
             -4.477 0-8.268-2.943-9.542-7z" />
      `
      : `
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M13.875 18.825A10.05 10.05 0 0112 19
             c-4.478 0-8.268-2.943-9.542-7
             a10.034 10.034 0 013.354-4.568
             m3.218-1.704A9.956 9.956 0 0112 5
             c4.478 0 8.268 2.943 9.542 7
             a9.966 9.966 0 01-4.132 4.868M3 3l18 18" />
      `;
  });
});
</script>

</body>
</html>
