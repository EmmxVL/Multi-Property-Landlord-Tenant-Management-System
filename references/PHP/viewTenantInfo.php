<?php
session_start();
require_once "dbConnect.php";

// ✅ Restrict access to Landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// ✅ Get tenant ID from URL
$tenantId = (int)($_GET['id'] ?? 0);

// Fetch tenant info
$stmt = $db->prepare("SELECT 
            u.full_name, 
            t.email, 
            u.phone_no,
            t.birthdate, 
            t.age, 
            t.gender, 
            t.id_type, 
            t.id_number, 
            t.id_photo, 
            t.birth_certificate, 
            t.tenant_photo, 
            t.occupation, 
            t.employer_name, 
            t.monthly_income, 
            t.proof_of_income, 
            p.property_id, 
            un.unit_id, 
            l.lease_start_date, 
            l.lease_end_date, 
            t.monthly_rent, 
            l.lease_status, 
            t.emergency_name, 
            t.emergency_contact, 
            t.relationship
               FROM user_tbl u
        LEFT JOIN tenant_info_tbl t ON u.user_id = t.user_id
        LEFT JOIN lease_tbl l ON u.user_id = l.user_id
        LEFT JOIN unit_tbl un ON l.unit_id = un.unit_id
        LEFT JOIN property_tbl p ON un.property_id = p.property_id
        WHERE u.user_id = :user_id
         LIMIT 1");
$stmt->execute([':user_id' => $tenantId]);
$tenantInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenantInfo) {
    $_SESSION['error'] = "Tenant information not found.";
    header("Location: manageTenants.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Information | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/styles.css">
</head>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- HEADER -->
  <?php include '../assets/header.php'; ?>

  <main class="flex-grow py-10 flex justify-center">
    <div class="max-w-3xl w-full bg-white/80 backdrop-blur-md p-10 rounded-3xl shadow-lg border border-slate-200 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Title -->
      <div class="flex items-center gap-3 mb-8">
        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5.121 17.804A9 9 0 1118.879 6.196 9 9 0 015.121 17.804z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-blue-900">Tenant Information</h1>
      </div>

      <!-- Info Section -->
      <div class="space-y-4 text-slate-700">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
          <p><span class="font-semibold text-blue-800">Full Name:</span> <?= htmlspecialchars($tenantInfo['full_name']) ?></p>
          <p><span class="font-semibold text-blue-800">Birthdate:</span> <?= htmlspecialchars($tenantInfo['birthdate']) ?></p>
          <p><span class="font-semibold text-blue-800">Gender:</span> <?= htmlspecialchars($tenantInfo['gender']) ?></p>
          <p><span class="font-semibold text-blue-800">Contact Number:</span> <?= htmlspecialchars($tenantInfo['phone_no']) ?></p>
          <p><span class="font-semibold text-blue-800">Email:</span> <?= htmlspecialchars($tenantInfo['email']) ?></p>
          <p><span class="font-semibold text-blue-800">Occupation:</span> <?= htmlspecialchars($tenantInfo['occupation']) ?></p>
          <p><span class="font-semibold text-blue-800">Employer Name:</span> <?= htmlspecialchars($tenantInfo['employer_name']) ?></p>
          <p><span class="font-semibold text-blue-800">Monthly Income:</span> ₱<?= number_format($tenantInfo['monthly_income'], 2) ?></p>
          <p class="sm:col-span-2">
            <span class="font-semibold text-blue-800">Emergency Contact:</span>
            <?= htmlspecialchars($tenantInfo['emergency_name']) ?> 
            (<?= htmlspecialchars($tenantInfo['relationship']) ?>) — 
            <?= htmlspecialchars($tenantInfo['emergency_contact']) ?>
          </p>
        </div>

        <!-- Uploaded Files -->
        <div class="mt-6 space-y-2">
          <?php if (!empty($tenantInfo['id_photo'])): ?>
            <p>
              <span class="font-semibold text-blue-800">ID Photo:</span>
              <a href="../PHP/<?= htmlspecialchars($tenantInfo['id_photo']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
            </p>
          <?php endif; ?>

          <?php if (!empty($tenantInfo['birth_certificate'])): ?>
            <p>
              <span class="font-semibold text-blue-800">Birth Certificate:</span>
              <a href="../PHP/<?= htmlspecialchars($tenantInfo['birth_certificate']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
            </p>
          <?php endif; ?>

          <?php if (!empty($tenantInfo['tenant_photo'])): ?>
            <p>
              <span class="font-semibold text-blue-800">Tenant Photo:</span>
              <a href="../PHP/<?= htmlspecialchars($tenantInfo['tenant_photo']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
            </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Back Button -->
      <div class="mt-10 text-center">
        <a href="manageTenants.php"
           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-all duration-150">
          ← Back to Tenants
        </a>
      </div>
    </div>
  </main>

  <!-- FOOTER -->
  <?php include '../assets/footer.php'; ?>
</body>
</html>
