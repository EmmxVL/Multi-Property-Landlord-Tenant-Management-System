<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "TenantManager.php"; // ✅ Use a TenantManager for tenant-related functions

// ✅ Ensure only landlords (or authorized admins) can create tenants
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

// ✅ Make sure the request came from a POST form
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

// ✅ Sanitize and validate input fields
$fullName = trim($_POST["full_name"] ?? '');
$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');
$unitId = isset($_POST["unit_id"]) ? (int)$_POST["unit_id"] : 0;

// ✅ Check if fields are valid
if (empty($fullName) || empty($phone) || empty($password)) {
    $_SESSION["error"] = "All fields are required.";
    header("Location: manageTenants.php");
    exit;
}

// ✅ Connect to database
$database = new Database();
$db = $database->getConnection();
if ($tenantCreated) {
    $_SESSION["success"] = "Tenant created successfully!";
    header("Location: manageTenants.php");
    exit;
} else {
    $_SESSION["error"] = "Failed to create tenant. Please try again.";
    header("Location: manageTenants.php");
    exit;
}
?>
