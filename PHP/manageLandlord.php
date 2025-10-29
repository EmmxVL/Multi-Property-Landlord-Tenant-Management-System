<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Edit Landlord</h2>
    <form action="landlordManager.php" method="POST" class="space-y-4">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($landlord['user_id']); ?>">
        <input type="hidden" name="action" value="edit">

        <div>
            <label class="block text-gray-700 mb-1">Full Name</label>
            <input type="text" name="full_name" required
                   class="w-full p-2 border rounded"
                   value="<?php echo htmlspecialchars($landlord['full_name']); ?>">
        </div>

        <div>
            <label class="block text-gray-700 mb-1">Phone Number</label>
            <input type="tel" name="phone" maxlength="11"
                   class="w-full p-2 border rounded"
                   value="<?php echo htmlspecialchars($landlord['phone_no']); ?>">
        </div>

        <div>
            <label class="block text-gray-700 mb-1">Password (leave blank to keep current)</label>
            <input type="password" name="password" class="w-full p-2 border rounded" placeholder="New password">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex-1">Update</button>
            <a href="dashboard/admin_dashboard.php" class="bg-gray-300 text-gray-700 px-4 py-2 rounded flex-1 text-center hover:bg-gray-400">Cancel</a>
        </div>
    </form>
</div>

<script>
<?php if (isset($_SESSION['admin_success'])): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: <?php echo json_encode($_SESSION['admin_success']); ?>,
        timer: 3000,
        showConfirmButton: false
    });
    <?php unset($_SESSION['admin_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['admin_error'])): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: <?php echo json_encode($_SESSION['admin_error']); ?>
    });
    <?php unset($_SESSION['admin_error']); ?>
<?php endif; ?>
</script>

</body>
</html>
