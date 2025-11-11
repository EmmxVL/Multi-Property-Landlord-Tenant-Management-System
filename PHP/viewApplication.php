<?php
session_start();

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../../login_page_admin.php");
    exit;
}

require_once "dbConnect.php";
$database = new Database();
$db = $database->getConnection();

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['admin_error'] = 'Invalid application ID.';
    header("Location: manageApplications.php");
    exit;
}

try {
    // Get user and role
    $stmt = $db->prepare("
        SELECT u.user_id, u.full_name, u.phone_no, u.created_at, r.role_name
        FROM user_tbl u
        JOIN user_role_tbl ur ON u.user_id = ur.user_id
        JOIN role_tbl r ON ur.role_id = r.role_id
        WHERE u.user_id = :user_id AND u.status = 'pending'
    ");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Application not found or already processed.");
    }

    // Get role-specific details
    $details = [];
    if ($user['role_name'] === 'Landlord') {
        $stmt = $db->prepare("SELECT * FROM landlord_info_tbl WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($user['role_name'] === 'Tenant') {
        $stmt = $db->prepare("SELECT * FROM tenant_info_tbl WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $_SESSION['admin_error'] = $e->getMessage();
    header("Location: manageApplications.php");
    exit;
}

// Helper function to render detail rows
function renderDetail($label, $value, $isLink = false) {
    if (empty($value)) return; // Don't show empty fields

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
    <title>Unitly Admin - View Application</title>
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
                        <div class="w-10 h-10 <?= $user['role_name'] === 'Landlord' ? 'bg-blue-100' : 'bg-orange-100' ?> rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 <?= $user['role_name'] === 'Landlord' ? 'text-blue-600' : 'text-orange-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800"><?= htmlspecialchars($user['full_name']) ?></h3>
                            <p class="text-slate-600 text-sm">Application for <?= htmlspecialchars($user['role_name']) ?> Role</p>
                        </div>
                    </div>
                    <a href="manageApplications.php" class="text-sm text-blue-600 hover:underline">
                        &larr; Back to All Applications
                    </a>
                </div>
            </div>

            <!-- Details -->
            <dl class="divide-y divide-gray-200">
                <?php renderDetail('Full Name', $user['full_name']); ?>
                <?php renderDetail('Phone Number', $user['phone_no']); ?>
                <?php renderDetail('Date Applied', date("M d, Y h:i A", strtotime($user['created_at']))); ?>

                <!-- Role Specific Details -->
                <div class="py-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 mb-2"><?= htmlspecialchars($user['role_name']) ?> Details</dt>
                </div>

                <?php if ($user['role_name'] === 'Landlord' && $details): ?>
                    <?php renderDetail('Age', $details['age']); ?>
                    <?php renderDetail('Address', $details['address']); ?>
                    <?php renderDetail('Occupation', $details['occupation']); ?>
                    <?php renderDetail('Land Title', $details['land_title'], true); ?>
                    <?php renderDetail('Building Permit', $details['building_permit'], true); ?>
                    <?php renderDetail('Business Permit', $details['business_permit'], true); ?>
                    <?php renderDetail('Mayor\'s Permit', $details['mayors_permit'], true); ?>
                    <?php renderDetail('Fire Safety Permit', $details['fire_safety_permit'], true); ?>
                    <?php renderDetail('Barangay Certificate', $details['barangay_cert'], true); ?>
                    <?php renderDetail('Occupancy Permit', $details['occupancy_permit'], true); ?>
                    <?php renderDetail('Sanitary Permit', $details['sanitary_permit'], true); ?>
                    <?php renderDetail('DTI Permit', $details['dti_permit'], true); ?>
                <?php elseif ($user['role_name'] === 'Tenant' && $details): ?>
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
                    <?php renderDetail('Emergency Name', $details['emergency_name']); ?>
                    <?php renderDetail('Emergency Contact', $details['emergency_contact']); ?>
                    <?php renderDetail('Relationship', $details['relationship']); ?>
                <?php endif; ?>
            </dl>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 rounded-b-xl border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <button onclick="confirmAction(<?= $userId; ?>, 'reject')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                        Reject Application
                    </button>
                    <button onclick="confirmAction(<?= $userId; ?>, 'approve')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium">
                        Approve Application
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function confirmAction(userId, action) {
            const title = action === 'approve' ? 'Approve Application?' : 'Reject Application?';
            const text = action === 'approve'
                ? 'This user will be granted access to the system.'
                : 'This will permanently delete the application and all associated data. This action cannot be undone.';
            const confirmButtonColor = action === 'approve' ? '#16a34a' : '#dc2626';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, ' + action + ' it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `handleApplication.php?id=${userId}&action=${action}`;
                }
            });
        }
    </script>
</body>
</html>