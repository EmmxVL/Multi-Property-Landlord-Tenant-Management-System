<?php
session_start();
require_once "dbConnect.php";

// 1. Check Admin Auth
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login_page_admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// 2. Get User ID from URL
$userId = (int)($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['admin_error'] = "Invalid landlord ID.";
    header("Location: admin_dashboard.php");
    exit;
}

// 3. Fetch All Landlord Data (from user_tbl and landlord_info_tbl)
try {
    $stmt = $db->prepare("
        SELECT u.full_name, u.phone_no, li.*
        FROM user_tbl u
        LEFT JOIN landlord_info_tbl li ON u.user_id = li.user_id
        WHERE u.user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $landlord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$landlord) {
        throw new Exception("Landlord not found.");
    }

} catch (Exception $e) {
    $_SESSION['admin_error'] = $e->getMessage();
    header("Location: admin_dashboard.php");
    exit;
}

// Get session messages
$adminSuccess = $_SESSION['admin_success'] ?? null;
$adminError  = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

// Helper function to render file inputs
function renderFileInput($name, $label, $currentValue, $userId) {
    echo '<div>';
    echo '<label class="block text-sm font-medium text-slate-700 mb-1">' . htmlspecialchars($label) . '</label>';
    if (!empty($currentValue)) {
        echo '<div class="flex items-center justify-between">';
        echo '<a href="../../' . htmlspecialchars($currentValue) . '" target="_blank" class="text-sm text-blue-600 hover:underline">View Current File</a>';
        echo '<span class="text-xs text-slate-500">Upload to replace</span>';
        echo '</div>';
    } else {
        echo '<span class="text-sm text-slate-500">No file uploaded.</span>';
    }
    echo '<input type="file" name="' . $name . '" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mt-1"/>';
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unitly Admin - Edit Landlord</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../../assets/styles.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
    
    <main class="flex-grow max-w-4xl mx-auto px-6 py-8 w-full">
        
        <form action="landlordManager.php" method="POST" enctype="multipart/form-data" 
              class="bg-white rounded-xl shadow-sm border border-slate-200">

            <input type="hidden" name="user_id" value="<?= $userId ?>">

            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800">Edit Landlord Profile</h3>
                            <p class="text-slate-600 text-sm">Updating account for: <?= htmlspecialchars($landlord['full_name']) ?></p>
                        </div>
                    </div>
                    <a href="dashboard/admin_dashboard.php" class="text-sm text-blue-600 hover:underline">
                        &larr; Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Form Sections -->
            <div class="p-6 space-y-6">
                
                <!-- Section 1: Account Details -->
                <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Account Details</legend>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($landlord['full_name']) ?>" required
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($landlord['phone_no']) ?>" required
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-medium text-slate-700 mb-1">New Password (Optional)</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current password"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </fieldset>

                <!-- Section 2: Personal Information -->
                <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                    <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Personal Information</legend>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
                        <input type="number" name="age" value="<?= htmlspecialchars($landlord['age'] ?? '') ?>"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Occupation</label>
                        <input type="text" name="occupation" value="<?= htmlspecialchars($landlord['occupation'] ?? '') ?>"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($landlord['address'] ?? '') ?>"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </fieldset>

                <!-- Section 3: Uploaded Documents -->
                <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                    <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Uploaded Documents</legend>
                    
                    <?php renderFileInput('land_title', 'Land Title', $landlord['land_title'] ?? null, $userId); ?>
                    <?php renderFileInput('building_permit', 'Building Permit', $landlord['building_permit'] ?? null, $userId); ?>
                    <?php renderFileInput('business_permit', 'Business Permit', $landlord['business_permit'] ?? null, $userId); ?>
                    <?php renderFileInput('mayors_permit', 'Mayor\'s Permit', $landlord['mayors_permit'] ?? null, $userId); ?>
                    <?php renderFileInput('fire_safety_permit', 'Fire Safety Permit', $landlord['fire_safety_permit'] ?? null, $userId); ?>
                    <?php renderFileInput('barangay_cert', 'Barangay Certificate', $landlord['barangay_cert'] ?? null, $userId); ?>
                    <?php renderFileInput('occupancy_permit', 'Occupancy Permit', $landlord['occupancy_permit'] ?? null, $userId); ?>
                    <?php renderFileInput('sanitary_permit', 'Sanitary Permit', $landlord['sanitary_permit'] ?? null, $userId); ?>
                    <?php renderFileInput('dti_permit', 'DTI Permit', $landlord['dti_permit'] ?? null, $userId); ?>
                    
                </fieldset>

            </div>

            <!-- Actions -->
            <div class="p-6 bg-gray-50 rounded-b-xl border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <a href="dashboard/admin_dashboard.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">
                        Save Changes
                    </button>
                </div>
            </div>

        </form>
    </main>


    <script>
        <?php if ($adminSuccess): ?>
            Swal.fire({ icon: 'success', title: 'Success!', text: <?php echo json_encode($adminSuccess); ?>, timer: 2000, showConfirmButton: false });
        <?php elseif ($adminError): ?>
            Swal.fire({ icon: 'error', title: 'Error!', text: <?php echo json_encode($adminError); ?> });
        <?php endif; ?>
    </script>
</body>
</html>