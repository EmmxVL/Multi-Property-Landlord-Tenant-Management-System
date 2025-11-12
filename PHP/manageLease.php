<?php
// manageLease.php
session_start();

require_once "dbConnect.php";
require_once "leaseManager.php";

// Restrict to landlords
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page_user.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_page_user.php");
    exit;
}

$landlordId = (int) $_SESSION["user_id"];

// Create DB connection (PDO)
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    $_SESSION['landlord_error'] = "Database connection failed: " . $e->getMessage();
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

// Instantiate LeaseManager with PDO
$leaseManager = new LeaseManager($db);

// Handle status update form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["lease_id"], $_POST["new_status"])) {
    $leaseId = (int) $_POST["lease_id"];
    $newStatus = $_POST["new_status"];

    // *** UPDATED LOGIC ***
    // The updateLeaseStatus function now handles setting the end date if status is 'Terminated'
    if ($leaseManager->updateLeaseStatus($leaseId, $newStatus)) {
        $_SESSION["landlord_success"] = "Lease status updated successfully.";
    } else {
        $_SESSION["landlord_error"] = $_SESSION["landlord_error"] ?? "Failed to update lease status.";
    }

    // Redirect to avoid resubmission
    header("Location: manageLease.php");
    exit;
}

// Fetch leases for this landlord
$leases = $leaseManager->getLeasesByLandlord($landlordId);

// pull flash messages
$success = $_SESSION['landlord_success'] ?? null;
$error   = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Manage Leases | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script> 
</head>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- Header -->
  <?php include '../assets/header.php'; ?>

  <!-- Main Section -->
  <main class="flex-grow flex justify-center py-12 px-4">
    <section class="bg-white/80 backdrop-blur-md w-full max-w-6xl rounded-3xl shadow-lg border border-slate-200 p-10 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Page Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 class="text-3xl font-extrabold text-blue-900">Manage Leases</h1>
        </div>

        <a href="addLease.php" 
           class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2 rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all duration-200">
          + Add Lease
        </a>
      </div>

      <!-- Alerts -->
      <?php if (!empty($success)): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl text-sm shadow-sm">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="mb-4 p-4 bg-rose-100 border border-rose-300 text-rose-700 rounded-xl text-sm shadow-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Lease Table -->
      <?php if (!empty($leases)): ?>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-sm text-slate-700">
            <thead class="bg-slate-100 text-slate-800 font-semibold">
              <tr>
                <th class="p-3 text-left">Unit</th>
                <th class="p-3 text-left">Tenant</th>
                <th class="p-3 text-left">Start</th>
                <th class="p-3 text-left">End</th>
                <th class="p-3 text-right">Balance</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-center">Change Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($leases as $lease): ?>
                <tr class="hover:bg-blue-50 transition-all duration-150 border-b border-slate-200">
                  <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($lease['unit_name']) ?></td>
                  <td class="p-3">
                    <p class="font-medium text-slate-800"><?= htmlspecialchars($lease['tenant_name']) ?></p>
                    <p class="text-xs text-slate-500"><?= htmlspecialchars($lease['tenant_phone']) ?></p>
                  </td>
                  <td class="p-3"><?= date("M d, Y", strtotime($lease['lease_start_date'])) ?></td>
                  
                  <!-- *** UPDATED: Show 'Present' for NULL end dates *** -->
                  <td class="p-3">
                    <?= $lease['lease_end_date'] ? date("M d, Y", strtotime($lease['lease_end_date'])) : '<span class="font-medium text-slate-600">Present</span>' ?>
                  </td>
                  
                  <td class="p-3 text-right font-semibold <?= (float)$lease['balance'] > 0 ? 'text-rose-600' : 'text-green-600' ?>">
                    ₱<?= number_format((float)$lease['balance'], 2) ?>
                  </td>
                  <td class="p-3 text-center">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                      <?= $lease['lease_status'] === 'Active' ? 'bg-green-100 text-green-700' :
                         ($lease['lease_status'] === 'Pending' ? 'bg-yellow-100 text-yellow-700' :
                         'bg-slate-100 text-slate-600') ?>">
                      <?= htmlspecialchars($lease['lease_status']) ?>
                    </span>
                  </td>
                  <td class="p-3 text-center">
                    <form method="POST" class="flex gap-2 items-center justify-center">
                      <input type="hidden" name="lease_id" value="<?= (int)$lease['lease_id'] ?>">
                      <select name="new_status" 
                              class="border border-slate-300 px-2 py-1 rounded text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="Pending" <?= $lease['lease_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Active" <?= $lease['lease_status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Terminated" <?= $lease['lease_status'] === 'Terminated' ? 'selected' : '' ?>>Terminated</option>
                      </select>
                      <button type="submit" 
                              class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium px-3 py-1 rounded-md text-sm shadow-sm hover:shadow transition">
                        Update
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-center text-slate-500 italic mt-8">No leases found for your properties yet.</p>
      <?php endif; ?>

      <!-- Back Button -->
      <div class="mt-10 text-center">
        <a href="dashboard/landlord_dashboard.php" 
           class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-800 px-5 py-2 rounded-xl text-sm font-medium transition-all duration-200 shadow-sm hover:shadow">
          ← Back to Dashboard
        </a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <?php include '../assets/footer.php'; ?>
</body>
</html>