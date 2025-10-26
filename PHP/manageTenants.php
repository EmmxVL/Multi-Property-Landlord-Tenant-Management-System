<?php
session_start();
require_once "dbConnect.php";
require_once "AccountManager.php"; // Use AccountManager instead

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$accountManager = new AccountManager($db);

// ✅ Add new tenant
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_tenant"])) {
    $fullName = trim($_POST["full_name"]);
    $phone = trim($_POST["phone_no"]);
    $password = trim($_POST["password"]);

    try {
        $accountManager->createTenant($fullName, $phone, $password); // redirect handled inside
    } catch (Exception $e) {
        $_SESSION["error"] = "Failed to create tenant: " . $e->getMessage();
        header("Location: manageTenants.php");
        exit;
    }
}

// ✅ Delete tenant
if (isset($_GET["delete"])) {
    $tenantId = (int)$_GET["delete"];
    try {
        $stmt = $db->prepare("DELETE FROM user_tbl WHERE user_id = :id");
        $stmt->execute([":id" => $tenantId]);
        $_SESSION["success"] = "Tenant deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION["error"] = "Error deleting tenant: " . $e->getMessage();
    }
    header("Location: manageTenants.php");
    exit;
}

// ✅ Fetch all tenants (role_id = 2)
$stmt = $db->prepare("
    SELECT u.user_id, u.full_name, u.phone_no
    FROM user_tbl u
    INNER JOIN user_role_tbl ur ON u.user_id = ur.user_id
    WHERE ur.role_id = 2
    ORDER BY u.full_name ASC
");
$stmt->execute();
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tenants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js" defer></script> 
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly Landlord</h1>
                    <p class="text-xs text-slate-500">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?>!</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-slate-700 text-sm hidden sm:inline"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?></span>
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                    <?php 
                        $fullName = $_SESSION['full_name'] ?? 'LU'; 
                        $names = explode(' ', $fullName);
                        $initials = ($names[0][0] ?? '') . ($names[1][0] ?? ''); 
                        echo htmlspecialchars(strtoupper($initials) ?: 'U');
                    ?>
                </div>
                <a href="logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-4xl mx-auto bg-white mt-10 p-6 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">👥 Manage Tenants</h1>

        <?php if (!empty($_SESSION["error"])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION["error"]) ?>
            </div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION["success"])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION["success"]) ?>
            </div>
            <?php unset($_SESSION["success"]); ?>
        <?php endif; ?>

        <!-- Add Tenant Form -->
        <form method="POST" class="mb-6 space-y-4">
            <input type="text" name="full_name" placeholder="Full Name" class="w-full border p-2 rounded" required>
            <input type="text" name="phone_no" placeholder="Phone Number" class="w-full border p-2 rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full border p-2 rounded" required>
            <button type="submit" name="add_tenant" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Add Tenant</button>
        </form>

        <!-- List of Tenants -->
        <h2 class="text-xl font-semibold mb-3">Your Tenants</h2>

        <?php if (count($tenants) > 0): ?>
            <table class="w-full border-collapse border border-slate-300 text-sm">
                <thead class="bg-slate-200">
                    <tr>
                        <th class="border p-2 text-left">Full Name</th>
                        <th class="border p-2 text-left">Phone Number</th>
                        <th class="border p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $tenant): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="border p-2"><?= htmlspecialchars($tenant["full_name"]) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($tenant["phone_no"]) ?></td>
                            <td class="border p-2 text-center">
                                <a href="updateTenants.php?id=<?= $tenant['user_id'] ?>" class="text-blue-600 hover:underline">Edit</a> |
                                <a href="?delete=<?= $tenant['user_id'] ?>" onclick="return confirm('Delete this tenant?')" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-slate-500 mt-4">No tenants yet.</p>
        <?php endif; ?>

        <div class="mt-6">
            <a href="dashboard/landlord_dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        </div>
    </div>

    <footer class="bg-blue-900 text-white mt-12">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">Unitly</h3>
                    <p class="text-blue-100 leading-relaxed text-sm">Helping landlords manage tenants with ease.</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Contact Us</h4>
                    <p class="text-blue-100 text-sm">004 Pilahan East, Sabang, Lipa City</p>
                    <p class="text-blue-100 text-sm">+63 (906) 581-6503</p>
                    <p class="text-blue-100 text-sm">unitlyph@gmail.com</p>
                </div>
            </div>
        </div>
        <div class="border-t border-blue-700 py-4 text-center text-blue-200 text-sm">
            © <?php echo date("Y"); ?> Unitly. All rights reserved.
        </div>
    </footer>
</body>
</html>
y