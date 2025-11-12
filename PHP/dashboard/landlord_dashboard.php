<?php
session_start();

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../../login_page_user.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login_page_user.php");
    exit;
}


require_once "../dbConnect.php";
require_once "../propertyManager.php";
require_once "../tenantManager.php"; // <-- This now includes your new, merged class
require_once "../leaseManager.php";
require_once "../paymentManager.php";
require_once "../messageManager.php";


// ✅ Create DB connection (PDO)
$database = new Database();
$db = $database->getConnection();

// ✅ Initialize managers
$userId = (int) $_SESSION["user_id"];
$propertyManager = new PropertyManager($db, $userId);
$tenantManager = new TenantManager($db, $userId); // <-- This now uses the new, merged class
$leaseManager = new LeaseManager($db);
$paymentManager = new PaymentManager($db);
$messageManager = new MessageManager($db);

// ✅ Fetch landlord's properties, tenants & leases
$properties = $propertyManager->getProperties();
$tenants = $tenantManager->getTenantsInfo(); // <-- This uses the correct function from your new class
$leases = $leaseManager->getLeasesByLandlord($userId);
$recentMessages = $messageManager->getRecentMessagesByLandlord($userId);

// *** NEW: Get pending tenant applications for this landlord ***
try {
    $stmt = $db->prepare("
        SELECT COUNT(u.user_id)
        FROM user_tbl u
        JOIN user_role_tbl ur ON u.user_id = ur.user_id
        JOIN tenant_info_tbl ti ON u.user_id = ti.user_id
        JOIN unit_tbl ut ON ti.requested_unit_id = ut.unit_id
        JOIN property_tbl p ON ut.property_id = p.property_id
        WHERE u.status = 'pending'
          AND ur.role_id = 2
          AND p.user_id = :landlord_id
    ");
    $stmt->execute([':landlord_id' => $userId]);
    $pendingTenantApps = $stmt->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $pendingTenantApps = 0;
    // You might want to log this error
}


// Fetch payments for each lease
$paymentsByLease = [];
foreach ($leases as $lease) {
    $paymentsByLease[$lease['lease_id']] = $paymentManager->getPaymentsByLease($lease['lease_id']);
}

// ✅ Landlord session messages
$landlordSuccess = $_SESSION['landlord_success'] ?? null;
$landlordError = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);

try {
    $getPaymentsByTenant = $paymentManager->getPaymentsByLandlord($userId);
    $totalEarnings = 0;

    foreach ($getPaymentsByTenant as $payment) {
        if (!empty($payment['status']) && strtolower($payment['status']) === 'confirmed') {
            $totalEarnings += (float)$payment['amount'];
        }
    }
} catch (Exception $e) {
    $getPaymentsByTenant = [];
    $totalEarnings = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Unitly - Landlord Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../../assets/styles.css">
    <script src="../../assets/script.js" defer></script>
    <script src="../../landlord/script.js" defer></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

<?php include '../../assets/header.php'; ?>

<main class="flex-grow max-w-7xl mx-auto px-6 py-10 w-full">
  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-semibold text-slate-800 tracking-tight">Landlord Dashboard Overview</h2>
  </div>
  
  <!-- Quick Stats -->
      
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in">

    <!-- Number of Properties -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-slate-600 text-sm font-medium">Total Properties</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">
                <?= !empty($properties) ? count($properties) : 0 ?>
            </p>
            <p class="text-xs text-slate-600 mt-1">
                Active properties managed.
            </p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"/>
            </svg>
        </div>
    </div>
</div>

    <!-- Total Tenants -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-slate-600 text-sm font-medium">Active Tenants</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">
                <?= !empty($tenants) ? count($tenants) : 0 ?>
            </p>
            <p class="text-xs text-emerald-600 mt-1">
                Tenants currently renting units.
            </p>
        </div>
        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
                <circle cx="12" cy="7" r="4" stroke-width="2" />
            </svg>
        </div>
    </div>
</div>


<!-- *** NEW: PENDING APPLICATIONS CARD *** -->
<a href="../landlordApplications.php" class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card hover:bg-slate-50 transition-colors">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-slate-600 text-sm font-medium">Pending Applications</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">
                <?= $pendingTenantApps ?>
            </p>
            <p class="text-xs <?= $pendingTenantApps > 0 ? 'text-orange-600' : 'text-slate-600' ?> mt-1">
                Tenants awaiting your approval.
            </p>
        </div>

        <div class="w-12 h-12 <?= $pendingTenantApps > 0 ? 'bg-orange-100' : 'bg-gray-100' ?> rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 <?= $pendingTenantApps > 0 ? 'text-orange-600' : 'text-gray-500' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
</a>



    <!-- Total Cash Earned -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-slate-600 text-sm font-medium">Total Cash Earned</p>
           <p class="text-3xl font-bold text-slate-800 mt-1">
                ₱<?= number_format($totalEarnings, 2) ?>
            </p>
            <p class="text-xs text-purple-600 mt-1">
                From confirmed tenants payments (<?= count($getPaymentsByTenant) ?> total records)
            </p>
        </div>

        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
            </svg>
        </div>
    </div>
</div>


</div>

  <!-- Quick Overview -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- My Properties -->
    <section class="bg-white rounded-3xl shadow-lg border border-slate-100 hover:shadow-xl transition-all duration-300 p-8">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <h3 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
      <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5
                   M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
      </div>
      My Properties
    </h3>

    <a href="../manageProperties.php"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-semibold
              transition-all duration-200 shadow-md hover:shadow-lg">
      Manage All
    </a>
  </div>

  <!-- Property List -->
  <div class="space-y-4">
    <?php if (!empty($properties)): ?>
      <?php foreach ($properties as $property): ?>
        <div class="group p-6 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50
                   border border-slate-200 rounded-2xl hover:shadow-lg hover:border-blue-300
                   transition-all duration-300 cursor-pointer">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h4 class="font-bold text-slate-800 text-lg mb-2 group-hover:text-blue-700 transition-colors">
                <?= htmlspecialchars($property['property_name']) ?>
              </h4>
              <div class="flex items-center gap-2 text-slate-600">
                <div class="w-5 h-5 bg-slate-200 rounded-lg flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <span class="text-sm font-medium">
                  <?= htmlspecialchars($property['location_name']) ?>
                </span>
              </div>
            </div>

            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    <?php else: ?>
      <!-- Empty State -->
      <div class="text-center py-12">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5
                     M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <p class="text-slate-500 font-medium text-lg mb-2">No properties yet</p>
        <p class="text-slate-400 text-sm">Add your first property to get started</p>
      </div>
    <?php endif; ?>
  </div>
</section>


  <!-- My Tenants -->
<section class="bg-white rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-xl">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <h3 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
      <div class="p-2 bg-emerald-100 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
          <circle cx="12" cy="7" r="4" stroke-width="2" />
        </svg>
      </div>
      My Tenants
    </h3>


    <a href="../manageTenants.php"
      class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg flex items-center gap-2 transition-all duration-200">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
      </svg>
      Manage
    </a>
  </div>

  <!-- Tenant List -->
  <div class="space-y-4">
    <?php if (!empty($tenants)): ?>
      <?php foreach (array_slice($tenants, 0, 3) as $tenant): // Show only first 3 tenants ?>
        <div class="tenant-card p-5 border border-emerald-200 rounded-2xl hover:shadow-md transition-all duration-200">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <h4 class="font-bold text-slate-800 text-lg mb-1">
                <?= htmlspecialchars($tenant['full_name']) ?>
              </h4>
              <div class="flex items-center gap-2 text-slate-600">
                <span class="text-lg">📞</span>
                <span class="font-medium">
                  <?= htmlspecialchars($tenant['phone_no']) ?>
                </span>
              </div>
            </div>

            <!-- Tenant Status -->
            <div class="flex items-center gap-2">
              <?php
                // Use $tenant['status'] from the new getTenantsInfo()
                $statusClass = 'bg-slate-400';
                $statusText = 'Inactive';
                if (!empty($tenant['status']) && strtolower($tenant['status']) === 'approved') {
                    // Check if they are on an active lease
                    $isOnActiveLease = false;
                    foreach($leases as $lease) {
                        if ($lease['tenant_name'] === $tenant['full_name'] && $lease['lease_status'] === 'Active') {
                            $isOnActiveLease = true;
                            break;
                        }
                    }
                    
                    if ($isOnActiveLease) {
                        $statusClass = 'bg-emerald-500';
                        $statusText = 'Active';
                    } else {
                        $statusClass = 'bg-yellow-500';
                        $statusText = 'Approved'; // Approved but no active lease
                    }
                }
              ?>
              <div class="w-3 h-3 <?= $statusClass ?> rounded-full <?= $statusClass === 'bg-emerald-500' ? 'animate-pulse' : '' ?>"></div>
              <span class="text-xs font-medium <?= str_replace('bg-', 'bg-', str_replace('-500', '-100', $statusClass)) . ' ' . str_replace('bg-', 'text-', str_replace('-500', '-700', $statusClass)) ?> px-2 py-1 rounded-full">
                <?= $statusText ?>
              </span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Empty State -->
      <div class="text-center py-12">
        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
            <circle cx="12" cy="7" r="4" stroke-width="2" />
          </svg>
        </div>
        <p class="text-slate-500 font-medium text-lg mb-2">No tenants yet</p>
        <p class="text-slate-400 text-sm">Add tenants by creating new leases.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Lease Agreements + Maintenance Requests -->

<!-- Lease Agreements -->
<section class="bg-white rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-xl">
  <div class="flex items-center justify-between mb-6">
    <h3 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
      <div class="p-2 bg-indigo-100 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      Lease Agreements
    </h3>

    <div class="flex space-x-2">
      <a href="../addLease.php" 
         class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Lease
      </a>

      <a href="../manageLease.php" 
         class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-6 py-2.5 rounded-xl font-semibold text-sm shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
        Manage
      </a>
    </div>
  </div>

  <div class="space-y-5">
    <?php if (!empty($leases)): ?>
      <?php foreach ($leases as $lease): ?>
        <div class="p-5 border border-slate-200 rounded-2xl bg-gradient-to-r from-indigo-50 to-indigo-100 hover:shadow-md transition-all duration-200">
          <div class="flex justify-between items-center mb-2">
            <div>
              <h4 class="font-bold text-slate-800 text-lg mb-1">
                <?= htmlspecialchars($lease['unit_name']) ?>
              </h4>
              <p class="text-sm text-slate-600">
                👤 Tenant: <span class="font-medium text-slate-700"><?= htmlspecialchars($lease['tenant_name']) ?></span>
              </p>
            </div>
            <span class="text-sm px-3 py-1 rounded-full font-medium
              <?= $lease['lease_status'] === 'Active' 
                ? 'bg-emerald-100 text-emerald-700' 
                : 'bg-slate-100 text-slate-600' ?>">
              <?= htmlspecialchars($lease['lease_status']) ?>
            </span>
          </div>
          <div class="mt-2 text-sm text-slate-700">
            🗓 <span class="font-medium">Start:</span> <?= htmlspecialchars($lease['lease_start_date']) ?> 
            | <span class="font-medium">End:</span> <?= htmlspecialchars($lease['lease_end_date']) ?><br>
            💰 <span class="font-medium">Balance:</span> 
            <span class="text-rose-600 font-semibold">₱<?= number_format($lease['balance'], 2) ?></span>
          </div>

          <!-- View/Update Payments Button -->
          <?php
            $recentPayment = !empty($paymentsByLease[$lease['lease_id']]) ? end($paymentsByLease[$lease['lease_id']]) : null;
          ?>
          <p class="text-sm mt-2">
            <?php if ($recentPayment): ?>
              <strong>Recent Payment:</strong> ₱<?= number_format($recentPayment['amount'],2) ?> on <?= htmlspecialchars($recentPayment['payment_date']) ?> | Status: <?= htmlspecialchars($recentPayment['status']) ?>
            <?php else: ?>
              <span class="text-gray-500 italic">No payments recorded yet.</span>
            <?php endif; ?>
          </p>

          <button onclick="document.getElementById('payment-modal-<?= $lease['lease_id'] ?>').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm mt-2">View / Update Payments</button>

          <!-- Payment Modal -->
          <div id="payment-modal-<?= $lease['lease_id'] ?>" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl w-full max-w-3xl relative">
              <h3 class="text-lg font-semibold mb-4">Payments for <?= htmlspecialchars($lease['unit_name']) ?> (<?= htmlspecialchars($lease['tenant_name']) ?>)</h3>
              <button onclick="document.getElementById('payment-modal-<?= $lease['lease_id'] ?>').classList.add('hidden')" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">&times;</button>

              <table class="w-full text-left border border-gray-200 rounded-lg text-sm">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="py-1 px-2">Date</th>
                    <th class="py-1 px-2">Amount Paid</th>
                    <th class="py-1 px-2">Balance</th>
                    <th class="py-1 px-2">Status</th>
                    <th class="py-1 px-2">Receipt</th>
                    <th class="py-1 px-2">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($paymentsByLease[$lease['lease_id']] as $payment): ?>
                    <tr class="border-t border-gray-200">
                      <td class="py-1 px-2"><?= htmlspecialchars($payment['payment_date']) ?></td>
                      <td class="py-1 px-2">₱<?= number_format($payment['amount'],2) ?></td>
                      <td class="py-1 px-2">₱<?= number_format($payment['balance_after_payment'],2) ?></td>
                      <td class="py-1 px-2"><?= htmlspecialchars($payment['status']) ?></td>
                      <td class="py-1 px-2">
                        <?php if ($payment['receipt_upload']): ?>
                          <a href="../uploads/<?= htmlspecialchars($payment['receipt_upload']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                        <?php else: ?>N/A<?php endif; ?>
                      </td>
                      <td class="py-1 px-2">
                        <form method="POST" action="../updatePaymentStatus.php" class="flex space-x-1">
                          <input type="hidden" name="payment_id" value="<?= $payment['payment_id'] ?>">
                          <select name="status" class="border border-gray-300 rounded px-1 text-sm">
                            <option value="Confirmed" <?= $payment['status']=='Confirmed'?'selected':'' ?>>Confirmed</option>
                            <option value="Ongoing" <?= $payment['status']=='Ongoing'?'selected':'' ?>>Ongoing</option>
                            <option value="Late" <?= $payment['status']=='Late'?'selected':'' ?>>Late</option>
                          </select>
                          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-2 rounded text-sm">Update</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center py-12">
        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <p class="text-slate-500 font-medium text-lg mb-2">No lease agreements found</p>
        <p class="text-slate-400 text-sm">Add a new lease to get started.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

  <!-- Maintenance Requests -->
  <section class="bg-white rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-xl">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
        <div class="p-2 bg-orange-100 rounded-xl">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z" />
          </svg>
        </div>
        Maintenance Requests
      </h3>

      <a href="../manageMaintenance.php"
         class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
        Manage
      </a>
    </div>

    <div class="space-y-4">
      <?php if (!empty($maintenanceRequests)): ?>
        <?php foreach ($maintenanceRequests as $req): ?>
          <div class="p-4 border border-orange-200 rounded-2xl hover:shadow-md transition-all duration-200">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-slate-800 text-base mb-1">
                  🧰 <?= htmlspecialchars($req['issue_title']) ?>
                </h4>
                <p class="text-sm text-slate-600 mb-1">
                  <?= htmlspecialchars($req['description']) ?>
                </p>
                <p class="text-xs text-slate-500">
                  Tenant: <span class="font-medium"><?= htmlspecialchars($req['tenant_name']) ?></span> • 
                  Date: <?= htmlspecialchars($req['request_date']) ?>
                </p>
              </div>
              <span class="px-3 py-1 text-xs rounded-full font-medium
                <?= $req['status'] === 'Pending' 
                  ? 'bg-amber-100 text-amber-700' 
                  : ($req['status'] === 'Completed' 
                    ? 'bg-emerald-100 text-emerald-700' 
                    : 'bg-slate-100 text-slate-600') ?>">
                <?= htmlspecialchars($req['status']) ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-slate-500 font-medium text-lg mb-2">No maintenance requests yet</p>
          <p class="text-slate-400 text-sm">Tenant reports will appear here.</p>
        </div>
      <?php endif; ?>
    </div>
    </section>
</div>
      
</section>

<!-- Messages Section -->
<section class="bg-white rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-xl">
  <div class="flex items-center justify-between mb-6">
    <h3 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
      <div class="p-2 bg-indigo-100 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-6.219-8.56M21 12l-4.35 4.35" />
        </svg>
      </div>
      Messages
    </h3>

    <a href="../sendMessage.php"
       class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
      Manage
    </a>
  </div>

  <div class="space-y-4">
  <?php if (!empty($recentMessages)): ?>
    <?php foreach ($recentMessages as $msg): ?>
      <?php
        // Prevent undefined index warnings
        $senderName = $msg['sender_name'] ?? 'Landlord';
        $message = $msg['message'] ?? '(No message)';
        $dateSent = $msg['date_sent'] ?? 'N/A';
      ?>
      <div class="p-4 border border-indigo-200 rounded-2xl hover:shadow-md transition-all duration-200">
        <div class="flex justify-between items-start">
          <div>
            <h4 class="font-semibold text-slate-800 text-base mb-1">
              💬 <?= htmlspecialchars($senderName) ?>
            </h4>
            <p class="text-sm text-slate-600 mb-1">
              <?= htmlspecialchars($message) ?>
            </p>
            <p class="text-xs text-slate-500">
              Date: <?= htmlspecialchars($dateSent) ?>
            </p>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-center py-12">
      <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-6.219-8.56M21 12l-4.35 4.35" />
        </svg>
      </div>
      <p class="text-slate-500 font-medium text-lg mb-2">No messages yet</p>
      <p class="text-slate-400 text-sm">Messages between you and tenants will appear here.</p>
    </div>
  <?php endif; ?>
</div>

</main>


<!-- FOOTER -->
<?php include '../../assets/footer.php'; ?>

<script>
<?php if ($landlordSuccess): ?>
Swal.fire({ icon: 'success', title: 'Success!', text: <?= json_encode($landlordSuccess) ?>, timer: 3000, showConfirmButton: false });
<?php elseif ($landlordError): ?>
Swal.fire({ icon: 'error', title: 'Operation Failed', text: <?= json_encode($landlordError) ?>, confirmButtonText: 'Okay' });
<?php endif; ?>
</script>

</body>
</html>
