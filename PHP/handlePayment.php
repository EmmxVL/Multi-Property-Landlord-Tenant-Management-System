<?php
session_start();
require_once "dbConnect.php";
require_once "paymentManager.php";
require_once "leaseManager.php";

// 1. Check Tenant Auth
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant" || !isset($_SESSION["user_id"])) {
    $_SESSION['tenant_error'] = "Unauthorized action.";
    header("Location: dashboard/tenant_dashboard.php");
    exit;
}

// 2. Check for POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard/tenant_dashboard.php");
    exit;
}

// DB connection
$database = new Database();
$db = $database->getConnection();

// Managers
$userId = (int) $_SESSION["user_id"];
$leaseManager = new LeaseManager($db);
$paymentManager = new PaymentManager($db);

// 3. Get and Validate Data
$leaseId = (int)($_POST['lease_id'] ?? 0);
$tenantId = (int)($_POST['tenant_id'] ?? 0);
$unitId = (int)($_POST['unit_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$receipt = $_FILES['receipt'] ?? null;

$redirectUrl = "makePayment.php?lease_id=" . $leaseId; // Redirect back to payment form on error

// Security check: Does this tenant own this lease?
if ($userId !== $tenantId) {
    $_SESSION['tenant_error'] = "Authorization failed.";
    header("Location: dashboard/tenant_dashboard.php");
    exit;
}

// Get lease details to verify amount
$lease = $leaseManager->getLeaseByIdForTenant($leaseId, $userId);
if (!$lease) {
    $_SESSION['tenant_error'] = "Invalid or inactive lease selected.";
    header("Location: dashboard/tenant_dashboard.php");
    exit;
}

$leaseBalance = (float)$lease['balance'];

// 4. Validate Input
if ($amount <= 0) {
    $_SESSION['tenant_error'] = "Please enter a valid amount.";
    header("Location: " . $redirectUrl);
    exit;
}
if ($amount > $leaseBalance) {
    $_SESSION['tenant_error'] = "Payment exceeds the outstanding balance of ₱" . number_format($leaseBalance, 2);
    header("Location: " . $redirectUrl);
    exit;
}
if (!$receipt || $receipt['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['tenant_error'] = "Please upload a valid receipt image or PDF.";
    header("Location: " . $redirectUrl);
    exit;
}

// 5. Handle File Upload
$uploadDir = "../uploads/receipts/"; // Changed from root "uploads/" to be more organized
$dbPath = "uploads/receipts/";

// Check/create directory
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['tenant_error'] = "Failed to create upload directory. Please contact support.";
        header("Location: " . $redirectUrl);
        exit;
    }
}

// Check file type (MIME type is more secure)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($receipt['tmp_name']);
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

if (!in_array($mime, $allowedMimes)) {
    $_SESSION['tenant_error'] = "Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.";
    header("Location: " . $redirectUrl);
    exit;
}

// Check file size (e.g., 10MB limit)
if ($receipt['size'] > 10000000) {
    $_SESSION['tenant_error'] = "File is too large (Max 10MB).";
    header("Location: " . $redirectUrl);
    exit;
}

// Generate a unique filename
$extension = pathinfo($receipt['name'], PATHINFO_EXTENSION);
$filename = "receipt_lease-" . $leaseId . "_" . uniqid() . "." . $extension;
$targetFile = $uploadDir . $filename;
$dbFile = $dbPath . $filename; // This is what we store in the DB

// Move the file
if (move_uploaded_file($receipt['tmp_name'], $targetFile)) {
    
    // 6. Add payment to database
    // We use the `addPayment` function from your original PaymentManager
    $success = $paymentManager->addPayment(
        $leaseId,
        $userId,
        $amount,
        $dbFile, // Pass the correct database path
        'Ongoing' // Set status to 'Ongoing' for landlord to confirm
    );

    if ($success) {
        $_SESSION['tenant_success'] = "Payment submitted successfully. Please wait for your landlord to confirm.";
        header("Location: dashboard/tenant_dashboard.php");
        exit;
    } else {
        // Payment failed, delete the uploaded file
        @unlink($targetFile);
        $_SESSION['tenant_error'] = $_SESSION['tenant_error'] ?? "Failed to save payment. Please try again.";
        header("Location: " . $redirectUrl);
        exit;
    }
} else {
    $_SESSION['tenant_error'] = "Failed to upload receipt. Please try again.";
    header("Location: " . $redirectUrl);
    exit;
}
?>