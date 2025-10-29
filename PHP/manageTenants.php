<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "AccountManager.php";

// ✅ Restrict access to Landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$manager = new AccountManager($db);

// ✅ Get landlord ID from session
$landlordId = (int)($_SESSION["user_id"] ?? 0);

// ✅ Handle tenant creation (form submission)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_tenant"])) {
    $fullName = trim($_POST["full_name"] ?? '');
    $phone = trim($_POST["phone_no"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($fullName) || empty($phone) || empty($password)) {
        $_SESSION["error"] = "All fields are required.";
    } else {
        $manager->createTenant($landlordId, $fullName, $phone, $password);
        $_SESSION["success"] = "Tenant added successfully.";
    }

    header("Location: manageTenants.php");
    exit;
}

// ✅ Fetch tenants belonging to this landlord
$stmt = $db->prepare("
    SELECT u.user_id, u.full_name, u.phone_no
    FROM user_tbl u
    INNER JOIN user_role_tbl ur ON u.user_id = ur.user_id
    WHERE ur.role_id = 2 AND u.landlord_id = :landlord_id
    ORDER BY u.full_name ASC
");
$stmt->execute([":landlord_id" => $landlordId]);
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tenants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js" defer></script> 
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
     <!-- Header -->
<?php include '../assets/header.php'; ?>
<main class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-10">
  <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
    
    <!-- Header -->
    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-2 mb-6">
      👥 <span>Manage Tenants</span>
    </h1>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION["error"])): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 text-sm">
        <?= htmlspecialchars($_SESSION["error"]) ?>
      </div>
      <?php unset($_SESSION["error"]); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION["success"])): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 text-sm">
        <?= htmlspecialchars($_SESSION["success"]) ?>
      </div>
      <?php unset($_SESSION["success"]); ?>
    <?php endif; ?>

    <!-- Add Tenant Form -->
    <form method="POST" class="space-y-4 mb-8 bg-slate-50 p-6 rounded-xl border border-slate-200">
      <div>
        <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
        <input type="text" id="full_name" name="full_name"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="e.g., Juan Dela Cruz" required>
      </div>

      <div>
        <label for="phone_no" class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
        <input type="text" id="phone_no" name="phone_no"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="e.g., 09123456789" required>
      </div>

      <div>
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
        <input type="password" id="password" name="password"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="Set an account password" required>
      </div>

      <div class="flex justify-end">
        <button type="submit" name="add_tenant"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
          ➕ Add Tenant
        </button>
      </div>
    </form>

    <!-- Tenant List -->
    <h2 class="text-2xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
      🧾 Your Tenants
    </h2>

    <?php if (count($tenants) > 0): ?>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm text-slate-700">
          <thead class="bg-slate-100 border-b border-slate-300 text-slate-800 font-semibold">
            <tr>
              <th class="p-3 text-left">Full Name</th>
              <th class="p-3 text-left">Phone Number</th>
              <th class="p-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tenants as $tenant): ?>
              <tr class="hover:bg-blue-50 transition-all duration-150 border-b border-slate-200">
                <td class="p-3 font-medium text-slate-800">
              <a href="viewTenant.php?id=<?= $tenant['user_id'] ?>" 
                class="text-blue-600 hover:text-blue-800 hover:underline transition">
                <?= htmlspecialchars($tenant["full_name"]) ?>
              </a>
          </td>

                <td class="p-3"><?= htmlspecialchars($tenant["phone_no"]) ?></td>
                <td class="p-3 text-center space-x-2">
                  <a href="updateTenants.php?id=<?= $tenant['user_id'] ?>" 
                     class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                    Edit
                  </a>
                  <span class="text-slate-400">|</span>
                  <a href="?delete=<?= $tenant['user_id'] ?>" 
                     onclick="return confirm('Are you sure you want to delete this tenant?')" 
                     class="text-red-600 hover:text-red-800 font-medium hover:underline">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div class="text-center py-10">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5m10 0a2 2 0 110 4m0-4v2m0-6V4m-6 6v10m-6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
          </svg>
        </div>
        <p class="text-slate-500 font-medium text-lg mb-1">No tenants yet</p>
        <p class="text-slate-400 text-sm">Add new tenants using the form above.</p>
      </div>
    <?php endif; ?>

    <!-- Back to Dashboard -->
    <div class="mt-8 text-center">
      <a href="dashboard/landlord_dashboard.php"
         class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-all duration-150">
        ← Back to Dashboard
      </a>
    </div>
  </div>
</main>


     
<?php include '../assets/footer.php'; ?>
</body>
</html>
z