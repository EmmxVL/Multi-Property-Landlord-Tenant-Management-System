<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php"; // Uses the new merged class
require_once "leaseManager.php"; // Needed to check active lease status

// 1. Check Landlord Auth
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../../login_page_user.php");
    exit;
}

$landlordId = (int)$_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$tenantManager = new TenantManager($db, $landlordId);
$leaseManager = new LeaseManager($db);

// Get session messages
$landlordSuccess = $_SESSION['landlord_success'] ?? null;
$landlordError  = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);

try {
    // 2. Fetch ALL approved tenants for this landlord
    $tenants = $tenantManager->getTenantsInfo();
    
    // 3. Fetch all active leases to check status
    $leases = $leaseManager->getLeasesByLandlord($landlordId);
    $activeLeaseTenantIds = [];
    foreach ($leases as $lease) {
        if ($lease['lease_status'] === 'Active' && isset($lease['tenant_name'])) {
            // We need to find the user_id associated with the tenant_name, as tenants array doesn't have name
            // This is a bit inefficient, let's just get IDs
        }
    }
    // Let's get active lease tenant IDs directly
    $stmt = $db->prepare("
        SELECT DISTINCT l.user_id 
        FROM lease_tbl l
        JOIN unit_tbl u ON l.unit_id = u.unit_id
        JOIN property_tbl p ON u.property_id = p.property_id
        WHERE l.lease_status = 'Active' AND p.user_id = :landlord_id
    ");
    $stmt->execute([':landlord_id' => $landlordId]);
    $activeLeaseTenantIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);


} catch (PDOException $e) {
    $tenants = [];
    $landlordError = "Error fetching tenants: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unitly - Manage Tenants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
    
    <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">
        <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                         <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
                          <circle cx="12" cy="7" r="4" stroke-width="2" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-slate-800">Manage My Tenants</h3>
                        <p class="text-slate-600 text-sm">Edit or remove your current tenants.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="landlordApplications.php" class="text-sm text-purple-600 hover:underline font-medium">
                        View Applications
                    </a>
                    <a href="dashboard/landlord_dashboard.php" class="text-sm text-blue-600 hover:underline font-medium">
                        &larr; Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if (!empty($tenants)): ?>
            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tenantsTable">
                    <thead>
                        <tr class="border-b-2 border-slate-200 bg-slate-50">
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Tenant Name</th>
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Phone</th>
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Status</th>
                            <th class="text-center py-4 px-4 font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tenantsTableBody">
                    <?php foreach ($tenants as $tenant): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors duration-150">
                            <td class="py-4 px-4">
                                <p class="font-semibold text-slate-800"><?= htmlspecialchars($tenant['full_name']); ?></p>
                                <p class="text-xs text-slate-500">Tenant ID: <?= htmlspecialchars($tenant['user_id']); ?></p>
                            </td>
                            <td class="py-4 px-4 text-slate-700"><?= htmlspecialchars($tenant['phone_no']); ?></td>
                            <td class="py-4 px-4">
                                <?php
                                    $isOnActiveLease = in_array($tenant['user_id'], $activeLeaseTenantIds);
                                    if ($tenant['status'] === 'approved' && $isOnActiveLease) {
                                        $statusText = 'Active';
                                        $statusClass = 'bg-green-100 text-green-700';
                                    } elseif ($tenant['status'] === 'approved') {
                                        $statusText = 'Approved (Idle)';
                                        $statusClass = 'bg-yellow-100 text-yellow-700';
                                    } else {
                                        $statusText = ucfirst($tenant['status']);
                                        $statusClass = 'bg-gray-100 text-gray-600';
                                    }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <!-- This links to the new edit_tenant.php page -->
                                    <a href="edittenant.php?id=<?= $tenant['user_id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg transition">
                                        Edit
                                    </a>
                                    <!-- This uses the new delete handler -->
                                    <button onclick="confirmDelete(<?= $tenant['user_id']; ?>, '<?= htmlspecialchars(addslashes($tenant['full_name']), ENT_QUOTES); ?>')" class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                     <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5" />
                      <circle cx="12" cy="7" r="4" stroke-width="2" />
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-slate-800 mb-2">No tenants found</h4>
                <p class="text-slate-500">You have not approved any tenant applications yet.</p>
                 <a href="landlord_applications.php" class="mt-4 inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    View Applications
                 </a>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function confirmDelete(userId, tenantName) {
            Swal.fire({
                title: 'Delete Tenant?',
                html: `Are you sure you want to permanently delete <strong>${tenantName}</strong>?<br/><br/><strong class='text-red-600'>This will delete all their info and files. This action cannot be undone.</strong><br/><br/><span class='text-sm text-gray-500'>Note: You cannot delete a tenant on an active lease.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete tenant'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Links to the new handler in the parent PHP/ folder
                    window.location.href = `tenantUpdate.php?id=${userId}`;
                }
            });
        }
    </script>
    <script>
        <?php if ($landlordSuccess): ?>
            Swal.fire({ icon: 'success', title: 'Success!', text: <?php echo json_encode($landlordSuccess); ?>, timer: 2000, showConfirmButton: false });
        <?php elseif ($landlordError): ?>
            Swal.fire({ icon: 'error', title: 'Error!', text: <?php echo json_encode($landlordError); ?> });
        <?php endif; ?>
    </script>
</body>
</html>