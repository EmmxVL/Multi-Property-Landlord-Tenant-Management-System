<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../../PHP/dbConnect.php";
$database = new Database();
$db = $database->getConnection();

// ✅ Ensure admin access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../login_page.php");
    exit;
}

// ✅ Check if landlord ID is provided
if (!isset($_GET["user_id"])) {
    header("Location: dashboard/admin_dashboard.php");
exit;

}

$user_id = intval($_GET["user_id"]);

// ✅ Fetch landlord data
$stmt = $db->prepare("
    SELECT u.user_id, u.full_name, u.phone_no
    FROM user_tbl u
    JOIN user_role_tbl ur ON u.user_id = ur.user_id
    WHERE ur.role_id = 1 AND u.user_id = ?
");
$stmt->execute([$user_id]);
$landlord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$landlord) {
    $_SESSION["admin_error"] = "Landlord not found.";
    header("Location: dashboard/admin_dashboard.php");
exit;

}

// ✅ Handle form submission (Update)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $phone_no = trim($_POST["phone_no"]);
    $password = trim($_POST["password"]);

    if (empty($full_name) || empty($phone_no)) {
        $_SESSION["admin_error"] = "Full name and phone number are required.";
    } else {
        // Begin update query
        if (!empty($password)) {
            // Update with password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = $db->prepare("
                UPDATE user_tbl
                SET full_name = ?, phone_no = ?, password = ?
                WHERE user_id = ?
            ");
            $success = $update->execute([$full_name, $phone_no, $hashedPassword, $user_id]);
        } else {
            // Update without password
            $update = $db->prepare("
                UPDATE user_tbl
                SET full_name = ?, phone_no = ?
                WHERE user_id = ?
            ");
            $success = $update->execute([$full_name, $phone_no, $user_id]);
        }

        if ($success) {
            $_SESSION["admin_success"] = "Landlord details updated successfully.";
        } else {
            $_SESSION["admin_error"] = "Failed to update landlord.";
        }
    }

    // ✅ Correct redirect path (to dashboard inside /PHP/)
    header("Location: dashboard/admin_dashboard.php");
exit;

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-800 mb-6 text-center">Edit Landlord</h1>
        <form method="POST">
            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($landlord['full_name']); ?>" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="phone_no" class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                <input type="tel" id="phone_no" name="phone_no" maxlength="11" value="<?php echo htmlspecialchars($landlord['phone_no']); ?>" required class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password (optional)</label>
                <input type="password" id="password" name="password" placeholder="Leave blank to keep current password" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg">Save Changes</button>
                <a href="../../dashboard/admin_dashboard.php" class="flex-1 text-center bg-slate-200 hover:bg-slate-300 text-slate-700 py-3 rounded-lg">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
