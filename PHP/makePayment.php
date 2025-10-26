<?php
session_start();

// Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant" || !isset($_SESSION["user_id"])) {
    header("Location: ../../login_page.php");
    exit;
}

require_once "dbConnect.php";
require_once "leaseManager.php";
require_once "paymentManager.php";

// DB connection
$database = new Database();
$db = $database->getConnection();

// Managers
$userId = (int) $_SESSION["user_id"];
$leaseManager = new LeaseManager($db);
$paymentManager = new PaymentManager($db);

// Get lease_id from URL
$leaseId = isset($_GET['lease_id']) ? (int)$_GET['lease_id'] : 0;
$lease = $leaseManager->getLeaseByIdForTenant($leaseId, $userId);

if (!$lease) {
    $_SESSION['tenant_error'] = "Invalid or inactive lease selected.";
    header("Location: dashboard/tenant_dashboard.php");
    exit;
}

// Inside the POST handler
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = floatval($_POST['amount'] ?? 0);
        $receipt = $_FILES['receipt'] ?? null;

        // Fetch current lease balance again
        $leaseBalance = (float)$lease['balance'];

        if ($amount <= 0) {
            $_SESSION['tenant_error'] = "Please enter a valid amount.";
        } elseif ($amount > $leaseBalance) {
            $_SESSION['tenant_error'] = "Payment exceeds the outstanding balance of ₱" . number_format($leaseBalance, 2);
        } elseif (!$receipt || $receipt['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['tenant_error'] = "Please upload a valid receipt.";
        } else {
        }

        // Upload receipt
        $uploadDir = "../../uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = time() . "_" . basename($receipt['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($receipt['tmp_name'], $targetFile)) {
            // Add payment
            $success = $paymentManager->addPayment($leaseId, $userId, $amount, $filename);

            if ($success) {
                $_SESSION['tenant_success'] = "Payment submitted successfully. Awaiting confirmation.";
                header("Location: dashboard/tenant_dashboard.php");
                exit;
            } else {
                $_SESSION['tenant_error'] = $_SESSION['tenant_error'] ?? "Failed to save payment. Try again.";
            }
        } else {
            $_SESSION['tenant_error'] = "Failed to upload receipt. Try again.";
        }
    }


$tenantSuccess = $_SESSION['tenant_success'] ?? null;
$tenantError   = $_SESSION['tenant_error'] ?? null;
unset($_SESSION['tenant_success'], $_SESSION['tenant_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Unitly - Make Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

<header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Unitly Tenant - Make Payment</h1>
        <a href="dashboard/tenant_dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
    </div>
</header>

<main class="flex-grow max-w-3xl mx-auto px-6 py-8 w-full">
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-xl font-semibold text-slate-800 mb-4">Payment for <?= htmlspecialchars($lease['unit_name']) ?></h2>
        <p class="mb-4 text-gray-700"><strong>Lease Period:</strong> <?= htmlspecialchars($lease['lease_start_date']) ?> to <?= htmlspecialchars($lease['lease_end_date']) ?></p>
        <p class="mb-6 text-gray-700"><strong>Outstanding Balance:</strong> ₱<?= number_format($lease['balance'], 2) ?></p>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-slate-700 mb-1">Payment Amount</label>
                <input type="number" step="0.01" min="0" name="amount" id="amount" required
                       class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Enter payment amount">
            </div>

            <div>
                <label for="receipt" class="block text-sm font-medium text-slate-700 mb-1">Upload Receipt</label>
                <input type="file" name="receipt" id="receipt" accept=".jpg,.jpeg,.png,.pdf" required
                       class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg transition-colors">Submit Payment</button>
        </form>
    </section>
</main>

<footer class="bg-blue-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-6 py-16 text-sm text-blue-100">
        &copy; <?= date('Y') ?> Unitly. All rights reserved.
    </div>
</footer>

<script>
<?php if ($tenantSuccess): ?>
Swal.fire({ icon: 'success', title: 'Success!', text: <?= json_encode($tenantSuccess) ?>, timer: 3000, showConfirmButton: false });
<?php elseif ($tenantError): ?>
Swal.fire({ icon: 'error', title: 'Error', text: <?= json_encode($tenantError) ?>, confirmButtonText: 'Okay' });
<?php endif; ?>
</script>

</body>
</html>
