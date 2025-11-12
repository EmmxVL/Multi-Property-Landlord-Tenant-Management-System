<?php
session_start();

// 1. Check Landlord Auth
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../../login_page_user.php");
    exit;
}

$landlordId = (int)$_SESSION['user_id']; // Get the logged-in landlord's ID

require_once "dbConnect.php";
$database = new Database();
$db = $database->getConnection();

// Get session messages
$landlordSuccess = $_SESSION['landlord_success'] ?? null;
$landlordError  = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);

try {
    // Selects pending tenants (role 2) who have requested a unit (ti.requested_unit_id)
    // that belongs to a property (p.property_id) owned by the current landlord (p.user_id).
    $stmt = $db->prepare("
        SELECT 
            u.user_id, 
            u.full_name, 
            u.phone_no, 
            u.created_at, 
            ut.unit_name,
            p.property_name
        FROM user_tbl u
        JOIN user_role_tbl ur ON u.user_id = ur.user_id
        JOIN tenant_info_tbl ti ON u.user_id = ti.user_id
        JOIN unit_tbl ut ON ti.requested_unit_id = ut.unit_id
        JOIN property_tbl p ON ut.property_id = p.property_id
        WHERE u.status = 'pending'
          AND ur.role_id = 2
          AND p.user_id = :landlord_id
        ORDER BY u.created_at ASC
    ");
    $stmt->execute([':landlord_id' => $landlordId]); // Bind the landlord's ID
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $applications = [];
    $landlordError = "Error fetching applications: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unitly - Manage Tenant Applications</title>
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
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-slate-800">Tenant Applications</h3>
                        <p class="text-slate-600 text-sm">Approve or reject applications for your units.</p>
                    </div>
                </div>
                <a href="dashboard/landlord_dashboard.php" class="text-sm text-blue-600 hover:underline">
                    &larr; Back to Dashboard
                </a>
            </div>

            <?php if (!empty($applications)): ?>
            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="applicationsTable">
                    <thead>
                        <tr class="border-b-2 border-slate-200 bg-slate-50">
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Applicant Name</th>
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Requested Unit</th>
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Phone</th>
                            <th class="text-left py-4 px-4 font-semibold text-slate-700">Date Applied</th>
                            <th class="text-center py-4 px-4 font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="applicationsTableBody">
                    <?php foreach ($applications as $app): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors duration-150">
                            <td class="py-4 px-4 font-semibold text-slate-800"><?= htmlspecialchars($app['full_name']); ?></td>
                            <td class="py-4 px-4 text-slate-700">
                                <span class="font-medium"><?= htmlspecialchars($app['unit_name']); ?></span>
                                <span class="text-xs text-slate-500">(<?= htmlspecialchars($app['property_name']); ?>)</span>
                            </td>
                            <td class="py-4 px-4 text-slate-700"><?= htmlspecialchars($app['phone_no']); ?></td>
                            <td class="py-4 px-4 text-slate-700"><?= date("M d, Y", strtotime($app['created_at'])); ?></td>
                            <td class="py-4 px-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="viewTenantApplication.php?id=<?= $app['user_id']; ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg transition">
                                        View & Approve
                                    </a>
                                    <button onclick="confirmAction(<?= $app['user_id']; ?>, 'reject')" class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition">
                                        Reject
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
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-slate-800 mb-2">No pending applications</h4>
                <p class="text-slate-500">New tenant applications for your units will appear here.</p>
            </div>
            <?php endif; ?>
        </section>
    </main>


    <script>
        // This function is now only for REJECTING
        function confirmAction(userId, action) {
            if (action !== 'reject') return; // Only handle reject
            
            Swal.fire({
                title: 'Reject Application?',
                text: 'This will permanently delete the application and all associated data. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, reject it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Links to the new handler in the parent PHP/ folder
                    window.location.href = `tenantApplicationHandler.php?id=${userId}&action=reject`;
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