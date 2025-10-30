<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php";
require_once "leaseManager.php";

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

// create DB connection (PDO) using your Database class
$database = new Database();
$db = $database->getConnection();

$landlordId = (int) ($_SESSION["user_id"] ?? 0);
if ($landlordId <= 0) {
    header("Location: ../login_page.php");
    exit;
}

// instantiate managers with the PDO connection
$tenantManager = new TenantManager($db, $landlordId);
$leaseManager  = new LeaseManager($db);

// Fetch tenants belonging to this landlord (TenantManager handles landlord linking internally)
$tenants = $tenantManager->getTenantsInfo();

// Fetch available units owned by landlord
try {
    $stmt = $db->prepare("
        SELECT u.unit_id, u.unit_name
        FROM unit_tbl u
        INNER JOIN property_tbl p ON u.property_id = p.property_id
        WHERE p.user_id = :landlord_id
        ORDER BY u.unit_name ASC
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
    $end      = $_POST["lease_end_date"] ?? null;
    $balance  = $_POST["balance"] ?? 0;

    // basic validation
    if (empty($tenantId) || empty($unitId) || empty($start) || empty($end)) {
        $error = "All fields except balance are required.";
    } else {
        // optional: further validate dates, numeric values
        $success = $leaseManager->createLease((int)$tenantId, (int)$unitId, $start, $end, (int)$balance);

        if ($success) {
            $_SESSION["landlord_success"] = "Lease created successfully!";
            // redirect back to landlord dashboard (adjust path if needed)
            header("Location: dashboard/landlord_dashboard.php");
            exit;
        } else {
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
    <title>Add Lease</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js" defer></script> 
</head>
<body>
 <!-- Header -->
<?php include '../assets/header.php'; ?>
<main class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-12">
  <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-3xl border border-slate-200 p-8">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
        📝 Add New Lease
      </h1>
      <a href="dashboard/landlord_dashboard.php" 
         class="text-blue-600 hover:text-blue-800 font-medium transition-all duration-200">
        ← Back to Dashboard
      </a>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
      <div class="bg-rose-100 border border-rose-300 text-rose-700 text-center p-3 rounded-xl mb-6">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Add Lease Form -->
    <form method="POST" class="space-y-6">
      
      <!-- Tenant Selection -->
      <div>
        <label for="tenant_id" class="block text-sm font-semibold text-slate-700 mb-2">Select Tenant</label>
        <select name="tenant_id" id="tenant_id" required
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
          <option value="">-- Choose Tenant --</option>
          <?php foreach ($tenants as $t): ?>
            <option value="<?= htmlspecialchars($t['user_id']) ?>">
              <?= htmlspecialchars($t['full_name']) ?> 
              <?= isset($t['phone_no']) ? '(' . htmlspecialchars($t['phone_no']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Unit Selection -->
      <div>
        <label for="unit_id" class="block text-sm font-semibold text-slate-700 mb-2">Select Unit</label>
        <select name="unit_id" id="unit_id" required
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
          <option value="">-- Choose Unit --</option>
          <?php foreach ($units as $u): ?>
            <option value="<?= htmlspecialchars($u['unit_id']) ?>">
              <?= htmlspecialchars($u['unit_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Lease Dates -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="lease_start_date" class="block text-sm font-semibold text-slate-700 mb-2">Lease Start Date</label>
          <input type="date" name="lease_start_date" id="lease_start_date" required
                 class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>
        <div>
          <label for="lease_end_date" class="block text-sm font-semibold text-slate-700 mb-2">Lease End Date</label>
          <input type="date" name="lease_end_date" id="lease_end_date" required
                 class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>
      </div>

      <!-- Balance -->
      <div>
        <label for="balance" class="block text-sm font-semibold text-slate-700 mb-2">Initial Balance (Optional)</label>
        <input type="number" name="balance" id="balance" min="0" placeholder="0"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
          ✅ Create Lease
        </button>
      </div>
    </form>
  </div>
</main>
    <!-- Footer -->
<?php include '../assets/footer.php'; ?>
</body>
</html>
