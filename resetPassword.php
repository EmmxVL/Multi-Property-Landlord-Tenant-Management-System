<?php
session_start();
require_once "PHP/dbConnect.php";

if (!isset($_SESSION['verified_phone'])) {
    header("Location: forgotPassword.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPass = trim($_POST["password"]);
    $confirmPass = trim($_POST["confirm_password"]);

    if ($newPass !== $confirmPass) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: resetPassword.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $phone = $_SESSION['verified_phone'];

    $update = $db->prepare("UPDATE user_tbl SET password = ? WHERE phone_no = ?");
    $update->execute([$hash, $phone]);

    unset($_SESSION['verified_phone']);
    $_SESSION['success'] = "Password successfully reset! Please log in.";

    header("Location: login_page.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-semibold text-center mb-4">Reset Password</h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label class="block mb-2 text-gray-700">New Password:</label>
            <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 mb-4" required>

            <label class="block mb-2 text-gray-700">Confirm Password:</label>
            <input type="password" name="confirm_password" class="w-full border border-gray-300 rounded px-3 py-2 mb-4" required>

            <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Reset Password</button>
        </form>
    </div>

</body>
</html>
