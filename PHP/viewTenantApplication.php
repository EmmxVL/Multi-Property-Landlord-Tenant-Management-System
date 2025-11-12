<?php
session_start();

// 1. Check Landlord Auth
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../../login_page_user.php");
    exit;
}

$landlordId = (int)$_SESSION['user_id'];
$tenantUserId = (int)($_GET['id'] ?? 0);
if ($tenantUserId <= 0) {
    $_SESSION['landlord_error'] = 'Invalid application ID.';
    header("Location: landlordApplications.php");
    exit;
}

require_once "dbConnect.php";
$database = new Database();
$db = $database->getConnection();

try {
    // 2. Fetch User, Role, and Requested Unit
    // This query ensures the landlord can ONLY see applications for units they own.
    $stmt = $db->prepare("
        SELECT 
            u.user_id, u.full_name, u.phone_no, u.created_at,
            ti.*, -- Get all columns from tenant_info_tbl
            ut.unit_id, ut.unit_name,
            p.property_name
        FROM user_tbl u
        JOIN user_role_tbl ur ON u.user_id = ur.user_id
        JOIN tenant_info_tbl ti ON u.user_id = ti.user_id
        JOIN unit_tbl ut ON ti.requested_unit_id = ut.unit_id
        JOIN property_tbl p ON ut.property_id = p.property_id
        WHERE u.user_id = :tenant_user_id 
          AND u.status = 'pending'
          AND ur.role_id = 2
          AND p.user_id = :landlord_id
    ");
    $stmt->execute([
        ':tenant_user_id' => $tenantUserId,
        ':landlord_id' => $landlordId
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Application not found or you are not authorized to view it.");
    }
    
    // We already have all the details from the join
    $details = $user;

} catch (Exception $e) {
    $_SESSION['landlord_error'] = $e->getMessage();
    header("Location: landlordApplications.php");
    exit;
}

// Helper function to render detail rows
function renderDetail($label, $value, $isLink = false) {
    if (empty($value) && $value !== '0') return; // Don't show empty fields, but show '0'

    echo '<div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">';
    echo '<dt class="text-sm font-medium text-gray-500">' . htmlspecialchars($label) . '</dt>';
    if ($isLink) {
        echo '<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">';
        echo '<a href="../../' . htmlspecialchars($value) . '" target="_blank" class="text-blue-600 hover:underline">View Document</a>';
        echo '</dd>';
    } else {
        echo '<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">' . htmlspecialchars($value) . '</dd>';
    }
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unitly - View Tenant Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    
    <main class="flex-grow max-w-4xl mx-auto px-6 py-8 w-full">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800"><?= htmlspecialchars($user['full_name']) ?></h3>
                            <p class="text-slate-600 text-sm">Application for Tenant Role</p>
                        </div>
                    </div>
                    <a href="landlordApplications.php" class="text-sm text-blue-600 hover:underline">
                        &larr; Back to All Applications
                    </a>
                </div>
            </div>

            <!-- Details -->
            <dl class="divide-y divide-gray-200">
                <?php renderDetail('Full Name', $user['full_name']); ?>
                <?php renderDetail('Phone Number', $user['phone_no']); ?>
                <?php renderDetail('Date Applied', date("M d, Y h:i A", strtotime($user['created_at']))); ?>
                
                <!-- *** NEW: Show requested unit *** -->
                <div class="bg-blue-50 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-bold text-blue-800">Requested Unit</dt>
                    <dd class="mt-1 text-sm text-blue-900 sm:mt-0 sm:col-span-2 font-semibold">
                        <?= htmlspecialchars($user['unit_name'] . ' (' . $user['property_name'] . ')') ?>
                    </dd>
                </div>

                <!-- Role Specific Details -->
                <div class="py-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 mb-2">Tenant Details</dt>
                </div>

                <?php if ($details): ?>
                    <?php renderDetail('Birthdate', $details['birthdate']); ?>
                    <?php renderDetail('Age', $details['age']); ?>
                    <?php renderDetail('Gender', $details['gender']); ?>
                    <?php renderDetail('Email', $details['email']); ?>
                    <?php renderDetail('Tenant Photo', $details['tenant_photo'], true); ?>
                    <?php renderDetail('ID Type', $details['id_type']); ?>
                    <?php renderDetail('ID Number', $details['id_number']); ?>
                    <?php renderDetail('ID Photo', $details['id_photo'], true); ?>
                    <?php renderDetail('Birth Certificate', $details['birth_certificate'], true); ?>
                    <?php renderDetail('Occupation', $details['occupation']); ?>
                    <?php renderDetail('Employer Name', $details['employer_name']); ?>
                    <?php renderDetail('Monthly Income', $details['monthly_income']); ?>
                    <?php renderDetail('Proof of Income', $details['proof_of_income'], true); ?>
                    <?php renderDetail('Expected Monthly Rent', $details['monthly_rent']); ?>
                    <?php renderDetail('Emergency Name', $details['emergency_name']); ?>
                    <?php renderDetail('Emergency Contact', $details['emergency_contact']); ?>
                    <?php renderDetail('Relationship', $details['relationship']); ?>
                <?php endif; ?>
            </dl>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 rounded-b-xl border-t border-gray-200">
                
                <!-- *** NEW: Approve & Create Lease Form *** -->
                <form action="tenantApplicationHandler.php" method="POST">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="user_id" value="<?= $tenantUserId ?>">
                    <input type="hidden" name="unit_id" value="<?= $user['unit_id'] ?>">

                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Approve & Create Lease</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Lease Start Date</label>
                            <input type="date" name="lease_start_date" required
                                   class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Rent Amount (PHP)</label>
                            <input type="number" name="monthly_rent" min="0" step="0.01" placeholder="e.g., 5000" required
                                   value="<?= htmlspecialchars($details['monthly_rent'] ?? '0') ?>"
                                   class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Initial Payment (PHP)</label>
                            <input type="number" name="initial_payment" min="0" step="0.01" placeholder="0"
                                   class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mb-4 -mt-2">
                        Enter the advance or deposit paid by the tenant. This will be logged as their first 'Confirmed' payment.
                    </p>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="confirmReject(<?= $tenantUserId; ?>)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                            Reject Application
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                            Approve & Create Lease
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function confirmReject(userId) {
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
</body>
</html>