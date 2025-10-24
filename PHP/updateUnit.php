<?php
session_start();
require_once "dbConnect.php";
require_once "UnitManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$propertyId = isset($_GET["property_id"]) ? (int)$_GET["property_id"] : 0;
$unitId = isset($_GET["unit_id"]) ? (int)$_GET["unit_id"] : 0;

if ($propertyId <= 0 || $unitId <= 0) {
    echo "Invalid request.";
    exit;
}

$db = (new Database())->getConnection();
$unitManager = new UnitManager($db, $userId);

// ✅ Fetch unit info
$stmt = $db->prepare("SELECT * FROM unit_tbl WHERE unit_id = :unit_id AND user_id = :user_id");
$stmt->execute([':unit_id' => $unitId, ':user_id' => $userId]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unit) {
    echo "Unit not found or unauthorized.";
    exit;
}

// ✅ Handle Update Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_unit"])) {
    $unitName = trim($_POST["unit_name"]);
    $rent = (int)$_POST["rent"];

    if ($unitManager->updateUnit($unitId, $unitName, $rent)) {
        $_SESSION["success"] = "Unit updated successfully!";
        header("Location: manageUnit.php?property_id=$propertyId");
        exit;
    } else {
        $_SESSION["error"] = "Failed to update unit.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Unit</title>
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
    <main class="flex-grow flex justify-center items-center py-12">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-slate-800 mb-6 text-center">✏️ Update Unit</h1>

        <?php if (!empty($_SESSION["error"])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION["error"]) ?>
            </div>
            <?php unset($_SESSION["error"]); ?>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Unit Name</label>
                <input type="text" name="unit_name" value="<?= htmlspecialchars($unit['unit_name']) ?>" class="w-full border p-2 rounded" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Rent Amount</label>
                <input type="number" name="rent" value="<?= htmlspecialchars($unit['rent']) ?>" class="w-full border p-2 rounded" required>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="manageUnit.php?property_id=<?= $propertyId ?>" class="text-blue-600 hover:underline">← Back</a>
                <button type="submit" name="update_unit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Save Changes</button>
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
