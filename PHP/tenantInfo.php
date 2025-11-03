<?php
session_start();
require_once "dbConnect.php";
require_once "TenantInfoManager.php";

// Restrict access to tenants
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Tenant" || !isset($_SESSION['user_id'])) {
    header("Location: ../../login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$tenantManager = new TenantInfoManager($db);
$userId = (int) $_SESSION['user_id'];

// Fetch existing tenant info
$tenantInfo = $tenantManager->getTenantInfo($userId);

// Handle file deletion
if (isset($_GET['delete_file'])) {
    $fileField = $_GET['delete_file'];

    if (!empty($tenantInfo[$fileField]) && file_exists($tenantInfo[$fileField])) {
        unlink($tenantInfo[$fileField]); // Delete the file
    }

    // Update database
    $stmt = $db->prepare("UPDATE tenant_info_tbl SET $fileField = NULL WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);

    // Refresh tenant info
    $tenantInfo = $tenantManager->getTenantInfo($userId);
}

// Handle form submission
$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $data = $_POST;
    $files = $_FILES;

    // Add user_id and existing file paths to data array
    $data['user_id'] = $userId;
    $data['existing_id_photo'] = $tenantInfo['id_photo'] ?? null;
    $data['existing_birth_certificate'] = $tenantInfo['birth_certificate'] ?? null;
    $data['existing_tenant_photo'] = $tenantInfo['tenant_photo'] ?? null;
    $data['existing_proof_of_income'] = $tenantInfo['proof_of_income'] ?? null;

    if ($tenantInfo) {
        $updated = $tenantManager->updateTenantInfo($userId, $data, $files);
        $success = $updated ? "Tenant information updated successfully." : "Failed to update tenant info.";
    } else {
        $created = $tenantManager->createTenantInfo($data, $files);
        $success = $created ? "Tenant information saved successfully." : "Failed to save tenant info.";
    }

    // Refresh info after saving
    $tenantInfo = $tenantManager->getTenantInfo($userId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Information | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script> 
  <script src="../assets/tenant.js" defer></script>
</head>

<?php include '../assets/header.php'; ?>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <main class="flex-grow flex items-center justify-center py-12 px-6">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-6xl rounded-3xl shadow-lg border border-slate-200 p-10 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Title -->
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-extrabold text-blue-900 flex items-center gap-2">
          👤 <span>My Information</span>
        </h1>
      </div>

      <!-- SweetAlert Notifications -->
      <?php if ($success): ?>
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: <?= json_encode($success) ?>,
            confirmButtonColor: '#2563eb'
          });
        </script>
      <?php elseif ($error): ?>
        <script>
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: <?= json_encode($error) ?>,
            confirmButtonColor: '#2563eb'
          });
        </script>
      <?php endif; ?>

      <!-- FORM -->
      <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- LEFT COLUMN -->
        <div class="space-y-5">
          <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Basic Information</h2>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($tenantInfo['full_name'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder:text-slate-400">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Birthdate</label>
            <input type="date" name="birthdate" value="<?= htmlspecialchars($tenantInfo['birthdate'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Gender</label>
            <select name="gender" class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
              <option value="">Select Gender</option>
              <option value="Male" <?= (isset($tenantInfo['gender']) && $tenantInfo['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= (isset($tenantInfo['gender']) && $tenantInfo['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Contact Number</label>
            <input type="text" name="phone_no" maxlength="11"
                   value="<?= htmlspecialchars($tenantInfo['phone_no'] ?? '') ?>"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($tenantInfo['email'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>

          <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mt-6">Occupation</h2>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Occupation</label>
            <input type="text" name="occupation" value="<?= htmlspecialchars($tenantInfo['occupation'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Employer Name</label>
            <input type="text" name="employer_name" value="<?= htmlspecialchars($tenantInfo['employer_name'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Monthly Income</label>
            <input type="number" step="0.01" name="monthly_income" value="<?= htmlspecialchars($tenantInfo['monthly_income'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="space-y-5">
          <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Emergency Contact</h2>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Name</label>
            <input type="text" name="emergency_name" value="<?= htmlspecialchars($tenantInfo['emergency_name'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Relationship</label>
            <input type="text" name="relationship" value="<?= htmlspecialchars($tenantInfo['relationship'] ?? '') ?>"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Contact Number</label>
            <input type="text" name="emergency_contact" maxlength="11"
                   value="<?= htmlspecialchars($tenantInfo['emergency_contact'] ?? '') ?>"
                   oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"
                   class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
          </div>

          <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mt-6">Documents</h2>
          <?php
          $fileFields = [
            'id_photo' => 'ID Photo',
            'birth_certificate' => 'Birth Certificate',
            'tenant_photo' => 'Tenant Photo',
            'proof_of_income' => 'Proof of Income'
          ];
          foreach ($fileFields as $field => $label):
          ?>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1"><?= $label ?></label>
            <?php if(!empty($tenantInfo[$field])): ?>
              <div class="mb-2 flex items-center gap-3">
                <a href="<?= htmlspecialchars($tenantInfo[$field]) ?>" target="_blank">
                  <img src="<?= htmlspecialchars($tenantInfo[$field]) ?>" width="100" alt="<?= $label ?>" class="border rounded-xl shadow-sm hover:shadow-md transition">
                </a>
                <a href="?delete_file=<?= $field ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs">Delete</a>
              </div>
            <?php endif; ?>
            <input type="file" name="<?= $field ?>"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
          </div>
          <?php endforeach; ?>
        </div>

        <!-- BUTTONS -->
        <div class="lg:col-span-2 flex justify-between pt-8">
          <a href="dashboard/tenant_dashboard.php"
             class="bg-slate-500 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
            ← Back to Dashboard
          </a>
          <button type="submit"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
            💾 Save Information
          </button>
        </div>
      </form>
    </div>
  </main>

  <?php include '../assets/footer.php'; ?>
</body>
</html>
