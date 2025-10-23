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
</head>
<body class="bg-slate-100 min-h-screen">
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
</body>
</html>
