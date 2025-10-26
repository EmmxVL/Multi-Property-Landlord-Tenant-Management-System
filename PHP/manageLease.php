<?php
// manageLease.php
session_start();

require_once "dbConnect.php";
require_once "leaseManager.php";

// Restrict to landlords
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_page.php");
    exit;
}

$landlordId = (int) $_SESSION["user_id"];

// Create DB connection (PDO) using your Database class from dbConnect.php
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    // If DB can't be created, show useful message
    $_SESSION['landlord_error'] = "Database connection failed: " . $e->getMessage();
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

// Instantiate LeaseManager with PDO
$leaseManager = new LeaseManager($db);

// Handle status update form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["lease_id"], $_POST["new_status"])) {
    $leaseId = (int) $_POST["lease_id"];
    $newStatus = $_POST["new_status"];

    if ($leaseManager->updateLeaseStatus($leaseId, $newStatus)) {
        $_SESSION["landlord_success"] = "Lease status updated successfully.";
    } else {
        $_SESSION["landlord_error"] = $_SESSION["landlord_error"] ?? "Failed to update lease status.";
    }

    // Redirect to avoid resubmission
    header("Location: manageLease.php");
    exit;
}

// Fetch leases for this landlord
$leases = $leaseManager->getLeasesByLandlord($landlordId);

// pull flash messages
$success = $_SESSION['landlord_success'] ?? null;
$error   = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Manage Leases</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <header class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Lease Management</h1>
            <a href="dashboard/landlord_dashboard.php" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        </header>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Your Leases</h2>
                <a href="addLease.php" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-sm">+ Add Lease</a>
            </div>

            <?php if (!empty($leases)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="border p-2 text-left">Unit</th>
                                <th class="border p-2 text-left">Tenant</th>
                                <th class="border p-2 text-left">Start</th>
                                <th class="border p-2 text-left">End</th>
                                <th class="border p-2 text-right">Balance</th>
                                <th class="border p-2 text-left">Status</th>
                                <th class="border p-2 text-center">Change Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leases as $lease): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="border p-2"><?= htmlspecialchars($lease['unit_name']) ?></td>
                                    <td class="border p-2">
                                        <?= htmlspecialchars($lease['tenant_name']) ?><br>
                                        <span class="text-xs text-slate-500"><?= htmlspecialchars($lease['tenant_phone']) ?></span>
                                    </td>
                                    <td class="border p-2"><?= htmlspecialchars($lease['lease_start_date']) ?></td>
                                    <td class="border p-2"><?= htmlspecialchars($lease['lease_end_date']) ?></td>
                                    <td class="border p-2 text-right">₱<?= number_format((int)$lease['balance'], 2) ?></td>
                                    <td class="border p-2"><?= htmlspecialchars($lease['lease_status']) ?></td>
                                    <td class="border p-2">
                                        <form method="POST" class="flex gap-2 items-center justify-center">
                                            <input type="hidden" name="lease_id" value="<?= (int)$lease['lease_id'] ?>">
                                            <select name="new_status" class="border p-1 rounded text-sm">
                                                <option value="Pending" <?= $lease['lease_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Active" <?= $lease['lease_status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                                <option value="Terminated" <?= $lease['lease_status'] === 'Terminated' ? 'selected' : '' ?>>Terminated</option>
                                            </select>
                                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-slate-500 italic">No leases found for your properties yet.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
