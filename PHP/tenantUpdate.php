<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php"; // Uses the new merged class

// 1. Check Landlord Auth
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    $_SESSION['landlord_error'] = "Unauthorized action.";
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

$landlordId = (int)$_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$tenantManager = new TenantManager($db, $landlordId);

try {
    // 2. Check if this is a DELETE request via GET (from manageTenants.js)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $tenantUserId = (int)$_GET['id'];

        if ($tenantUserId <= 0) {
            $_SESSION['landlord_error'] = "Invalid tenant ID.";
        } else {
            // Attempt to delete tenant
            $deleted = $tenantManager->deleteTenant($tenantUserId);

            if ($deleted) {
                $_SESSION['landlord_success'] = "Tenant deleted successfully!";
            } else {
                $_SESSION['landlord_error'] = $_SESSION['landlord_error'] ?? "Tenant could not be deleted (possibly has an active lease).";
            }
        }

        // Redirect back to tenant list
        header("Location: manageTenants.php");
        exit;
    }

    // 3. Otherwise, handle POST updates (existing updateTenantInfo logic)
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $tenantUserId = (int)($_POST['user_id'] ?? 0);
        $redirectUrl = "editTenant.php?id=" . $tenantUserId;

        if ($tenantUserId <= 0) {
            $_SESSION['landlord_error'] = "Invalid tenant ID.";
            header("Location: manageTenants.php");
            exit;
        }

        $success = $tenantManager->updateTenantInfo($tenantUserId, $_POST, $_FILES);

        if ($success) {
            $_SESSION['landlord_success'] = "Tenant information updated successfully!";
        } else {
            $_SESSION['landlord_error'] = $_SESSION['landlord_error'] ?? "An unknown error occurred while updating.";
        }

        header("Location: " . $redirectUrl);
        exit;
    }

} catch (Exception $e) {
    $_SESSION['landlord_error'] = "Fatal Error: " . $e->getMessage();
    header("Location: manageTenants.php");
    exit;
}
