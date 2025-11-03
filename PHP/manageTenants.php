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

/* -------------------- DELETE TENANT -------------------- */
if (isset($_GET['delete'])) {
    $tenantId = (int) $_GET['delete'];

    try {
        $stmt = $db->prepare("DELETE FROM user_tbl WHERE user_id = :id AND landlord_id = :landlord_id");
        $stmt->execute([
            ':id' => $tenantId,
            ':landlord_id' => $landlordId
        ]);
        $_SESSION['success'] = "Tenant deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting tenant: " . $e->getMessage();
    }

    header("Location: manageTenants.php");
    exit;
}

/* -------------------- ADD TENANT -------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_tenant"])) {
    $fullName = trim($_POST["full_name"] ?? '');
    $phone = trim($_POST["phone_no"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($fullName) || empty($phone) || empty($password)) {
        $_SESSION["error"] = "All fields are required.";
    } else {
        try {
            $manager->createTenant($landlordId, $fullName, $phone, $password);
            $_SESSION["success"] = "Tenant added successfully.";
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error adding tenant: " . $e->getMessage();
        }
    }

    header("Location: manageTenants.php");
    exit;
}

/* -------------------- FETCH TENANTS -------------------- */
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
  <title>Manage Tenants | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../../assets/styles.css">
  <script src="../../assets/script.js" defer></script>
  <script src="../../assets/landlord.js" defer></script>
</head>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">
  
  <!-- Header -->
  <?php include '../assets/header.php'; ?>

  <main class="flex-grow py-10">
    <div class="max-w-5xl mx-auto bg-white/80 backdrop-blur-md p-8 rounded-3xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Page Title -->
      <div class="flex items-center gap-3 mb-8">
        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-blue-900">Manage Tenants</h1>
      </div>

      <!-- Add Tenant Form -->
      <form method="POST" class="space-y-5 mb-10 bg-slate-50/70 p-6 rounded-2xl border border-slate-200">
        <div>
          <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
          <input type="text" id="full_name" name="full_name"
                 class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                 placeholder="e.g., Juan Dela Cruz" required>
        </div>

        <div>
          <label for="phone_no" class="block text-sm font-semibold text-slate-700 mb-1">Phone Number</label>
          <input type="text" id="phone_no" name="phone_no" maxlength="11"
                 class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                 placeholder="e.g., 09123456789" required>
        </div>

        <div>
          <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
          <div class="relative">
            <input type="password" id="password" name="password"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                   placeholder="Set account password" required>
            <button type="button" id="toggle-password"
                    class="absolute inset-y-0 right-3 flex items-center"
                    aria-label="Toggle Password Visibility">
              <svg class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors" id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5
                         c4.478 0 8.268 2.943 9.542 7
                         -1.274 4.057-5.064 7-9.542 7
                         -4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>

        <div class="flex justify-end">
          <button type="submit" name="add_tenant"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
            ➕ Add Tenant
          </button>
        </div>
      </form>

      <!-- Tenant List -->
      <h2 class="text-2xl font-semibold text-blue-900 mb-4 flex items-center gap-2">
        🧾 Tenant List
      </h2>

      <?php if ($tenants): ?>
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
                  <td class="p-3 font-medium text-blue-700">
                    <a href="viewTenantInfo.php?id=<?= $tenant['user_id'] ?>" class="hover:underline">
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
                    <a href="#" 
                       class="delete-tenant text-red-600 hover:text-red-800 font-medium hover:underline"
                       data-id="<?= $tenant['user_id'] ?>">
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-10">
          <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <p class="text-slate-500 font-medium text-lg mb-1">No tenants found</p>
          <p class="text-slate-400 text-sm">Add a new tenant using the form above.</p>
        </div>
      <?php endif; ?>

      <!-- Back -->
      <div class="mt-8 text-center">
        <a href="dashboard/landlord_dashboard.php"
           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-all duration-150">
          ← Back to Dashboard
        </a>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php include '../assets/footer.php'; ?>

  <!-- Password Toggle -->
  <script>
  const togglePassword = document.getElementById("toggle-password");
  const passwordInput = document.getElementById("password");
  const eyeIcon = document.getElementById("eye-icon");

  togglePassword.addEventListener("click", () => {
    const isHidden = passwordInput.type === "password";
    passwordInput.type = isHidden ? "text" : "password";
  });
  </script>

  <!-- SweetAlert Messages -->
  <?php if (!empty($_SESSION["success"])): ?>
  <script>
  Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: <?= json_encode($_SESSION["success"]) ?>,
    confirmButtonColor: "#2563eb",
    timer: 2000,
    showConfirmButton: false
  });
  </script>
  <?php unset($_SESSION["success"]); endif; ?>

  <?php if (!empty($_SESSION["error"])): ?>
  <script>
  Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: <?= json_encode($_SESSION["error"]) ?>,
    confirmButtonColor: "#2563eb"
  });
  </script>
  <?php unset($_SESSION["error"]); endif; ?>

  <!-- Delete Confirmation -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".delete-tenant").forEach(link => {
      link.addEventListener("click", e => {
        e.preventDefault();
        const tenantId = link.getAttribute("data-id");

        Swal.fire({
          title: "Delete Tenant?",
          text: "Are you sure you want to remove this tenant? This action cannot be undone.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#dc2626",
          cancelButtonColor: "#6b7280",
          confirmButtonText: "Yes, delete",
          cancelButtonText: "Cancel",
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = `?delete=${tenantId}`;
          }
        });
      });
    });
  });
  </script>

</body>
</html>
