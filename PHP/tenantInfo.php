<?php
session_start();
require_once "dbConnect.php";
require_once "TenantInfoManager.php";

// Restrict access to tenants
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Tenant" || !isset($_SESSION['user_id'])) {
    header("Location: ../../login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$tenantManager = new TenantInfoManager($db);
$userId = (int) $_SESSION['user_id'];

// Fetch existing tenant info
$tenantInfo = $tenantManager->getTenantInfo($userId);

// Handle file deletion
if (isset($_GET['delete_file'])) {
    $fileField = $_GET['delete_file'];

    if (!empty($tenantInfo[$fileField]) && file_exists($tenantInfo[$fileField])) {
        unlink($tenantInfo[$fileField]); // Delete the file
    }

    // Update database
    $stmt = $db->prepare("UPDATE tenant_info_tbl SET $fileField = NULL WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);

    // Refresh tenant info
    $tenantInfo = $tenantManager->getTenantInfo($userId);
}

// Handle form submission
$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $data = $_POST;
    $files = $_FILES;

    // Add user_id and existing file paths to data array
    $data['user_id'] = $userId;
    $data['existing_id_photo'] = $tenantInfo['id_photo'] ?? null;
    $data['existing_birth_certificate'] = $tenantInfo['birth_certificate'] ?? null;
    $data['existing_tenant_photo'] = $tenantInfo['tenant_photo'] ?? null;
    $data['existing_proof_of_income'] = $tenantInfo['proof_of_income'] ?? null;

    if ($tenantInfo) {
        $updated = $tenantManager->updateTenantInfo($userId, $data, $files);
        $success = $updated ? "Tenant information updated successfully." : "Failed to update tenant info.";
    } else {
        $created = $tenantManager->createTenantInfo($data, $files);
        $success = $created ? "Tenant information saved successfully." : "Failed to save tenant info.";
    }

    // Refresh info after saving
    $tenantInfo = $tenantManager->getTenantInfo($userId);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tenant Info</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 min-h-screen p-6 font-sans">

<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg">
    <h1 class="text-2xl font-bold mb-6">My Information</h1>

    <?php if($success): ?>
        <script>
            Swal.fire({icon:'success', title:'Success', text:<?= json_encode($success) ?>});
        </script>
    <?php elseif($error): ?>
        <script>
            Swal.fire({icon:'error', title:'Error', text:<?= json_encode($error) ?>});
        </script>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

        <!-- Basic Info -->
        <div>
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($tenantInfo['full_name'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label>Birthdate</label>
            <input type="date" name="birthdate" value="<?= htmlspecialchars($tenantInfo['birthdate'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label>Gender</label>
            <select name="gender" class="w-full border p-2 rounded">
                <option value="">Select Gender</option>
                <option value="Male" <?= (isset($tenantInfo['gender']) && $tenantInfo['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (isset($tenantInfo['gender']) && $tenantInfo['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
            </select>
        </div>

        <div>
            <label>Contact Number</label>
            <input type="text" name="phone_no" value="<?= htmlspecialchars($tenantInfo['phone_no'] ?? '') ?>" class="w-full border p-2 rounded" maxlength="11"   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);">
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($tenantInfo['email'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <!-- Occupation Info -->
        <div>
            <label>Occupation</label>
            <input type="text" name="occupation" value="<?= htmlspecialchars($tenantInfo['occupation'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label>Employer Name</label>
            <input type="text" name="employer_name" value="<?= htmlspecialchars($tenantInfo['employer_name'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label>Monthly Income</label>
            <input type="number" step="0.01" name="monthly_income" value="<?= htmlspecialchars($tenantInfo['monthly_income'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <!-- Emergency Contact -->
        <div>
            <label>Emergency Contact Name</label>
            <input type="text" name="emergency_name" value="<?= htmlspecialchars($tenantInfo['emergency_name'] ?? '') ?>" class="w-full border p-2 rounded" >
        </div>

        <div>
            <label>Relationship</label>
            <input type="text" name="relationship" value="<?= htmlspecialchars($tenantInfo['relationship'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label>Emergency Contact Number</label>
            <input type="text" name="emergency_contact" value="<?= htmlspecialchars($tenantInfo['emergency_contact'] ?? '') ?>" class="w-full border p-2 rounded" maxlength="11"  oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);">
        </div>

        <!-- File Uploads with Delete Button -->
        <?php
    $fileFields = [
        'id_photo' => 'ID Photo',
        'birth_certificate' => 'Birth Certificate',
        'tenant_photo' => 'Tenant Photo',
        'proof_of_income' => 'Proof of Income'
    ];

    foreach ($fileFields as $field => $label):
    ?>
        <div>
            <label><?= $label ?></label>
            <?php if(!empty($tenantInfo[$field])): ?>
                <div class="mb-2 flex items-center gap-2">
                    <!-- Clickable image -->
                    <a href="<?= htmlspecialchars($tenantInfo[$field]) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($tenantInfo[$field]) ?>" width="100" alt="<?= $label ?>" class="border rounded">
                    </a>
                    <a href="?delete_file=<?= $field ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Delete</a>
                </div>
            <?php endif; ?>
            <input type="file" name="<?= $field ?>" class="w-full">
        </div>
    <?php endforeach; ?>


        <!-- Form Actions -->
        <div class="flex justify-between mt-6">
            <a href="dashboard/tenant_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">Back to Dashboard</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">Save Information</button>
        </div>
    </form>
</div>

</body>
</html>
