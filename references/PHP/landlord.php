<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "accountManager.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

$fullName = trim($_POST["full_name"] ?? '');
$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');

$database = new Database();
$db = $database->getConnection();

$manager = new AccountManager($db);
$manager->createTenant((int)$_SESSION['user_id'], $fullName, $phone, $password);

?>
