<?php
session_start();
require_once "dbConnect.php";
require_once "paymentManager.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $db = (new Database())->getConnection();
    $paymentManager = new PaymentManager($db);

    if ($paymentManager->updatePaymentStatus($paymentId, $status)) {
        $_SESSION['landlord_success'] = "Payment status updated successfully.";
    } else {
        $_SESSION['landlord_error'] = "Failed to update payment status.";
    }

    header("Location: dashboard/landlord_dashboard.php");
    exit;
}
?>
