<?php
// manageLease.php
session_start();

require_once "dbConnect.php";
require_once "leaseManager.php";

// Restrict to landlords
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_page.php");
    exit;
}

$landlordId = (int) $_SESSION["user_id"];

// Create DB connection (PDO) using your Database class from dbConnect.php
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    // If DB can't be created, show useful message
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

<body>
  <?php include '../assets/header.php'; ?>

  <main class="max-w-6xl mx-auto py-10 px-6">
    <section class="bg-white shadow-lg rounded-2xl p-8 border border-slate-200">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Manage Leases
        </h2>
        <a href="addLease.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-medium text-sm shadow-md transition-all duration-200">
          + Add Lease
        </a>
      </div>

      <!-- Alerts -->
      <?php if (!empty($success)): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg border border-green-300">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg border border-red-300">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Lease Table -->
      <?php if (!empty($leases)): ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm border-collapse">
            <thead class="bg-slate-100 text-slate-700">
              <tr>
                <th class="border p-3 text-left font-semibold">Unit</th>
                <th class="border p-3 text-left font-semibold">Tenant</th>
                <th class="border p-3 text-left font-semibold">Start</th>
                <th class="border p-3 text-left font-semibold">End</th>
                <th class="border p-3 text-right font-semibold">Balance</th>
                <th class="border p-3 text-center font-semibold">Status</th>
                <th class="border p-3 text-center font-semibold">Change Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($leases as $lease): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                  <td class="border p-3"><?= htmlspecialchars($lease['unit_name']) ?></td>
                  <td class="border p-3">
                    <?= htmlspecialchars($lease['tenant_name']) ?><br>
                    <span class="text-xs text-slate-500"><?= htmlspecialchars($lease['tenant_phone']) ?></span>
                  </td>
                  <td class="border p-3"><?= htmlspecialchars($lease['lease_start_date']) ?></td>
                  <td class="border p-3"><?= htmlspecialchars($lease['lease_end_date']) ?></td>
                  <td class="border p-3 text-right font-medium text-rose-600">
                    ₱<?= number_format((float)$lease['balance'], 2) ?>
                  </td>
                  <td class="border p-3 text-center">
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                      <?= $lease['lease_status'] === 'Active' ? 'bg-emerald-100 text-emerald-700' :
                         ($lease['lease_status'] === 'Pending' ? 'bg-amber-100 text-amber-700' :
                         'bg-slate-100 text-slate-600') ?>">
                      <?= htmlspecialchars($lease['lease_status']) ?>
                    </span>
                  </td>
                  <td class="border p-3 text-center">
                    <form method="POST" class="flex gap-2 items-center justify-center">
                      <input type="hidden" name="lease_id" value="<?= (int)$lease['lease_id'] ?>">
                      <select name="new_status" class="border p-1 rounded text-sm focus:ring-2 focus:ring-blue-300">
                        <option value="Pending" <?= $lease['lease_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Active" <?= $lease['lease_status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Terminated" <?= $lease['lease_status'] === 'Terminated' ? 'selected' : '' ?>>Terminated</option>
                      </select>
                      <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
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
        <p class="text-slate-500 italic mt-6">No leases found for your properties yet.</p>
      <?php endif; ?>

      <!-- Back Button -->
      <div class="mt-8 text-center">
        <a href="dashboard/landlord_dashboard.php" class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-800 px-5 py-2 rounded-xl text-sm font-medium transition-all duration-200 shadow-sm hover:shadow">
          ← Back to Dashboard
        </a>
      </div>
    </section>
  </main>

  <?php include '../assets/footer.php'; ?>
</body>
</html>

