<?php
session_start();
require_once "dbConnect.php";
require_once "PropertyManager.php";

// Redirect if not logged in as Landlord
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$propertyManager = new PropertyManager($db, $userId);

$id = (int)($_GET["id"] ?? 0);
$property = $propertyManager->getPropertyById($id);

if (!$property) {
    die("Property not found or unauthorized.");
}

// ✅ Update logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["property_name"]);
    $location = trim($_POST["location"]);

    if ($propertyManager->updateProperty($id, $name, $location)) {
        header("Location: manageProperties.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unitly - Edit Property</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <main class="flex-grow max-w-3xl mx-auto px-6 py-12 w-full">
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                ✏️ Edit Property
            </h1>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Property Name</label>
                    <input type="text" name="property_name"
                           value="<?= htmlspecialchars($property["property_name"]) ?>"
                           class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
                    <input type="text" name="location"
                           value="<?= htmlspecialchars($property["location"]) ?>"
                           class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="manageProperties.php"
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
