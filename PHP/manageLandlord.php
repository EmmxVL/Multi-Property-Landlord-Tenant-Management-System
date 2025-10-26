<?php
session_start();
require_once "dbConnect.php";
require_once "landlordManager.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$manager = new LandlordManager($db);

$userId = intval($_GET['user_id'] ?? 0);

if (!$userId) {
    $_SESSION['admin_error'] = "Invalid landlord ID.";
    header("Location: dashboard/admin_dashboard.php"); // Redirect to dashboard
    exit;
}

// Fetch landlord info
$landlords = $manager->getAllLandlords();
$landlord = null;
foreach ($landlords as $l) {
    if ($l['user_id'] == $userId) {
        $landlord = $l;
        break;
    }
}

if (!$landlord) {
    $_SESSION['admin_error'] = "Landlord not found.";
    header("Location: dashboard/admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
