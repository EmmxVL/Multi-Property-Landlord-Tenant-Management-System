<?php
session_start();
require_once "dbConnect.php";
require_once "maintenanceManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$manager = new MaintenanceManager($db);
$landlordId = $_SESSION["user_id"]; // Assuming landlord’s ID is stored in session

// ✅ Handle status updates
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['request_id'], $_POST['status'])) {
    $requestId = (int) $_POST['request_id'];
    $status = $_POST['status'];
    $endDate = ($status === 'Completed') ? date('Y-m-d') : null;
    $manager->updateStatus($requestId, $status, $endDate);
}

// ✅ Get all maintenance requests for this landlord
$maintenanceRequests = $manager->getRequestsByLandlord($landlordId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Maintenance Requests</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
<div class="max-w-6xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Manage Maintenance Requests</h1>
        <a href="dashboard/landlord_dashboard.php" class="text-sm text-orange-600 hover:underline">← Back to Dashboard</a>
    </div>

    <?php if (empty($maintenanceRequests)): ?>
        <p class="text-slate-600">No maintenance requests yet.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-slate-200">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-2 border-b">Request ID</th>
                        <th class="px-4 py-2 border-b">Unit</th>
                        <th class="px-4 py-2 border-b">Description</th>
                        <th class="px-4 py-2 border-b">Start Date</th>
                        <th class="px-4 py-2 border-b">End Date</th>
                        <th class="px-4 py-2 border-b">Status</th>
                        <th class="px-4 py-2 border-b text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($maintenanceRequests as $req): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($req['request_id']) ?></td>
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($req['unit_name']) ?></td>
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($req['description']) ?></td>
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($req['maintenance_start_date'] ?? '-') ?></td>
                        <td class="px-4 py-2 border-b"><?= htmlspecialchars($req['maintenance_end_date'] ?? '-') ?></td>
                        <td class="px-4 py-2 border-b font-semibold">
                            <?php
                            $status = htmlspecialchars($req['maintenance_status']);
                            $color = match ($status) {
                                'Ongoing' => 'text-blue-600',
                                'Completed' => 'text-green-600',
                                'Rejected' => 'text-red-600',
                                default => 'text-slate-600',
                            };
                            ?>
                            <span class="<?= $color ?>"><?= $status ?></span>
                        </td>
                        <td class="px-4 py-2 border-b text-center">
                            <form method="POST" class="flex items-center justify-center gap-2">
                                <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                <select name="status" class="border border-slate-300 rounded px-2 py-1 text-sm">
                                    <option value="Ongoing" <?= $status === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                    <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <button type="submit"
                                        class="px-3 py-1.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">
                                    Update
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
