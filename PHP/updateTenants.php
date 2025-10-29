<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php";

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$tenantManager = new TenantManager($db, $userId);

$id = (int)($_GET["id"] ?? 0);
$tenants = $tenantManager->getTenants();
$tenant = null;

foreach ($tenants as $t) {
    if ($t["user_id"] == $id) {
        $tenant = $t;
        break;
    }
}

if (!$tenant) {
    die("Tenant not found or unauthorized.");
}

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"] ?? "");

    if ($tenantManager->updateTenant($id, $fullName, $phone, $password ?: null)) {
        $_SESSION["success"] = "Tenant updated successfully.";
        header("Location: manageTenants.php");
        exit;
    } else {
        $_SESSION["error"] = "Failed to update tenant.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unitly - Edit Tenant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    <!-- HEADER -->
   <?php include '../assets/header.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-grow max-w-3xl mx-auto px-6 py-12 w-full">
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                ✏️ Edit Tenant
            </h1>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="full_name"
                           value="<?= htmlspecialchars($tenant["full_name"]) ?>"
                           class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone"
                           value="<?= htmlspecialchars($tenant["phone_no"]) ?>"
                           class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">New Password (optional)</label>
                    <input type="password" name="password"
                           placeholder="Leave blank to keep current password"
                           class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="manageTenants.php"
                       class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-5 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- FOOTER -->
    <?php include '../assets/footer.php'; ?>

</body>
</html>
