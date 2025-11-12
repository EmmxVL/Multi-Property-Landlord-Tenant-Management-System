<?php
session_start();
require_once "dbConnect.php";
require_once "tenantManager.php"; // Uses the merged TenantManager class

// 1. Check Landlord Auth
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../../login_page_user.php");
    exit;
}

$landlordId = (int)$_SESSION['user_id'];
$tenantUserId = (int)($_GET['id'] ?? 0);
if ($tenantUserId <= 0) {
    $_SESSION['landlord_error'] = 'Invalid tenant ID.';
    header("Location: manageTenants.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$tenantManager = new TenantManager($db, $landlordId);

try {
    // Fetch tenant data
    $tenant = $tenantManager->getSingleTenantDetails($tenantUserId);
    if (!$tenant) {
        throw new Exception("Tenant not found or you are not authorized to view them.");
    }
} catch (Exception $e) {
    $_SESSION['landlord_error'] = $e->getMessage();
    header("Location: manageTenants.php");
    exit;
}

// Get session messages
$landlordSuccess = $_SESSION['landlord_success'] ?? null;
$landlordError  = $_SESSION['landlord_error'] ?? null;
unset($_SESSION['landlord_success'], $_SESSION['landlord_error']);

// Helper function for file input rendering
function renderFileInput($name, $label, $currentValue) {
    echo '<div>';
    echo '<label class="block text-sm font-medium text-slate-700 mb-1">' . htmlspecialchars($label) . '</label>';
    echo '<input type="hidden" name="existing_' . $name . '" value="' . htmlspecialchars($currentValue ?? '') . '">';
    if (!empty($currentValue)) {
        echo '<div class="flex items-center justify-between mb-1">';
        echo '<a href="../../' . htmlspecialchars($currentValue) . '" target="_blank" class="text-sm text-blue-600 hover:underline">View Current File</a>';
        echo '<span class="text-xs text-slate-500">Upload to replace</span>';
        echo '</div>';
    } else {
        echo '<span class="text-sm text-slate-500 mb-1">No file uploaded.</span>';
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
<title>Unitly - Edit Tenant Profile</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="../../assets/styles.css">
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

<main class="flex-grow max-w-4xl mx-auto px-6 py-8 w-full">
    <form action="tenantUpdate.php" method="POST" enctype="multipart/form-data" 
          class="bg-white rounded-xl shadow-sm border border-slate-200">

        <input type="hidden" name="user_id" value="<?= $tenantUserId ?>">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-slate-800">Edit Tenant Profile</h3>
                    <p class="text-slate-600 text-sm">Updating account for: <?= htmlspecialchars($tenant['full_name']) ?></p>
                </div>
            </div>
            <a href="manageTenants.php" class="text-sm text-blue-600 hover:underline">&larr; Back to Tenant List</a>
        </div>

        <!-- Form Sections -->
        <div class="p-6 space-y-6">
            <!-- Account Details -->
            <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Account Details</legend>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($tenant['full_name']) ?>" required
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                    <input type="tel" name="phone_no" value="<?= htmlspecialchars($tenant['phone_no']) ?>" required
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="col-span-full">
                    <label class="block text-sm font-medium text-slate-700 mb-1">New Password (Optional)</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </fieldset>

            <!-- Personal Information -->
            <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Personal Information</legend>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Birthdate</label>
                    <input type="date" name="birthdate" value="<?= htmlspecialchars($tenant['birthdate'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                     <select name="gender" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Select gender</option>
                        <option value="Male" <?= ($tenant['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($tenant['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($tenant['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-span-full">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($tenant['email'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </fieldset>

            <!-- Identification -->
            <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Identification</legend>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ID Type</label>
                    <input type="text" name="id_type" value="<?= htmlspecialchars($tenant['id_type'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">ID Number</label>
                    <input type="text" name="id_number" value="<?= htmlspecialchars($tenant['id_number'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </fieldset>

            <!-- Employment & Rent -->
            <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Employment & Rent</legend>
                 <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Occupation</label>
                    <input type="text" name="occupation" value="<?= htmlspecialchars($tenant['occupation'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Employer Name</label>
                    <input type="text" name="employer_name" value="<?= htmlspecialchars($tenant['employer_name'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Income (PHP)</label>
                    <input type="number" name="monthly_income" step="0.01" value="<?= htmlspecialchars($tenant['monthly_income'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Rent (PHP)</label>
                    <input type="number" name="monthly_rent" step="0.01" value="<?= htmlspecialchars($tenant['monthly_rent'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </fieldset>

            <!-- Emergency Contact -->
            <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Emergency Contact</legend>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Name</label>
                    <input type="text" name="emergency_name" value="<?= htmlspecialchars($tenant['emergency_name'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_contact" value="<?= htmlspecialchars($tenant['emergency_contact'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="col-span-full">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact Relationship</label>
                    <input type="text" name="relationship" value="<?= htmlspecialchars($tenant['relationship'] ?? '') ?>"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </fieldset>

            <!-- Uploaded Documents -->
            <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-6">
                <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">Uploaded Documents</legend>
                <?php
                    renderFileInput('tenant_id_photo', 'Photo of ID', $tenant['id_photo'] ?? null);
                    renderFileInput('birth_certificate', 'Birth Certificate', $tenant['birth_certificate'] ?? null);
                    renderFileInput('tenant_photo', 'Tenant Profile Photo', $tenant['tenant_photo'] ?? null);
                    renderFileInput('proof_of_income', 'Proof of Income', $tenant['proof_of_income'] ?? null);
                ?>
            </fieldset>
        </div>

        <!-- Actions -->
        <div class="p-6 bg-gray-50 rounded-b-xl border-t border-gray-200 flex justify-end space-x-3">
            <a href="manageTenants.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors font-medium">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">Save Changes</button>
        </div>
    </form>
</main>

<script>
<?php if ($landlordSuccess): ?>
    Swal.fire({ icon: 'success', title: 'Success!', text: <?= json_encode($landlordSuccess) ?>, timer: 2000, showConfirmButton: false });
<?php elseif ($landlordError): ?>
    Swal.fire({ icon: 'error', title: 'Error!', text: <?= json_encode($landlordError) ?> });
<?php endif; ?>
</script>

</body>
</html>
