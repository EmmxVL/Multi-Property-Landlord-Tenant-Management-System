<?php
session_start();
require_once "dbConnect.php";
require_once "maintenanceManager.php";

// ✅ Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$maintenanceManager = new MaintenanceManager($db);

// ✅ Fetch units leased by this tenant
$stmt = $db->prepare("
    SELECT u.unit_id, u.unit_name
    FROM lease_tbl l
    JOIN unit_tbl u ON l.unit_id = u.unit_id
    WHERE l.user_id = :tenant_id AND l.lease_status = 'Active'
");
$stmt->execute([':tenant_id' => $_SESSION["user_id"]]);
$tenantUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle new maintenance request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["description"], $_POST["unit_id"])) {
    $unitId = $_POST["unit_id"];
    $userId = $_SESSION["user_id"];
    $description = trim($_POST["description"]);

    $maintenanceManager->createRequest($unitId, $userId, $description);
    header("Location: tenantMaintenance.php");
    exit;
}

// ✅ Fetch maintenance requests for this tenant (with unit names)
$requests = $maintenanceManager->getRequestsByTenant($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Maintenance Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-md">
    <!--  Back Button -->
    <div class="mb-4">
        <a href="dashboard/tenant_dashboard.php"
           class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-md transition">
            ← Back to Dashboard
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Maintenance Requests</h1>

    <!-- Submit Form -->
    <form method="POST" class="mb-8 space-y-4">
        <div>
            <label for="unit_id" class="block text-sm font-medium text-gray-600">Select Unit</label>
            <select name="unit_id" id="unit_id" required
                    class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:ring-indigo-100">
                <option value="">-- Select Unit --</option>
                <?php foreach ($tenantUnits as $unit): ?>
                    <option value="<?= htmlspecialchars($unit['unit_id']) ?>">
                        <?= htmlspecialchars($unit['unit_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-600">Description</label>
            <textarea name="description" id="description" rows="3" required
                      class="w-full p-2 border rounded-md focus:outline-none focus:ring focus:ring-indigo-100"></textarea>
        </div>

        <button type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
            Submit Request
        </button>
    </form>

    <!-- Maintenance Cards -->
    <div class="grid gap-4">
        <?php if (empty($requests)): ?>
            <p class="text-gray-500 text-center">No maintenance requests yet.</p>
        <?php else: ?>
            <?php foreach ($requests as $req): ?>
                <div class="border p-4 rounded-lg shadow-sm <?= $req['maintenance_status'] === 'Completed' ? 'bg-green-50' : ($req['maintenance_status'] === 'Rejected' ? 'bg-red-50' : 'bg-yellow-50') ?>">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="font-semibold text-gray-700">
                            <?= htmlspecialchars($req['unit_name'] ?? 'Unknown Unit') ?>
                        </h2>
                        <span class="text-sm font-medium px-2 py-1 rounded <?= 
                            $req['maintenance_status'] === 'Completed' ? 'bg-green-100 text-green-700' : (
                            $req['maintenance_status'] === 'Rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                            <?= htmlspecialchars($req['maintenance_status']) ?>
                        </span>
                    </div>
                    <p class="text-gray-700"><?= htmlspecialchars($req['description']) ?></p>
                    <p class="text-xs text-gray-500 mt-2">
                        Start: <?= htmlspecialchars($req['maintenance_start_date'] ?? 'Pending') ?> |
                        End: <?= htmlspecialchars($req['maintenance_end_date'] ?? '---') ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
