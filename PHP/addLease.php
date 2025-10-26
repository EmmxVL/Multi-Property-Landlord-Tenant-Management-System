<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php";
require_once "leaseManager.php";

// ✅ Restrict access to landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

// create DB connection (PDO) using your Database class
$database = new Database();
$db = $database->getConnection();

$landlordId = (int) ($_SESSION["user_id"] ?? 0);
if ($landlordId <= 0) {
    header("Location: ../login_page.php");
    exit;
}

// instantiate managers with the PDO connection
$tenantManager = new TenantManager($db, $landlordId);
$leaseManager  = new LeaseManager($db);

// Fetch tenants belonging to this landlord (TenantManager handles landlord linking internally)
$tenants = $tenantManager->getTenants();

// Fetch available units owned by landlord
try {
    $stmt = $db->prepare("
        SELECT u.unit_id, u.unit_name
        FROM unit_tbl u
        INNER JOIN property_tbl p ON u.property_id = p.property_id
        WHERE p.user_id = :landlord_id
        ORDER BY u.unit_name ASC
    ");
    $stmt->execute([':landlord_id' => $landlordId]);
    $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $units = [];
    $error = "Database error when fetching units: " . $e->getMessage();
}

// ✅ Handle form submission
$error = $error ?? null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tenantId = $_POST["tenant_id"] ?? null;
    $unitId   = $_POST["unit_id"] ?? null;
    $start    = $_POST["lease_start_date"] ?? null;
    $end      = $_POST["lease_end_date"] ?? null;
    $balance  = $_POST["balance"] ?? 0;

    // basic validation
    if (empty($tenantId) || empty($unitId) || empty($start) || empty($end)) {
        $error = "All fields except balance are required.";
    } else {
        // optional: further validate dates, numeric values
        $success = $leaseManager->createLease((int)$tenantId, (int)$unitId, $start, $end, (int)$balance);

        if ($success) {
            $_SESSION["landlord_success"] = "Lease created successfully!";
            // redirect back to landlord dashboard (adjust path if needed)
            header("Location: dashboard/landlord_dashboard.php");
            exit;
        } else {
            $error = $_SESSION["landlord_error"] ?? "Failed to create lease.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Add Lease</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="m-0">Add New Lease</h4>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Tenant</label>
                    <select name="tenant_id" class="form-select" required>
                        <option value="">-- Choose Tenant --</option>
                        <?php foreach ($tenants as $t): ?>
                            <option value="<?= htmlspecialchars($t['user_id']) ?>">
                                <?= htmlspecialchars($t['full_name']) ?> <?= isset($t['phone_no']) ? '('.htmlspecialchars($t['phone_no']).')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Unit</label>
                    <select name="unit_id" class="form-select" required>
                        <option value="">-- Choose Unit --</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= htmlspecialchars($u['unit_id']) ?>"><?= htmlspecialchars($u['unit_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Lease Start Date</label>
                    <input type="date" name="lease_start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Lease End Date</label>
                    <input type="date" name="lease_end_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Initial Balance (Optional)</label>
                    <input type="number" name="balance" class="form-control" min="0" placeholder="0">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">Create Lease</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="dashboard/landlord_dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>

        </div>
    </div>
</div>
</body>
</html>
