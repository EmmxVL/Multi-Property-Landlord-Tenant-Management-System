<?php
session_start();

// Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant" || !isset($_SESSION["user_id"])) {
    header("Location: ../../login_page_user.php");
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

// Get tenant info
$stmt = $db->prepare("SELECT full_name FROM user_tbl WHERE user_id = :user_id");
$stmt->execute([':user_id' => $userId]);
$tenantName = $stmt->fetchColumn();


$tenantSuccess = $_SESSION['tenant_success'] ?? null;
$tenantError  = $_SESSION['tenant_error'] ?? null;
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
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        /* Custom styles for file input */
        #uploadArea {
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
        }
        #uploadArea.drag-over {
            background-color: #f0f9ff;
            border-color: #2563eb;
        }
        /* Hide the default file input */
        #receipt {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }
    </style>
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

      <!-- *** THIS FORM NOW POINTS TO handle_payment.php *** -->
      <form method="POST" action="handlePayment.php" enctype="multipart/form-data" id="paymentForm" class="space-y-6">
        <input type="hidden" name="lease_id" value="<?= $leaseId ?>">
        <input type="hidden" name="tenant_id" value="<?= $userId ?>">
        <input type="hidden" name="unit_id" value="<?= $lease['unit_id'] ?>">
        
        <!-- Amount -->
        <div>
          <label for="amount" class="block text-sm font-semibold text-slate-700 mb-2">Payment Amount</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-500 text-lg">₱</span>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" required
                   class="w-full pl-8 pr-4 py-4 text-lg border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                   placeholder="0.00"
                   max="<?= (float)$lease['balance'] > 0 ? (float)$lease['balance'] : '' ?>">
          </div>
          <div class="mt-2 flex justify-between text-xs text-slate-500">
            <p>Enter the amount you want to pay.</p>
            <p>Max: <span class="font-medium">₱<?= number_format($lease['balance'], 2) ?></span></p>
          </div>
        </div>

        <!-- Receipt Upload -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Upload Receipt (Required)</label>
          
          <div id="uploadArea" class="relative border-2 border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-green-400 transition-colors">
            <!-- The actual file input is hidden but covers the area -->
            <input type="file" name="receipt" id="receipt" accept=".jpg,.jpeg,.png,.pdf" required
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
            
            <!-- This is the content shown to the user -->
            <div id="uploadContent" class="">
              <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3-3 3m3-3v12"/>
              </svg>
              <p class="text-slate-600 font-medium mb-2">Click to upload or drag and drop</p>
              <p class="text-slate-400 text-sm">PNG, JPG, PDF up to 10MB</p>
            </div>
            
            <!-- This is shown after a file is selected -->
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
             <!-- Handle open-ended leases -->
            <div class="font-semibold text-sm"><?= date('M d, Y', strtotime($lease['lease_start_date'])) ?></div>
            <div class="text-xs text-slate-500">to <?= $lease['lease_end_date'] ? date('M d, Y', strtotime($lease['lease_end_date'])) : 'Present' ?></div>
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
          <span id="paymentStatus" class="font-bold text-green-600">--</span>
        </div>
      </div>
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

// --- Drag and Drop File Upload ---
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('receipt');
const uploadContent = document.getElementById('uploadContent');
const filePreview = document.getElementById('filePreview');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');
const removeFile = document.getElementById('removeFile');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('drag-over');
});
uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('drag-over');
});
uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        updateFilePreview();
    }
});
fileInput.addEventListener('change', updateFilePreview);

removeFile.addEventListener('click', () => {
    fileInput.value = ''; // Clear the file input
    uploadContent.classList.remove('hidden');
    filePreview.classList.add('hidden');
});

function updateFilePreview() {
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        uploadContent.classList.add('hidden');
        filePreview.classList.remove('hidden');
    } else {
        uploadContent.classList.remove('hidden');
        filePreview.classList.add('hidden');
    }
}

// --- Payment Summary ---
const amountInput = document.getElementById('amount');
const summaryAmount = document.getElementById('summaryAmount');
const remainingBalance = document.getElementById('remainingBalance');
const paymentStatus = document.getElementById('paymentStatus');
const leaseBalance = <?= (float)$lease['balance'] ?>;

amountInput.addEventListener('input', () => {
    let amount = parseFloat(amountInput.value) || 0;
    if (amount > leaseBalance) {
        amount = leaseBalance;
        amountInput.value = leaseBalance.toFixed(2);
    }
    
    const newBalance = leaseBalance - amount;
    
    summaryAmount.textContent = '₱' + amount.toFixed(2);
    remainingBalance.textContent = '₱' + newBalance.toFixed(2);
    
    if (newBalance <= 0 && amount > 0) {
        paymentStatus.textContent = 'Paid in Full';
        paymentStatus.classList.remove('text-yellow-600');
        paymentStatus.classList.add('text-green-600');
    } else if (amount > 0) {
        paymentStatus.textContent = 'Partial Payment';
        paymentStatus.classList.remove('text-green-600');
        paymentStatus.classList.add('text-yellow-600');
    } else {
        paymentStatus.textContent = '--';
        paymentStatus.classList.remove('text-yellow-600');
        paymentStatus.classList.add('text-green-600');
    }
});

</script>

</body>
</html>