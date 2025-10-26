<?php
session_start();

// Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant" || !isset($_SESSION["user_id"])) {
    header("Location: ../../login_page.php");
    exit;
}

require_once "../dbConnect.php";
require_once "../leaseManager.php";
require_once "../paymentManager.php";

// Create DB connection
$database = new Database();
$db = $database->getConnection();

// Initialize managers
$userId = (int) $_SESSION["user_id"];
$leaseManager = new LeaseManager($db);
$paymentManager = new PaymentManager($db);

// Fetch tenant's leases & payments
$leases = $leaseManager->getLeasesByTenant($userId);
$totalLeases = $leases ? count($leases) : 0;
$payments = $paymentManager->getPaymentsByTenant($userId);
$nextPayment = $paymentManager->getNextDuePayment($userId);

// Session messages
$tenantSuccess = $_SESSION['tenant_success'] ?? null;
$tenantError   = $_SESSION['tenant_error'] ?? null;
unset($_SESSION['tenant_success'], $_SESSION['tenant_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unitly - Tenant Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    <!-- Header -->
<header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly Tenant</h1>
                    <p class="text-xs text-slate-500">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Tenant'); ?>!</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
    
                <div class="flex items-center space-x-2">
                     <span class="text-slate-700 text-sm hidden sm:inline"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?></span>
                     <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                         <?php // Initials
                             $fullName = $_SESSION['full_name'] ?? 'LU'; $names = explode(' ', $fullName);
                             $initials = ($names[0][0] ?? '') . ($names[1][0] ?? ''); echo htmlspecialchars(strtoupper($initials) ?: 'U');
                         ?>
                     </div>
                     <a href="../logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                     </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">
        <h2 class="text-2xl font-semibold text-slate-800 mb-6">Overview</h2>
    <!-- Quick Stats -->
     <!-- Current Lease -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in">

    <!-- Current Apartment -->
<div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
    <?php if (!empty($leases)): ?>
        <?php 
            // Get the first active lease
            $currentLease = $leases[0]; 
        ?>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Apartment Name</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">
                    <?= htmlspecialchars($currentLease['unit_name']) ?>
                </p>
                <p class="text-xs text-slate-600 mt-1">
                    <?= htmlspecialchars($currentLease['location']) ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
        </div>
    <?php else: ?>
        <p class="text-slate-500 text-sm italic text-center">No active apartment found</p>
    <?php endif; ?>
</div>


    <!-- Next Payment -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Due Payment</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">
                    ₱<?= isset($nextPayment['balance']) ? number_format($nextPayment['balance'], 2) : '0.00' ?>
                </p>
                <p class="text-xs text-green-600 mt-1">
                    Due Date: <?= isset($nextPayment['lease_end_date']) ? htmlspecialchars($nextPayment['lease_end_date']) : 'N/A' ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Maintenance Requests -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Maintenance Requests</p>
                <p class="text-3xl font-bold text-slate-800 mt-1"><?= $maintenanceCount ?? 0 ?></p>
                <p class="text-xs text-orange-600 mt-1"><?= $pendingRequests ?? 0 ?> pending</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Lease Expiration -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Lease Expires</p>
                <p class="text-xl font-bold text-slate-800 mt-1">
                    <?= isset($leaseExpiryMonths) ? htmlspecialchars($leaseExpiryMonths) . ' months' : 'N/A' ?>
                </p>
                <p class="text-xs text-slate-600 mt-1">
                    <?= isset($leaseEndDate) ? htmlspecialchars($leaseEndDate) : 'No active lease' ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 fade-in">

    <!-- Next Payment Due -->
    <?php if ($nextPayment): ?>
        <div class="bg-white rounded-xl shadow-sm p-8 border border-slate-200 property-card">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Next Payment Due</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">
                        ₱<?= number_format($nextPayment['balance'], 2) ?>
                    </p>
                    <p class="text-xs text-slate-600 mt-1">
                        Unit: <?= htmlspecialchars($nextPayment['unit_name']) ?>
                    </p>
                    <p class="text-xs text-orange-600 mt-1">
                        Due: <?= htmlspecialchars($nextPayment['lease_end_date']) ?>
                    </p>
                </div>

                <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 
                                 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 
                                 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>

            <?php if ($nextPayment['balance'] > 0): ?>
                <a href="../makePayment.php?lease_id=<?= $nextPayment['lease_id'] ?>"
                   class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 
                                 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 
                                 0 00-2-2H9a2 2 0 00-2 2v6a2 2 
                                 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Pay Now</span>
                </a>
            <?php else: ?>
                <div class="w-full bg-green-50 border border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg text-center flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 
                                 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Paid in Full</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <!-- Active Leases -->
    <div class="bg-white rounded-xl shadow-sm p-8 border border-slate-200 property-card">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-slate-600 text-sm font-medium">Active Leases</p>
                <p class="text-3xl font-bold text-slate-800 mt-1"><?= count($leases) ?></p>
                <p class="text-xs text-green-600 mt-1">Properties rented</p>
            </div>

            <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
            </div>
        </div>

        <?php if ($leases): ?>
            <div class="space-y-3">
                <?php foreach (array_slice($leases, 0, 3) as $lease): ?>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">
                                <?= htmlspecialchars($lease['unit_name']) ?>
                            </p>
                            <p class="text-xs text-slate-600">
                                ₱<?= number_format((float)$lease['balance'], 2) ?> balance
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                            <?= $lease['lease_status'] === 'Active' 
                                ? 'bg-green-100 text-green-800' 
                                : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= htmlspecialchars($lease['lease_status']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php if (count($leases) > 3): ?>
                    <div class="text-center pt-2">
                        <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            View all <?= count($leases) ?> leases
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p class="text-slate-500 italic text-sm">No active leases found</p>
            </div>
        <?php endif; ?>
    </div>

</div>

            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800">My Payments</h3>
                </div>

                <!-- Sorting and Filtering -->
                <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-slate-600">Sort by:</label>
                    <select id="sortBy" class="text-sm border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="date-desc">Date (Newest)</option>
                    <option value="date-asc">Date (Oldest)</option>
                    <option value="unit-asc">Unit (A–Z)</option>
                    <option value="unit-desc">Unit (Z–A)</option>
                    <option value="amount-desc">Amount (High–Low)</option>
                    <option value="amount-asc">Amount (Low–High)</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-slate-600">Unit:</label>
                    <select id="filterUnit" class="text-sm border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">All Units</option>
                    <?php if ($payments): 
                        $units = array_unique(array_column($payments, 'unit_name'));
                        sort($units);
                        foreach ($units as $unit): ?>
                        <option value="<?= htmlspecialchars($unit) ?>"><?= htmlspecialchars($unit) ?></option>
                    <?php endforeach; endif; ?>
                    </select>
                </div>
                </div>
            </div>

            <?php if ($payments): ?>
            <!-- Payments Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="paymentsTable">
                <thead>
                    <tr class="border-b-2 border-slate-200 bg-slate-50">
                    <?php 
                        $headers = ['Unit','Date','Amount Paid','Balance After','Status','Receipt'];
                        foreach ($headers as $h) echo "<th class='text-left py-4 px-4 font-semibold text-slate-700'>$h</th>";
                    ?>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <?php foreach ($payments as $payment): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                    <td class="py-4 px-4 font-medium text-slate-800"><?= htmlspecialchars($payment['unit_name']) ?></td>
                    <td class="py-4 px-4 text-slate-600">
                        <div><?= date('M d, Y', strtotime($payment['payment_date'])) ?><br>
                        <span class="text-xs text-slate-500"><?= date('g:i A', strtotime($payment['payment_date'])) ?></span>
                        </div>
                    </td>
                    <td class="py-4 px-4 font-bold text-green-600">₱<?= number_format((float)$payment['amount'], 2) ?></td>
                    <td class="py-4 px-4 font-semibold text-slate-800">₱<?= number_format((float)$payment['balance_after_payment'], 2) ?></td>
                    <td class="py-4 px-4">
                        <?php
                        $status = strtolower($payment['status']);
                        $statusClasses = [
                            'paid'=>'bg-green-100 text-green-800',
                            'completed'=>'bg-green-100 text-green-800',
                            'pending'=>'bg-yellow-100 text-yellow-800',
                            'failed'=>'bg-red-100 text-red-800'
                        ];
                        $badge = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?= $badge ?>">
                        <?= htmlspecialchars(ucfirst($payment['status'])) ?>
                        </span>
                    </td>
                    <td class="py-4 px-4 text-center">
                        <?php if ($payment['receipt_upload']): ?>
                        <a href="../../uploads/<?= htmlspecialchars($payment['receipt_upload']) ?>" target="_blank"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg">
                            👁 View
                        </a>
                        <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-lg">N/A</span>
                        <?php endif; ?>
                    </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-slate-600">Showing <span class="font-medium" id="showingCount"><?= count($payments) ?></span> payments</p>
                <div class="flex items-center space-x-2">
                <button class="px-3 py-1 text-sm text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg">Export CSV</button>
                <button class="px-3 py-1 text-sm text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg">Print</button>
                </div>
            </div>

            <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                </div>
                <h4 class="text-lg font-semibold text-slate-800 mb-2">No payments found</h4>
                <p class="text-slate-500 mb-4">You haven't made any payments yet.</p>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Make a Payment</button>
            </div>
            <?php endif; ?>
            </section>


        </div>
    </main>

    <!-- Footer -->
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

    <!-- SweetAlert Notifications -->
    <script>
        <?php if ($tenantSuccess): ?>
            Swal.fire({ 
                icon: 'success', 
                title: 'Success!', 
                text: <?= json_encode($tenantSuccess) ?>, 
                timer: 3000, 
                showConfirmButton: false 
            });
        <?php elseif ($tenantError): ?>
            Swal.fire({ 
                icon: 'error', 
                title: 'Operation Failed', 
                text: <?= json_encode($tenantError) ?>, 
                confirmButtonText: 'Okay' 
            });
        <?php endif; ?>
    </script>

</body>
</html>