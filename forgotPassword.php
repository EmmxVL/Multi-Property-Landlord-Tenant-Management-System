<?php
ob_start(); // start output buffering early to prevent header errors
session_start();
require_once "PHP/dbConnect.php";
require_once "iprog_sms.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"]);

    if (empty($phone)) {
        $_SESSION['error'] = "Please enter your phone number.";
        header("Location: forgotPassword.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // Normalize phone number
    $phone = preg_replace('/[^+0-9]/', '', $phone);
    if (strpos($phone, '+63') === 0) {
        $phone = '0' . substr($phone, 3);
    }

// Check if phone exists
$stmt = $db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $result = sendOTP($phone);

    if (
        $result &&
        isset($result["status"]) &&
        strtolower(trim($result["status"])) === "success"
    ) {
        $_SESSION['otp_phone'] = $phone;

        if (ob_get_length()) {
            ob_end_clean();
        }

        header("Location: verify_otp.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to send OTP. Please try again.";
    }
} else {
    $_SESSION['error'] = "No account found with that phone number.";
}

header("Location: forgotPassword.php");
exit;
}

ob_end_flush(); // output any buffered HTML safely
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-semibold text-center mb-4">Forgot Password</h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php elseif (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-2 rounded mb-3">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label class="block mb-2 text-gray-700">Phone Number:</label>
            <input type="text" name="phone" class="w-full border border-gray-300 rounded px-3 py-2 mb-4"
                   placeholder="e.g. 09123456789" required>
            <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Send OTP</button>
        </form>

        <div class="mt-4 text-center">
            <button type="button" onclick="window.location.href='login_page.php'" class="text-blue-600 hover:underline">← Back to Login</button>
        </div>
    </div>
</body>
</html>
