<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "AccountManager.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: manageTenants.php");
    exit;
}

$fullName = trim($_POST["full_name"] ?? '');
$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');

if (empty($fullName) || empty($phone) || empty($password)) {
    $_SESSION["error"] = "All fields are required.";
    header("Location: manageTenants.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$manager = new AccountManager($db);
$landlordId = (int)($_SESSION["user_id"] ?? 0);

if ($manager->createTenant($landlordId, $fullName, $phone, $password)) {
    $_SESSION["success"] = "Tenant added successfully!";
} else {
    $_SESSION["error"] = "Failed to add tenant.";
}

header("Location: manageTenants.php");
exit;
?>
