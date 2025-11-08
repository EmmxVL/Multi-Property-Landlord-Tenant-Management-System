<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
require_once "Auth.php";

$database = new Database();
$db = $database->getConnection();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$auth = new Auth($db, $isAjax);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login_page.php");
    exit;
}

$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');

if ($phone === '' || $password === '') {
    $authReflection = new ReflectionClass($auth);
    $sendError = $authReflection->getMethod('sendError');
    $sendError->setAccessible(true);
    $sendError->invoke($auth, 'Phone and password are required.');
    exit;
}

// Perform login
try {
    $auth->login($phone, $password);
} catch (Exception $e) {
    // You could also create a dedicated error handler class later
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
