<?php
session_start();
require_once "dbConnect.php";

// 1. Check Admin Auth
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['admin_error'] = "Unauthorized action.";
    header("Location: dashboard/admin_dashboard.php");
    exit;
}

// 2. Check for POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard/admin_dashboard.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// 3. Get User ID and redirect path
$userId = (int)($_POST['user_id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['admin_error'] = "Invalid landlord ID.";
    header("Location: dashboard/admin_dashboard.php");
    exit;
}
$redirectUrl = "manageLandlord.php?user_id=" . $userId;

// --- Helper File Upload Function ---
/**
 * Handles a file upload, deletes the old file, and returns the new database path.
 */
function handleFileUpload(array $file, int $userId, string $fieldName, PDO $db): ?string {
    // Check for upload error
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // No new file uploaded, this is fine
        }
        throw new Exception("File upload error for {$fieldName}: " . $file['error']);
    }

    // --- New file was uploaded, process it ---

    $uploadDir = "../uploads/landlord_docs/user_{$userId}/";
    $dbDir = "uploads/landlord_docs/user_{$userId}/";

    // 1. Get old file path from DB
    $stmt = $db->prepare("SELECT $fieldName FROM landlord_info_tbl WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $oldFilePath = $stmt->fetchColumn();

    // 2. Delete old file from server
    if ($oldFilePath && file_exists("../" . $oldFilePath)) {
        @unlink("../" . $oldFilePath);
    }

    // 3. Create new directory if needed
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory.");
        }
    }

    // 4. Save new file
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = $fieldName . '_' . uniqid() . '.' . $extension;
    $serverPath = $uploadDir . $uniqueName;
    $databasePath = $dbDir . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $serverPath)) {
        throw new Exception("Failed to move uploaded file for {$fieldName}.");
    }

    return $databasePath;
}
// --- End Helper Function ---


try {
    $db->beginTransaction();

    // --- 1. Update user_tbl (Account Details) ---
    $fullName = $_POST['full_name'] ?? '';
    $phone = preg_replace('/[^+0-9]/', '', trim($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? null;

    if (empty($fullName) || empty($phone)) {
        throw new Exception("Full Name and Phone Number are required.");
    }

    if (!empty($password)) {
        // Update with new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE user_tbl SET full_name = :name, phone_no = :phone, password = :pass WHERE user_id = :id");
        $stmt->execute([':name' => $fullName, ':phone' => $phone, ':pass' => $hashedPassword, ':id' => $userId]);
    } else {
        // Update without changing password
        $stmt = $db->prepare("UPDATE user_tbl SET full_name = :name, phone_no = :phone WHERE user_id = :id");
        $stmt->execute([':name' => $fullName, ':phone' => $phone, ':id' => $userId]);
    }

    // --- 2. Update landlord_info_tbl (Profile & Files) ---
    
    // Handle all file uploads first
    $fileFields = [
        'land_title', 'building_permit', 'business_permit', 'mayors_permit',
        'fire_safety_permit', 'barangay_cert', 'occupancy_permit',
        'sanitary_permit', 'dti_permit'
    ];
    $paths = [];
    foreach ($fileFields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE) {
            $paths[$field] = handleFileUpload($_FILES[$field], $userId, $field, $db);
        }
    }

    // Build the SET part of the query
    $setClauses = [];
    $params = [':user_id' => $userId];

    // Add text fields
    $params[':age'] = $_POST['age'] ?: null;
    $params[':occupation'] = $_POST['occupation'] ?: null;
    $params[':address'] = $_POST['address'] ?: null;
    $setClauses[] = "age = :age";
    $setClauses[] = "occupation = :occupation";
    $setClauses[] = "address = :address";

    // Add file fields (only if a new file was uploaded)
    foreach ($fileFields as $field) {
        if (!empty($paths[$field])) {
            $setClauses[] = "$field = :$field";
            $params[":$field"] = $paths[$field];
        }
    }

    // Execute the update query
    if (!empty($setClauses)) {
        $sql = "UPDATE landlord_info_tbl SET " . implode(", ", $setClauses) . " WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    }

    // Commit changes
    $db->commit();
    $_SESSION['admin_success'] = "Landlord profile updated successfully!";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['admin_error'] = "Error: " . $e->getMessage();
}

// Redirect back to the edit page
header("Location: $redirectUrl");
exit;