<?php
session_start();
require_once "dbConnect.php";
require_once "TenantManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$propertyId = isset($_GET["property_id"]) ? (int)$_GET["property_id"] : 0;

if ($propertyId <= 0) {
    echo "Invalid property.";
    exit;
}

$db = (new Database())->getConnection();
$tenantManager = new TenantManager($db, $userId);

// ✅ Add new tenant
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_tenant"])) {
    $tenantName = trim($_POST["tenant_name"]);
    $contact = trim($_POST["contact"]);
    $unitId = (int)$_POST["unit_id"];
    $tenantManager->addTenant($propertyId, $unitId, $tenantName, $contact);
    header("Location: manageTenant.php?property_id=$propertyId");
    exit;
}

// ✅ Delete tenant
if (isset($_GET["delete"])) {
    $tenantId = (int)$_GET["delete"];
    $tenantManager->deleteTenant($tenantId);
    header("Location: manageTenant.php?property_id=$propertyId");
    exit;
}

// ✅ Fetch all tenants for this property
$tenants = $tenantManager->getTenantsByProperty($propertyId);

// ✅ Fetch available units for dropdown
$unitQuery = $db->prepare("SELECT unit_id, unit_name FROM unit_tbl WHERE property_id = :property_id AND user_id = :user_id");
$unitQuery->execute([':property_id' => $propertyId, ':user_id' => $userId]);
$units = $unitQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tenants</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    <!-- HEADER -->
    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly Landlord</h1>
                    <p class="text-xs text-slate-500">
                        Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?>!
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="text-slate-700 text-sm hidden sm:inline">
                        <?= htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?>
                    </span>
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                        <?php
                            $fullName = $_SESSION['full_name'] ?? 'LU';
                            $names = explode(' ', $fullName);
                            $initials = ($names[0][0] ?? '') . ($names[1][0] ?? '');
                            echo htmlspecialchars(strtoupper($initials) ?: 'U');
                        ?>
                    </div>
                    <a href="../logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <div class="max-w-5xl mx-auto bg-white mt-10 p-6 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">👥 Manage Tenants</h1>

        <!-- Add Tenant Form -->
        <form method="POST" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="tenant_name" placeholder="Tenant Full Name" class="border p-2 rounded" required>
            <input type="text" name="contact" placeholder="Contact Number" class="border p-2 rounded" required>
            <select name="unit_id" class="border p-2 rounded" required>
                <option value="">Select Unit</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= $unit['unit_id'] ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="md:col-span-3 text-right">
                <button type="submit" name="add_tenant" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Add Tenant</button>
            </div>
        </form>

        <!-- List of Tenants -->
        <h2 class="text-xl font-semibold mb-3">Tenants in this Property</h2>

        <?php if (count($tenants) > 0): ?>
            <table class="w-full border-collapse border border-slate-300 text-sm">
                <thead class="bg-slate-200">
                    <tr>
                        <th class="border p-2 text-left">Tenant Name</th>
                        <th class="border p-2 text-left">Contact</th>
                        <th class="border p-2 text-left">Unit</th>
                        <th class="border p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tenants as $t): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="border p-2"><?= htmlspecialchars($t["tenant_name"]) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($t["contact"]) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($t["unit_name"]) ?></td>
                            <td class="border p-2 text-center">
                                <a href="updateTenant.php?tenant_id=<?= $t['tenant_id'] ?>&property_id=<?= $propertyId ?>" class="text-blue-600 hover:underline">Edit</a> |
                                <a href="?delete=<?= $t['tenant_id'] ?>&property_id=<?= $propertyId ?>" onclick="return confirm('Delete this tenant?')" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-slate-500 mt-4">No tenants found for this property.</p>
        <?php endif; ?>

        <div class="mt-6">
            <a href="manageProperties.php" class="text-blue-600 hover:underline">← Back to Properties</a>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-blue-900 text-white mt-12">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">Unitly</h3>
                    <p class="text-blue-100 text-sm leading-relaxed">
                        Simplifying property management by connecting landlords and tenants seamlessly.
                    </p>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-4 text-blue-200">Contact Us</h4>
                    <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
                    <p class="text-blue-100 text-sm">+63 (0906) 581-6503</p>
                    <p class="text-blue-100 text-sm">Unitlyph@gmail.com</p>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-4 text-blue-200">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Developers</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-4 text-blue-200">Stay Connected</h4>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="email" placeholder="Enter your email"
                               class="newsletter-input w-full p-2 rounded bg-blue-800 border border-blue-700 focus:outline-none focus:border-blue-500 text-sm"
                               required>
                        <button type="submit"
                                class="newsletter-btn w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-sm transition-colors">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center text-center md:text-left">
                <div class="text-blue-200 text-sm mb-4 md:mb-0">
                    © <?= date("Y"); ?> Unitly. All rights reserved.
                </div>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="footer-bottom-link hover:text-white">Privacy Policy</a>
                    <a href="#" class="footer-bottom-link hover:text-white">Terms of Service</a>
                    <a href="#" class="footer-bottom-link hover:text-white">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
