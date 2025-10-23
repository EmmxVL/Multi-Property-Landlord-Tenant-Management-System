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
<body class="bg-slate-100 min-h-screen">
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
</body>
</html>
