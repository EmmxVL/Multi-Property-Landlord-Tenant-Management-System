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
            <h1 class="text-2xl font-bold text-slate-800">Unitly Tenant Dashboard</h1>
            <a href="../logout.php" class="text-red-600 hover:underline">Logout</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">
        <h2 class="text-2xl font-semibold text-slate-800 mb-6">Overview</h2>

        <!-- Next Payment Due -->
        <?php if ($nextPayment): ?>
            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-2">Next Payment Due</h3>
                <div class="p-4 bg-yellow-50 rounded-lg flex justify-between items-center">
                    <div>
                        <p><strong>Unit:</strong> <?= htmlspecialchars($nextPayment['unit_name']) ?></p>
                        <p><strong>Amount Due:</strong> ₱<?= number_format($nextPayment['balance'], 2) ?></p>
                        <p><strong>Due Date:</strong> <?= htmlspecialchars($nextPayment['lease_end_date']) ?></p>
                    </div>
                    <?php if ($nextPayment['balance'] > 0): ?>
                        <a href="../makePayment.php?lease_id=<?= $nextPayment['lease_id'] ?>" class="bg-yellow-400 hover:bg-yellow-500 text-white font-semibold py-2 px-4 rounded-lg transition">
                            Pay Now
                        </a>
                    <?php else: ?>
                        <span class="text-green-600 font-semibold">Paid in full</span>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Active Leases -->
            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">My Active Leases</h3>
                <?php if ($leases): ?>
                    <table class="w-full text-left border border-gray-200 rounded-lg text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>Unit</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leases as $lease): ?>
                                <tr class="border-t border-gray-200">
                                    <td><?= htmlspecialchars($lease['unit_name']) ?></td>
                                    <td><?= htmlspecialchars($lease['lease_start_date']) ?></td>
                                    <td><?= htmlspecialchars($lease['lease_end_date']) ?></td>
                                    <td>₱<?= number_format((float)$lease['balance'], 2) ?></td>
                                    <td><?= htmlspecialchars($lease['lease_status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-500 italic">No active leases found.</p>
                <?php endif; ?>
            </section>

            <!-- Payments -->
            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">My Payments</h3>
                <?php if ($payments): ?>
                    <table class="w-full text-left border border-gray-200 rounded-lg text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>Unit</th>
                                <th>Date</th>
                                <th>Amount Paid</th>
                                <th>Balance After Payment</th>
                                <th>Status</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr class="border-t border-gray-200">
                                    <td><?= htmlspecialchars($payment['unit_name']) ?></td>
                                    <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                                    <td>₱<?= number_format((float)$payment['amount'], 2) ?></td>
                                    <td>₱<?= number_format((float)$payment['balance_after_payment'], 2) ?></td>
                                    <td><?= htmlspecialchars($payment['status']) ?></td>
                                    <td>
                                        <?php if ($payment['receipt_upload']): ?>
                                            <a href="../../uploads/<?= htmlspecialchars($payment['receipt_upload']) ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                                        <?php else: ?>N/A<?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-500 italic">No payments found.</p>
                <?php endif; ?>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white mt-12">
        <div class="max-w-7xl mx-auto px-6 py-16 text-sm text-blue-100">
            &copy; <?= date('Y') ?> Unitly. All rights reserved.
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
