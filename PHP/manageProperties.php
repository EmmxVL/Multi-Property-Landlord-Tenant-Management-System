<?php
session_start();
require_once "dbConnect.php";
require_once "PropertyManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$propertyManager = new PropertyManager($db, $userId);

// ✅ Add new property
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_property"])) {
    $name = trim($_POST["property_name"]);
    $location = trim($_POST["location"]);
    $propertyManager->addProperty($name, $location);
}

// ✅ Delete property
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $propertyManager->deleteProperty($id);
    header("Location: manageProperties.php");
    exit;
}

// ✅ Fetch landlord's properties
$properties = $propertyManager->getProperties();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Properties</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
   <script src="../assets/script.js" defer></script> 
    <script src="../assets/admin.js" defer></script>
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
    
                <div class="flex items-center space-x-2">
                     <span class="text-slate-700 text-sm hidden sm:inline"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Landlord'); ?></span>
                     <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                         <?php // Initials
                             $fullName = $_SESSION['full_name'] ?? 'LU'; $names = explode(' ', $fullName);
                             $initials = ($names[0][0] ?? '') . ($names[1][0] ?? ''); echo htmlspecialchars(strtoupper($initials) ?: 'U');
                         ?>
                     </div>
                     <a href="../logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                     </a>
                </div>
            </div>
        </div>
    </header>
    <div class="max-w-4xl mx-auto bg-white mt-10 p-6 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">🏠 Manage Properties</h1>

        <?php if (!empty($_SESSION["error"])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION["error"]) ?>
            </div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        <!-- Add Property Form -->
        <form method="POST" class="mb-6 space-y-4">
            <input type="text" name="property_name" placeholder="Property Name" class="w-full border p-2 rounded" required>
            <input type="text" name="location" placeholder="Location" class="w-full border p-2 rounded" required>
            <button type="submit" name="add_property" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Add Property</button>
        </form>

        <!-- List of Properties -->
        <h2 class="text-xl font-semibold mb-3">Your Properties</h2>

        <?php if (count($properties) > 0): ?>
            <table class="w-full border-collapse border border-slate-300 text-sm">
                <thead class="bg-slate-200">
                    <tr>
                        <th class="border p-2 text-left">Property Name</th>
                        <th class="border p-2 text-left">Location</th>
                        <th class="border p-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <!-- ✅ Clickable property name -->
                            <td class="border p-2">
                                <a href="manageUnit.php?property_id=<?= $p['property_id'] ?>" 
                                   class="text-blue-600 hover:underline font-medium">
                                   <?= htmlspecialchars($p["property_name"]) ?>
                                </a>
                            </td>
                            <td class="border p-2"><?= htmlspecialchars($p["location"]) ?></td>
                            <td class="border p-2 text-center">
                                <a href="updateProperties.php?id=<?= $p['property_id'] ?>" class="text-blue-600 hover:underline">Edit</a> |
                                <a href="?delete=<?= $p['property_id'] ?>" onclick="return confirm('Delete this property?')" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-slate-500 mt-4">No properties yet.</p>
        <?php endif; ?>

        <div class="mt-6">
            <a href="dashboard/landlord_dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        </div>
    </div>
    <footer class="bg-blue-900 text-white mt-12">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">CompanyName</h3>
                    <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Vision</h4>
                    <p class="text-blue-100 leading-relaxed text-sm">To revolutionize property management by fostering seamless connections between landlords and tenants.</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Contact Us</h4>
                    <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
                    <p class="text-blue-100 text-sm">+63 (0906) 581-6503</p>
                    <p class="text-blue-100 text-sm">Unitlyph@gmail.com</p>
                    <p class="text-blue-100 text-sm">www.unitly.com</p>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Developers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Stay Connected</h4>
                    <div class="flex space-x-4 mb-6">
                   
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M...Z"/></svg></a>
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M...Z"/></svg></a>
                    </div>
                    <h5 class="text-lg font-medium mb-3 text-blue-200">Newsletter</h5>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="email" id="newsletter-email" placeholder="Enter your email" class="newsletter-input w-full p-2 rounded bg-blue-800 border border-blue-700 focus:outline-none focus:border-blue-500 text-sm" required>
                        <button type="submit" class="newsletter-btn w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-sm transition-colors">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center text-center md:text-left">
                <div class="text-blue-200 text-sm mb-4 md:mb-0">© <?php echo date("Y"); ?> Unitly. All rights reserved.</div>
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
