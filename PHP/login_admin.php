<?php
require_once "dbConnect.php";
require_once "Auth.php";
session_start();

// *** FIXED: Points to the correct admin login page ***
$errorRedirectPage = '../login_page_admin.php'; 

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Pass the correct error page to the Auth class
    $auth = new Auth($db, false, $errorRedirectPage);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $phone = $_POST["phone"] ?? '';
        $password = $_POST["password"] ?? '';

        if (empty($phone) || empty($password)) {
            $_SESSION["login_error"] = "Please enter both phone and password.";
            header("Location: $errorRedirectPage");
            exit;
        }

        // Only allow 'Admin' role to log in here
        $auth->login($phone, $password, ['Admin']);
    } else {
        header("Location: $errorRedirectPage");
        exit;
    }
} catch (Exception $e) {
    $_SESSION["login_error"] = "An error occurred: " . $e->getMessage();
    header("Location: $errorRedirectPage");
    exit;
}