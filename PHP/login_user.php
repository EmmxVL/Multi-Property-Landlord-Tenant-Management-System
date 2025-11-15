<?php
require_once "dbConnect.php";
require_once "Auth.php";
session_start();

$errorRedirectPage = '../login_page_user.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $auth = new Auth($db, false, $errorRedirectPage);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $phone = $_POST["phone"] ?? '';
        $password = $_POST["password"] ?? '';

        if (empty($phone) || empty($password)) {
            $_SESSION["login_error"] = "Please enter both phone and password.";
            header("Location: $errorRedirectPage");
            exit;
        }

        $auth->login($phone, $password, ['Tenant', 'Landlord']);
    } else {
        header("Location: $errorRedirectPage");
        exit;
    }
} catch (Exception $e) {
    $_SESSION["login_error"] = "An error occurred: " . $e->getMessage();
    header("Location: $errorRedirectPage");
    exit;
}