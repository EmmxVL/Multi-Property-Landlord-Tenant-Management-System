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
        $uploadDir = "uploads/";
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

<!-- Header -->
<?php include '../assets/header.php'; ?>

<main class="flex-grow max-w-4xl mx-auto px-6 py-8 w-full">
  <!-- Header -->
  <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl p-8 mb-8 text-white flex items-center space-x-4">
    <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 9V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2m2 4h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm7-5a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
      </svg>
    </div>
    <div>
      <h1 class="text-2xl font-bold">Make Payment</h1>
      <p class="text-green-100">Submit your rental payment securely</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Payment Form -->
    <section class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-8">
      <div class="flex items-center space-x-3 mb-6">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-slate-800">
          Payment for <?= htmlspecialchars($lease['unit_name']) ?>
        </h2>
      </div>

      <form method="POST" enctype="multipart/form-data" id="paymentForm" class="space-y-6">
        <!-- Amount -->
        <div>
          <label for="amount" class="block text-sm font-semibold text-slate-700 mb-2">Payment Amount</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 text-lg">₱</span>
            <input type="number" step="0.01" min="0" name="amount" id="amount" required
                   class="w-full pl-8 pr-4 py-4 text-lg border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                   placeholder="0.00">
          </div>
          <div class="mt-2 flex justify-between text-xs text-slate-500">
            <p>Enter the amount you want to pay</p>
          </div>
        </div>

        <!-- Payment Method -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-3">Payment Method</label>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <?php
            $methods = [
              ['bank_transfer', 'Bank Transfer', 'blue', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
              ['gcash', 'GCash', 'green', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
              ['cash', 'Cash', 'yellow', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z']
            ];
            foreach ($methods as $i => $m): ?>
              <label class="relative flex items-center p-4 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                <input type="radio" name="payment_method" value="<?= $m[0] ?>" class="sr-only" <?= $i === 0 ? 'checked' : '' ?>>
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-<?= $m[2] ?>-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-<?= $m[2] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $m[3] ?>"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-slate-700"><?= $m[1] ?></span>
                </div>
                <div class="absolute inset-0 border-2 border-green-500 rounded-lg opacity-0 payment-method-selected"></div>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Receipt Upload -->
        <div>
          <label for="receipt" class="block text-sm font-semibold text-slate-700 mb-2">Upload Receipt</label>
          
          <div id="uploadArea" class="border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-green-400 transition-colors">
            <input type="file" name="receipt" id="receipt" accept=".jpg,.jpeg,.png,.pdf" required class="border-slate-300 ">
            <div id="uploadContent">
              <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3-3 3m3-3v12"/>
              </svg>
              
              <p class="text-slate-600 font-medium mb-2">Click to upload or drag and drop</p>
              <p class="text-slate-400 text-sm">PNG, JPG, PDF up to 10MB</p>
            </div>
            <div id="filePreview" class="hidden flex items-center justify-center space-x-3">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p class="font-medium text-slate-800" id="fileName"></p>
                <p class="text-sm text-slate-500" id="fileSize"></p>
              </div>
              <button type="button" id="removeFile" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" id="submitBtn"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-lg flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          <span>Submit Payment Securely</span>
        </button>
      </form>
    </section>

    <!-- Sidebar -->
    <div class="space-y-6">
      <!-- Lease Info -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-3">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Lease Information</h3>
        <div class="flex justify-between border-b border-slate-100 py-2">
          <span class="text-slate-600 text-sm">Unit</span>
          <span class="font-semibold"><?= htmlspecialchars($lease['unit_name']) ?></span>
        </div>
        <div class="flex justify-between border-b border-slate-100 py-2">
          <span class="text-slate-600 text-sm">Lease Period</span>
          <div class="text-right">
            <div class="font-semibold text-sm"><?= date('M d, Y', strtotime($lease['lease_start_date'])) ?></div>
            <div class="text-xs text-slate-500">to <?= date('M d, Y', strtotime($lease['lease_end_date'])) ?></div>
          </div>
        </div>
        <div class="flex justify-between py-2">
          <span class="text-slate-600 text-sm">Outstanding Balance</span>
          <span class="font-bold text-lg text-red-600">₱<?= number_format($lease['balance'], 2) ?></span>
        </div>
      </div>

      <!-- Payment Summary -->
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200 p-6 space-y-3">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Payment Summary</h3>
        <div class="flex justify-between"><span>Payment Amount</span><span id="summaryAmount" class="font-bold text-green-600">₱0.00</span></div>
        <div class="flex justify-between"><span>Remaining Balance</span><span id="remainingBalance" class="font-semibold">₱<?= number_format($lease['balance'], 2) ?></span></div>
        <div class="pt-3 border-t border-green-200 flex justify-between">
          <span class="font-medium text-slate-600">Status After Payment</span>
          <span id="paymentStatus" class="font-bold text-green-600">Partial Payment</span>
        </div>
      </div>
            </main>

<!-- Footer -->
<?php include '../assets/footer.php'; ?>

<script>
<?php if ($tenantSuccess): ?>
Swal.fire({ icon: 'success', title: 'Success!', text: <?= json_encode($tenantSuccess) ?>, timer: 3000, showConfirmButton: false });
<?php elseif ($tenantError): ?>
Swal.fire({ icon: 'error', title: 'Error', text: <?= json_encode($tenantError) ?>, confirmButtonText: 'Okay' });
<?php endif; ?>
</script>

</body>
</html>
