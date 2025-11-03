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
    <title>Manage Maintenance Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
   <script src="../assets/script.js" defer></script> 
    <script src="../assets/landlord.js" defer></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
    <div id="notification-container"></div>
    <?php include '../assets/header.php'; ?>
<main class="py-10 bg-gradient-to-br from-slate-50 to-white-50 min-h-screen">
  <div class="max-w-6xl mx-auto bg-white/90 backdrop-blur-lg rounded-3xl shadow-xl border border-slate-200 p-8 transition-all duration-300 hover:shadow-2xl">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-2">
        🧰 <span>Manage Maintenance Requests</span>
      </h1>
      <a href="dashboard/landlord_dashboard.php" 
         class="text-sm font-medium text-orange-600 hover:text-orange-700 hover:underline transition-all duration-200">
         ← Back to Dashboard
      </a>
    </div>

    <!-- No Requests -->
    <?php if (empty($maintenanceRequests)): ?>
      <div class="text-center py-16">
        <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z" />
          </svg>
        </div>
        <p class="text-slate-600 font-medium text-lg mb-1">No maintenance requests yet.</p>
        <p class="text-slate-400 text-sm">Tenant-submitted requests will appear here once created.</p>
      </div>

    <!-- Requests Table -->
    <?php else: ?>
      <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
        <table class="min-w-full text-sm text-slate-700">
          <thead class="bg-orange-100/60 text-slate-800 uppercase text-xs font-semibold tracking-wide">
            <tr>
              <th class="px-4 py-3 text-left">Request ID</th>
              <th class="px-4 py-3 text-left">Unit</th>
              <th class="px-4 py-3 text-left">Description</th>
              <th class="px-4 py-3 text-left">Start Date</th>
              <th class="px-4 py-3 text-left">End Date</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-center">Action</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 bg-white">
            <?php foreach ($maintenanceRequests as $req): ?>
              <?php
                $status = htmlspecialchars($req['maintenance_status']);
                $color = match ($status) {
                    'Ongoing' => 'text-blue-600 bg-blue-100',
                    'Completed' => 'text-green-600 bg-green-100',
                    'Rejected' => 'text-red-600 bg-red-100',
                    default => 'text-slate-600 bg-slate-100',
                };
              ?>
              <tr class="hover:bg-orange-50 transition-all duration-150">
                <td class="px-4 py-3 font-medium"><?= htmlspecialchars($req['request_id']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($req['unit_name']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($req['description']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($req['maintenance_start_date'] ?? '-') ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($req['maintenance_end_date'] ?? '-') ?></td>
                <td class="px-4 py-3">
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
                            class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg">
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

                        <?php include '../assets/footer.php'; ?>
</body>
</html>
