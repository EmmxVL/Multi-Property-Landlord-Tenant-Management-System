<?php
session_start();
require_once "iprog_sms.php";

if (!isset($_SESSION['otp_phone'])) {
    header("Location: forgotPassword.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = trim($_POST["otp"]);
    $phone = $_SESSION['otp_phone'];

    $result = verifyOTP($phone, $otp);

    if ($result && $result["status"] === "success") {
        $_SESSION['verified_phone'] = $phone;
        unset($_SESSION['otp_phone']);
        header("Location: resetPassword.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid or expired OTP.";
        header("Location: verify_otp.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-semibold text-center mb-4">Verify OTP</h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label class="block mb-2 text-gray-700">Enter the 6-digit OTP:</label>
            <input type="text" name="otp" maxlength="6" class="w-full border border-gray-300 rounded px-3 py-2 mb-4 text-center" placeholder="123456" required>
            <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Verify OTP</button>
        </form>

        <!-- ✅ Back to Login button -->
        <div class="mt-4 text-center">
            <button type="button" onclick="window.location.href='login_page.php'" class="text-blue-600 hover:underline">
                ← Back to Login
            </button>
        </div>
    </div>

</body>
</html>
