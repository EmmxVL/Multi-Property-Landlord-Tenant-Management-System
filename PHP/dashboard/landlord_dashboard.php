<?php
session_start();

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../../login_page.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_page.php");
    exit;
}

require_once "../dbConnect.php";
require_once "../propertyManager.php";
require_once "../tenantManager.php";
require_once "../leaseManager.php";
require_once "../paymentManager.php"; // new: for payment data

// ✅ Create DB connection (PDO)
$database = new Database();
$db = $database->getConnection();

// ✅ Initialize managers
$userId = (int) $_SESSION["user_id"];
$propertyManager = new PropertyManager($db, $userId);
$tenantManager = new TenantManager($db, $userId);
$leaseManager = new LeaseManager($db);
$paymentManager = new PaymentManager($db);

// ✅ Fetch landlord's properties, tenants & leases
$properties = $propertyManager->getProperties();
$tenants = $tenantManager->getTenants();
$leases = $leaseManager->getLeasesByLandlord($userId);


// Fetch payments for each lease
$paymentsByLease = [];
foreach ($leases as $lease) {
    $paymentsByLease[$lease['lease_id']] = $paymentManager->getPaymentsByLease($lease['lease_id']);
}

// ✅ Landlord session messages
$landlordSuccess = $_SESSION['landlord_success'] ?? null;
$landlordError = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);
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

<header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">U</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Unitly Landlord</h1>
                <p class="text-xs text-slate-500">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?>!</p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-slate-700 text-sm hidden sm:inline"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?></span>
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                <?php 
                    $fullName = $_SESSION['full_name'] ?? 'LU'; 
                    $names = explode(' ', $fullName);
                    $initials = ($names[0][0] ?? '') . ($names[1][0] ?? ''); 
                    echo htmlspecialchars(strtoupper($initials) ?: 'U');
                ?>
            </div>
            <a href="../logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</header>

<main class="flex-grow max-w-7xl mx-auto px-6 py-10 w-full bg-slate-50 min-h-screen">
  <!-- Header -->
  <div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-semibold text-slate-800 tracking-tight">Landlord Dashboard Overview</h2>
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
                  <?= htmlspecialchars($property['location']) ?>
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
    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md transition-all duration-300 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
            <circle cx="12" cy="7" r="4" stroke-width="2" />
          </svg>
          My Tenants
        </h3>
        <a href="../manageTenants.php"
           class="text-emerald-600 text-sm font-medium hover:underline">Manage</a>
      </div>

      <div class="space-y-3">
        <?php if (!empty($tenants)): ?>
          <?php foreach ($tenants as $tenant): ?>
            <div class="p-4 bg-gradient-to-r from-emerald-50 to-emerald-100 border border-slate-200 rounded-lg hover:shadow transition-all">
              <span class="font-semibold text-slate-800 block text-base">
                <?= htmlspecialchars($tenant['full_name']) ?>
              </span>
              <span class="text-slate-600 text-sm flex items-center gap-1">
                📞 <?= htmlspecialchars($tenant['phone_no']) ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-slate-500 italic text-center py-4">No tenants found.</p>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <!-- Lease Agreements -->
  <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mt-10">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Lease Agreements
      </h3>
      <div class="flex space-x-2">
        <a href="../addLease.php" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition">+ Add Lease</a>
        <a href="../manageLease.php" class="bg-slate-100 hover:bg-slate-200 text-slate-800 py-2 px-4 rounded-lg text-sm font-medium transition">Manage</a>
      </div>
    </div>

    <?php if (!empty($leases)): ?>
      <?php foreach ($leases as $lease): ?>
        <div class="mb-6 border border-gray-200 rounded-xl p-5 hover:shadow transition">
          <div class="flex justify-between items-center mb-3">
            <div>
              <h4 class="font-semibold text-slate-800">
                <?= htmlspecialchars($lease['unit_name']) ?> 
                <span class="text-slate-500 text-sm"> (Tenant: <?= htmlspecialchars($lease['tenant_name']) ?>)</span>
              </h4>
              <p class="text-sm text-slate-600 mt-1">
                🗓 Start: <?= htmlspecialchars($lease['lease_start_date']) ?> | End: <?= htmlspecialchars($lease['lease_end_date']) ?> <br>
                💰 Balance: <span class="font-semibold text-rose-600">₱<?= number_format($lease['balance'],2) ?></span>
              </p>
            </div>
            <span class="text-sm px-3 py-1 rounded-full 
              <?= $lease['lease_status'] == 'Active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
              <?= htmlspecialchars($lease['lease_status']) ?>
            </span>
          </div>

          <!-- Payment Info -->
          <?php
              $recentPayment = !empty($paymentsByLease[$lease['lease_id']]) ? end($paymentsByLease[$lease['lease_id']]) : null;
          ?>
 <?php if ($recentPayment): ?>
                        <p class="text-sm mb-2">
                            <strong>Recent Payment:</strong> ₱<?= number_format($recentPayment['amount'],2) ?> on <?= htmlspecialchars($recentPayment['payment_date']) ?> | Status: <?= htmlspecialchars($recentPayment['status']) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-gray-500 italic text-sm">No payments recorded yet.</p>
                    <?php endif; ?>

                    <!-- View/Update Payments Button -->
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
                                                <a href="../../uploads/<?= htmlspecialchars($payment['receipt_upload']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
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
            <p class="text-gray-500 italic">No leases found.</p>
        <?php endif; ?>
    </section>
</main>


<footer class="bg-blue-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <h3 class="text-2xl font-bold mb-6 text-blue-100">CompanyName</h3>
                <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Vision</h4>
                <p class="text-blue-100 leading-relaxed text-sm">To revolutionize property management by fostering seamless connections between landlords and tenants.</p>
            </div>
            <div>
                <h4 class="text-xl font-semibold mb-6 text-blue-200">Contact Us</h4>
                <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
                <p class="text-blue-100 text-sm">+63 (0906) 581-6503</p>
                <p class="text-blue-100 text-sm">Unitlyph@gmail.com</p>
                <p class="text-blue-100 text-sm">www.unitly.com</p>
            </div>
        </div>
    </div>
</footer>

<script>
<?php if ($landlordSuccess): ?>
Swal.fire({ icon: 'success', title: 'Success!', text: <?= json_encode($landlordSuccess) ?>, timer: 3000, showConfirmButton: false });
<?php elseif ($landlordError): ?>
Swal.fire({ icon: 'error', title: 'Operation Failed', text: <?= json_encode($landlordError) ?>, confirmButtonText: 'Okay' });
<?php endif; ?>
</script>

</body>
</html>
