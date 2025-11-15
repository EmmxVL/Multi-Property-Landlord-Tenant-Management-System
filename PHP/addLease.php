<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php";
require_once "leaseManager.php";
require_once "paymentManager.php"; // *** NEW: Need this to create the initial payment ***

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page_user.php");
    exit;
}

// create DB connection (PDO) using your Database class
$database = new Database();
$db = $database->getConnection();

$landlordId = (int) ($_SESSION["user_id"] ?? 0);
if ($landlordId <= 0) {
    header("Location: ../login_page_user.php");
    exit;
}

// instantiate managers with the PDO connection
$tenantManager = new TenantManager($db, $landlordId);
$leaseManager  = new LeaseManager($db);
$paymentManager = new PaymentManager($db); // *** NEW ***

// Fetch *approved* tenants belonging to this landlord who are NOT on an active lease
$tenants = $tenantManager->getAvailableTenants(); 

// Fetch *available* units owned by landlord (not on an active lease)
try {
    $stmt = $db->prepare("
        SELECT u.unit_id, u.unit_name, p.property_name
        FROM unit_tbl u
        INNER JOIN property_tbl p ON u.property_id = p.property_id
        WHERE p.user_id = :landlord_id
          AND u.unit_id NOT IN (
            SELECT la.unit_id FROM lease_tbl la WHERE la.lease_status = 'Active'
          )
        ORDER BY p.property_name, u.unit_name
    ");
    $stmt->execute([':landlord_id' => $landlordId]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $units = [];
    $error = "Database error when fetching units: " . $e->getMessage();
}

// ✅ Handle form submission
$error = $error ?? null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tenantId = $_POST["tenant_id"] ?? null;
    $unitId   = $_POST["unit_id"] ?? null;
    $start    = $_POST["lease_start_date"] ?? null;
    // End date is no longer collected
    $monthlyRent   = (float)($_POST["monthly_rent"] ?? 0);
    $initialPayment = (float)($_POST["initial_payment"] ?? 0);

    // basic validation
    if (empty($tenantId) || empty($unitId) || empty($start) || $monthlyRent <= 0) {
        $error = "Tenant, Unit, Start Date, and Monthly Rent are required.";
    } else {
        
        // --- NEW LOGIC ---
        // 1. Calculate initial balance. 
        // The balance is what they *owe*. So it's the rent MINUS their payment.
        // If they pay 3 months (30k) on a 10k/mo rent, their balance is -20k (a credit).
        $balance = $monthlyRent - $initialPayment;

        // 2. Create the lease
        // We pass NULL for end date and 'Active' for status
        $newLeaseId = $leaseManager->createLease((int)$tenantId, (int)$unitId, $start, null, $balance, 'Active');

        if ($newLeaseId > 0) {
            // 3. If lease is created, log the initial payment
            if ($initialPayment > 0) {
                // We pass 'null' for receipt path because this is a direct entry by landlord
                $paymentManager->createPayment(
                    $newLeaseId,
                    $initialPayment,
                    $start, // Payment date is same as start date
                    'Confirmed',
                    null,
                    'Initial payment (e.g., advance/deposit)',
                    $balance // The balance *after* this payment
                );
            }
            
            $_SESSION["landlord_success"] = "Lease created successfully and is now active!";
            header("Location: manageLease.php"); // Redirect to manage leases
            exit;
        } else {
            // Error message is set inside leaseManager
            $error = $_SESSION["landlord_error"] ?? "Failed to create lease.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Add Lease | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script> 
</head>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">
  

  <!-- Main Section -->
  <main class="flex-grow flex justify-center py-12 px-4">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-3xl rounded-3xl shadow-lg border border-slate-200 p-10 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Title Section -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10m-9 4h4m-9 8h18a2 2 0 002-2V7a2 2 0 00-2-2H3a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <h1 class="text-3xl font-extrabold text-blue-900">Add New Lease</h1>
        </div>

        <a href="dashboard/landlord_dashboard.php" 
           class="text-blue-600 hover:text-blue-800 font-medium transition-all duration-200">
          ← Back to Dashboard
        </a>
      </div>

      <!-- Error Message -->
      <?php if (!empty($error)): ?>
        <div class="bg-rose-100 border border-rose-300 text-rose-700 text-center p-3 rounded-xl mb-6 shadow-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Lease Form -->
      <form method="POST" class="space-y-6">
        
        <!-- Tenant Selection -->
        <div>
          <label for="tenant_id" class="block text-sm font-semibold text-blue-900 mb-2">Select Tenant</label>
          <select name="tenant_id" id="tenant_id" required
                  class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-sm transition">
            <option value="">-- Choose Tenant --</option>
            <?php foreach ($tenants as $t): ?>
              <option value="<?= htmlspecialchars($t['user_id']) ?>">
                <?= htmlspecialchars($t['full_name']) ?> 
                <?= isset($t['phone_no']) ? '(' . htmlspecialchars($t['phone_no']) . ')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-slate-500 mt-1">Only shows approved tenants not currently on an active lease.</p>
        </div>

        <!-- Unit Selection -->
        <div>
          <label for="unit_id" class="block text-sm font-semibold text-blue-900 mb-2">Select Unit</label>
          <select name="unit_id" id="unit_id" required
                  class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-sm transition">
            <option value="">-- Choose Unit --</option>
            <?php foreach ($units as $u): ?>
              <option value="<?= htmlspecialchars($u['unit_id']) ?>">
                <?= htmlspecialchars($u['unit_name'] . ' (' . $u['property_name'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-slate-500 mt-1">Only shows units not currently on an active lease.</p>
        </div>

        <!-- Lease Dates & Rent -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="lease_start_date" class="block text-sm font-semibold text-blue-900 mb-2">Lease Start Date</label>
            <input type="date" name="lease_start_date" id="lease_start_date" required
                   class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-sm transition">
          </div>
          <div>
            <label for="monthly_rent" class="block text-sm font-semibold text-blue-900 mb-2">Monthly Rent Amount (PHP)</label>
            <input type="number" name="monthly_rent" id="monthly_rent" min="0" step="0.01" placeholder="e.g., 5000" required
                   class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-sm transition">
          </div>
        </div>

        <!-- Initial Payment -->
        <div>
          <label for="initial_payment" class="block text-sm font-semibold text-blue-900 mb-2">Initial Payment Made (PHP)</label>
          <input type="number" name="initial_payment" id="initial_payment" min="0" step="0.01" placeholder="e.g., 15000 (for 3 months advance)"
                 class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white shadow-sm transition">
          <p class="text-xs text-slate-500 mt-1">Enter the amount the tenant paid today (e.g., advance, deposit). This will be logged as their first payment.</p>
        </div>

        <!-- Submit -->
        <div class="flex justify-end pt-4">
          <button type="submit"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
            ✅ Create Active Lease
          </button>
        </div>

      </form>
    </div>
  </main>



</body>
</html>