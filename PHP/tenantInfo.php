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

    // ✅ FIXED: Check if tenant_info record actually exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM tenant_info_tbl WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $hasInfo = $stmt->fetchColumn() > 0;

    if ($hasInfo) {
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

<form class="grid grid-cols-1 lg:grid-cols-2 gap-8">

  <!-- LEFT COLUMN -->
  <div class="space-y-5">
    <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Basic Information</h2>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Full Name</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['full_name'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Birthdate</label>
      <input type="date" value="<?= htmlspecialchars($tenantInfo['birthdate'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Gender</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['gender'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Contact Number</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['phone_no'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
      <input type="email" value="<?= htmlspecialchars($tenantInfo['email'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mt-6">Occupation</h2>
    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Occupation</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['occupation'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Employer Name</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['employer_name'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Monthly Income</label>
      <input type="text" value="₱<?= htmlspecialchars(number_format((float)($tenantInfo['monthly_income'] ?? 0), 2)) ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="space-y-5">
    <h2 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2">Emergency Contact</h2>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Name</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['emergency_name'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Relationship</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['relationship'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-600 mb-1">Contact Number</label>
      <input type="text" value="<?= htmlspecialchars($tenantInfo['emergency_contact'] ?? '') ?>" readonly
             class="w-full border border-slate-300 rounded-xl px-4 py-2 bg-gray-100">
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
          <div class="mb-2">
            <a href="/<?= htmlspecialchars($tenantInfo[$field]) ?>" target="_blank">
              <img src="/<?= htmlspecialchars($tenantInfo[$field]) ?>" width="100" alt="<?= $label ?>" class="border rounded-xl shadow-sm hover:shadow-md transition">
            </a>
          </div>
        <?php else: ?>
          <p class="text-sm text-gray-500 italic">No <?= strtolower($label) ?> uploaded.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</form>


        <!-- BUTTONS -->
        <div class="lg:col-span-2 flex justify-between pt-8">
          <a href="dashboard/tenant_dashboard.php"
             class="bg-slate-500 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
            ← Back to Dashboard
          </a>
      
        </div>
 
    </div>
  </main>

  <?php include '../assets/footer.php'; ?>
</body>
</html>
