<?php
session_start();
require_once "dbConnect.php";
require_once "UnitManager.php";

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
$unitManager = new UnitManager($db, $userId);

// ✅ Add new unit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_unit"])) {
    $unitName = trim($_POST["unit_name"]);
    $rent = (int)$_POST["rent"];
    $unitManager->addUnit($propertyId, $unitName, $rent);
    header("Location: manageUnit.php?property_id=$propertyId");
    exit;
}

// ✅ Delete unit
if (isset($_GET["delete"])) {
    $unitId = (int)$_GET["delete"];
    $unitManager->deleteUnit($unitId);
    header("Location: manageUnit.php?property_id=$propertyId");
    exit;
}

// ✅ Fetch all units for this property
$units = $unitManager->getUnitsByProperty($propertyId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Units</title>
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
    <div class="max-w-4xl mx-auto bg-white mt-10 p-6 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">🏘 Manage Units</h1>

        <?php if (!empty($_SESSION["error"])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION["error"]) ?>
            </div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        <!-- Add Unit Form -->
        <form method="POST" class="mb-6 space-y-4">
            <input type="text" name="unit_name" placeholder="Unit Name" class="w-full border p-2 rounded" required>
            <input type="number" name="rent" placeholder="Rent Amount" class="w-full border p-2 rounded" required>
            <button type="submit" name="add_unit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Add Unit</button>
        </form>

        <!-- List of Units -->
        <h2 class="text-xl font-semibold mb-3">Units in this Property</h2>

        <?php if (count($units) > 0): ?>
            <table class="w-full border-collapse border border-slate-300 text-sm">
                <thead class="bg-slate-200">
                    <tr>
                        <th class="border p-2 text-left">Unit Name</th>
                        <th class="border p-2 text-left">Rent</th>
                        <th class="border p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $u): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="border p-2"><?= htmlspecialchars($u["unit_name"]) ?></td>
                            <td class="border p-2">₱<?= number_format($u["rent"], 2) ?></td>
                            <td class="border p-2 text-center">
                                <a href="updateUnit.php?unit_id=<?= $u['unit_id'] ?>&property_id=<?= $propertyId ?>" class="text-blue-600 hover:underline">Edit</a> |
                                <a href="?delete=<?= $u['unit_id'] ?>&property_id=<?= $propertyId ?>" onclick="return confirm('Delete this unit?')" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-slate-500 mt-4">No units found for this property.</p>
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
