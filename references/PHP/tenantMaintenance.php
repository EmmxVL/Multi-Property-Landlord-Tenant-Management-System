<?php
session_start();
require_once "dbConnect.php";
require_once "maintenanceManager.php";

// ✅ Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$maintenanceManager = new MaintenanceManager($db);

// ✅ Fetch units leased by this tenant
$stmt = $db->prepare("
    SELECT u.unit_id, u.unit_name
    FROM lease_tbl l
    JOIN unit_tbl u ON l.unit_id = u.unit_id
    WHERE l.user_id = :tenant_id AND l.lease_status = 'Active'
");
$stmt->execute([':tenant_id' => $_SESSION["user_id"]]);
$tenantUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle new maintenance request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["description"], $_POST["unit_id"])) {
    $unitId = $_POST["unit_id"];
    $userId = $_SESSION["user_id"];
    $description = trim($_POST["description"]);

    $maintenanceManager->createRequest($unitId, $userId, $description);
    header("Location: tenantMaintenance.php");
    exit;
}

// ✅ Fetch maintenance requests for this tenant (with unit names)
$requests = $maintenanceManager->getRequestsByTenant($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maintenance Requests | Unitly</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/styles.css">
   <script src="../assets/script.js" defer></script> 
    <script src="../assets/tenant.js" defer></script>
</head>
<?php include '../assets/header.php'; ?>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- Main Section -->
  <main class="flex-grow flex justify-center py-12 px-4">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-2xl rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Back Button -->
      <div class="mb-6">
        <a href="dashboard/tenant_dashboard.php" 
           class="inline-flex items-center gap-2 bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium hover:bg-blue-200 transition">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Dashboard
        </a>
      </div>

      <!-- Title -->
      <div class="text-center mb-8">
        <div class="w-14 h-14 mx-auto bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-7 h-7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
          </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-blue-900 mt-4">Maintenance Requests</h1>
        <p class="text-slate-600 text-sm mt-1">Submit and track your unit maintenance requests easily.</p>
      </div>

      <!-- Form -->
      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Select Unit</label>
          <select name="unit_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            <option value="">-- Select Unit --</option>
            <?php foreach ($tenantUnits as $unit): ?>
              <option value="<?= htmlspecialchars($unit['unit_id']) ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
          <textarea name="description" rows="4" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none" placeholder="Describe the issue..." required></textarea>
        </div>

        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
          Submit Request
        </button>
      </form>

      <!-- Divider -->
      <div class="relative my-8">
        <h3 class="text-center text-3xl font-extrabold text-blue-900 mt-4">Recent Requests</h3>
      </div>

      <!-- Maintenance List -->
      <?php if (empty($requests)): ?>
        <p class="text-center text-slate-500 italic">No maintenance requests yet.</p>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($requests as $req): ?>
            <div class="p-4 rounded-xl border shadow-sm 
              <?= $req['maintenance_status'] === 'Completed' ? 'bg-green-50 border-green-200' : 
                  ($req['maintenance_status'] === 'Rejected' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') ?>">
              <div class="flex justify-between items-center mb-1">
                <h2 class="font-semibold text-slate-800"><?= htmlspecialchars($req['unit_name'] ?? 'Unknown Unit') ?></h2>
                <span class="px-3 py-1 text-xs rounded-full font-medium 
                  <?= $req['maintenance_status'] === 'Completed' ? 'bg-green-200 text-green-800' :
                      ($req['maintenance_status'] === 'Rejected' ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800') ?>">
                  <?= htmlspecialchars($req['maintenance_status']) ?>
                </span>
              </div>
              <p class="text-slate-700 text-sm"><?= htmlspecialchars($req['description']) ?></p>
              <p class="text-xs text-slate-500 mt-2">Start: <?= htmlspecialchars($req['maintenance_start_date'] ?? 'Pending') ?> | End: <?= htmlspecialchars($req['maintenance_end_date'] ?? '---') ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <?php include '../assets/footer.php'; ?>
</body>
</html>
