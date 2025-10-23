<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "AccountManager.php";

$database = new Database();
$db = $database->getConnection();
$accountManager = new AccountManager($db);

// --- Check Role ---
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../login_page.php");
    exit;
}

// --- Process form ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $accountManager->createLandlord($fullName, $phone, $password);
} else {
    header("Location: dashboard/admin_dashboard.php");
    exit;
}
?>
