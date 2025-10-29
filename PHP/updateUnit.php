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
   <?php include '../assets/header.php'; ?>
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
    <?php include '../assets/footer.php'; ?>
</body>
</html>
