<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "AccountManager.php"; // <-- Use AccountManager now

// ✅ Only landlords can create tenants
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

// ✅ Ensure POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

// ✅ Sanitize and validate input
$fullName = trim($_POST["full_name"] ?? '');
$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');
$landlordId = $_SESSION["user_id"] ?? 0; // assume landlord ID stored in session

if (empty($fullName) || empty($phone) || empty($password)) {
    $_SESSION["error"] = "All fields are required.";
    header("Location: manageTenants.php");
    exit;
}

// ✅ Initialize DB connection
$database = new Database();
$db = $database->getConnection();

// ✅ Use AccountManager to create tenant
$accountManager = new AccountManager($db);
try {
    $accountManager->createTenant($fullName, $phone, $password); // Redirect handled inside
} catch (Exception $e) {
    $_SESSION["error"] = "Failed to create tenant: " . $e->getMessage();
    header("Location: manageTenants.php");
    exit;
}
