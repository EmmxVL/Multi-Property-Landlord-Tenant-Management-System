<?php
session_start();
require_once "dbConnect.php";
require_once "maintenanceManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$manager = new MaintenanceManager($db);
$landlordId = $_SESSION["user_id"]; // Assuming landlord’s ID is stored in session

// ✅ Handle status updates
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['request_id'], $_POST['status'])) {
    $requestId = (int) $_POST['request_id'];
    $status = $_POST['status'];
    $endDate = ($status === 'Completed') ? date('Y-m-d') : null;
    $manager->updateStatus($requestId, $status, $endDate);
}

// ✅ Get all maintenance requests for this landlord
$maintenanceRequests = $manager->getRequestsByLandlord($landlordId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Maintenance Requests | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script> 
  <script src="../assets/landlord.js" defer></script>
</head>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- Header -->
  <?php include '../assets/header.php'; ?>

  <!-- Main Section -->
  <main class="flex-grow flex justify-center py-12 px-4">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-6xl rounded-3xl shadow-lg border border-slate-200 p-10 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-yellow-500 rounded-full flex items-center justify-center shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h1 class="text-3xl font-extrabold text-blue-900">Manage Maintenance Requests</h1>
        </div>

        <a href="dashboard/landlord_dashboard.php" 
           class="text-sm font-semibold text-orange-600 hover:text-orange-700 hover:underline transition-all duration-200">
           ← Back to Dashboard
        </a>
      </div>

      <!-- Empty State -->
      <?php if (empty($maintenanceRequests)): ?>
        <div class="text-center py-20">
          <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-slate-700 mb-1">No maintenance requests yet</h2>
          <p class="text-slate-500 text-sm">Tenant-submitted requests will appear here once created.</p>
        </div>

      <!-- Requests Table -->
      <?php else: ?>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
          <table class="min-w-full text-sm text-slate-700">
            <thead class="bg-gradient-to-r from-orange-100 to-yellow-100 text-slate-800 font-semibold uppercase text-xs">
              <tr>
                <th class="px-4 py-3 text-left">Request ID</th>
                <th class="px-4 py-3 text-left">Unit</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-left">Start Date</th>
                <th class="px-4 py-3 text-left">End Date</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center">Action</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-slate-200">
              <?php foreach ($maintenanceRequests as $req): ?>
                <?php
                  $status = htmlspecialchars($req['maintenance_status']);
                  $color = match ($status) {
                      'Ongoing' => 'bg-blue-100 text-blue-700',
                      'Completed' => 'bg-green-100 text-green-700',
                      'Rejected' => 'bg-red-100 text-red-700',
                      default => 'bg-slate-100 text-slate-700',
                  };
                ?>
                <tr class="hover:bg-orange-50 transition-all duration-200">
                  <td class="px-4 py-3 font-medium"><?= htmlspecialchars($req['request_id']) ?></td>
                  <td class="px-4 py-3"><?= htmlspecialchars($req['unit_name']) ?></td>
                  <td class="px-4 py-3"><?= htmlspecialchars($req['description']) ?></td>
                  <td class="px-4 py-3"><?= htmlspecialchars($req['maintenance_start_date'] ?? '-') ?></td>
                  <td class="px-4 py-3"><?= htmlspecialchars($req['maintenance_end_date'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-center">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $color ?>">
                      <?= $status ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <form method="POST" class="flex items-center justify-center gap-2">
                      <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                      <select name="status"
                              class="border border-slate-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="Ongoing" <?= $status === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                      </select>
                      <button type="submit"
                              class="bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white text-sm font-semibold px-4 py-1.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        Update
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <?php include '../assets/footer.php'; ?>

</body>
</html>
