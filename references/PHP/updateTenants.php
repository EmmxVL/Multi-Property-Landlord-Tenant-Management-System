<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php";

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$tenantManager = new TenantManager($db, $userId);

$id = (int)($_GET["id"] ?? 0);
$tenants = $tenantManager->getTenantsInfo();
$tenant = null;

foreach ($tenants as $t) {
    if ($t["user_id"] == $id) {
        $tenant = $t;
        break;
    }
}

if (!$tenant) {
    die("Tenant not found or unauthorized.");
}

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"] ?? "");

    if ($tenantManager->updateTenant($id, $fullName, $phone, $password ?: null)) {
        $_SESSION["success"] = "Tenant updated successfully.";
        header("Location: manageTenants.php");
        exit;
    } else {
        $_SESSION["error"] = "Failed to update tenant.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Unitly - Edit Tenant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- HEADER -->
  <?php include '../assets/header.php'; ?>

  <!-- MAIN CONTENT -->
  <main class="flex-grow flex justify-center py-10">
    <div class="w-full max-w-3xl bg-white/80 backdrop-blur-md p-10 rounded-3xl shadow-lg border border-slate-200 hover:shadow-2xl transition-all duration-300">
      
      <!-- Title -->
      <div class="flex items-center gap-3 mb-8">
        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 20h9M12 4H3m9 0a9 9 0 110 16a9 9 0 010-16z" />
          </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-blue-900">Edit Tenant</h1>
      </div>

      <!-- Form -->
      <form method="POST" class="space-y-6">
        
        <!-- Full Name -->
        <div>
          <label class="block text-sm font-medium text-blue-800 mb-1">Full Name</label>
          <input type="text" name="full_name"
                 value="<?= htmlspecialchars($tenant['full_name']) ?>"
                 class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition-all"
                 required>
        </div>

        <!-- Phone Number -->
        <div>
          <label class="block text-sm font-medium text-blue-800 mb-1">Phone Number</label>
          <input type="text" name="phone"
                 value="<?= htmlspecialchars($tenant['phone_no']) ?>"
                 class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition-all"
                 required>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-blue-800 mb-1">New Password <span class="text-slate-500 text-xs">(optional)</span></label>
          <input type="password" name="password"
                 placeholder="Leave blank to keep current password"
                 class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm transition-all">
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-6">
          <a href="manageTenants.php"
             class="bg-gradient-to-r from-slate-200 to-slate-300 hover:from-slate-300 hover:to-slate-400 text-slate-700 font-medium px-6 py-2.5 rounded-lg shadow-sm hover:shadow-md transition-all">
            Cancel
          </a>
          <button type="submit"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all">
            💾 Save Changes
          </button>
        </div>

      </form>
    </div>
  </main>

  <!-- FOOTER -->
  <?php include '../assets/footer.php'; ?>

</body>
</html>
