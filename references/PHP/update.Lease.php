<?php
session_start();
require_once "dbConnect.php";
require_once "leaseManager.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $leaseId = (int) $_POST["lease_id"];
    $newStatus = $_POST["new_status"] ?? "";
    $landlordId = $_SESSION["user_id"];

    $leaseManager = new LeaseManager($db);

    if ($leaseManager->updateLeaseStatus($leaseId, $newStatus)) {
        $_SESSION["success"] = "Lease updated successfully.";
    } else {
        $_SESSION["error"] = $_SESSION["landlord_error"] ?? "Failed to update lease.";
    }
}

header("Location: manage_leases.php");
exit;
?>
