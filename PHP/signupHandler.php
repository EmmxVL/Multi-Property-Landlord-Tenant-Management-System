<?php
session_start();
require_once "dbConnect.php"; // Your database connection

// --- Helper Functions ---

/**
 * Saves an uploaded file to a specified directory and returns the database path.
 *
 * @param array $file The $_FILES['input_name'] array.
 * @param string $uploadDir The full server path to upload to (e.g., ../uploads/tenant_docs/user_5/)
 * @param string $dbDir The path to store in the database (e.g., uploads/tenant_docs/user_5/)
 * @param string $fileName The base name for the file (e.g., 'id_photo')
 * @return string|null The database path or null if file is empty/invalid.
 * @throws Exception If file upload fails.
 */
function handleUpload(array $file, string $uploadDir, string $dbDir, string $fileName): ?string {
    // Check if file was uploaded and there are no errors
    if (!isset($file['error']) || is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        // No file uploaded, or an error occurred.
        // If UPLOAD_ERR_NO_FILE, it's not a critical error, just no file.
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // No file uploaded, return null
        }
        throw new Exception("Invalid file upload error for {$fileName}: " . $file['error']);
    }

    // Check file size (e.g., 10MB limit)
    if ($file['size'] > 10000000) {
        throw new Exception("File '{$fileName}' is too large (Max 10MB).");
    }

    // Check file type (allow common images and PDFs)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    
    if (!in_array($mime, $allowedMimes)) {
        throw new Exception("Invalid file type for '{$fileName}'. Only JPG, PNG, GIF, and PDF are allowed.");
    }

    // Create the upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory.");
        }
    }

    // Generate a unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = $fileName . '_' . uniqid() . '.' . $extension;
    $serverPath = $uploadDir . $uniqueName;
    $databasePath = $dbDir . $uniqueName;

    // Move the file
    if (!move_uploaded_file($file['tmp_name'], $serverPath)) {
        throw new Exception("Failed to move uploaded file for '{$fileName}'.");
    }

    return $databasePath;
}

/**
 * Normalizes a Philippine phone number.
 * @param string $phone
 * @return string
 */
function normalizePhone(string $phone): string {
    $phone = preg_replace('/[^+0-9]/', '', trim($phone));
    if (strpos($phone, '+63') === 0) {
        $phone = '0' . substr($phone, 3);
    }
    if (strlen($phone) === 10 && strpos($phone, '9') === 0) {
        $phone = '0' . $phone;
    }
    return $phone;
}

// --- Main Script Logic ---


$errorRedirect = 'signup.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: $errorRedirect");
    exit;
}

// Save POST data to session in case of error
$_SESSION['old_input'] = $_POST;

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Get and Validate Common Data
    $fullName = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($fullName) || empty($phone) || empty($password) || empty($role)) {
        throw new Exception("Please fill out all required fields.");
    }

    if ($password !== $confirmPassword) {
        throw new Exception("Passwords do not match.");
    }
    
    // 2. Check if phone number already exists
    $normalizedPhone = normalizePhone($phone);
    $stmt = $db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = :phone");
    $stmt->execute([':phone' => $normalizedPhone]);
    if ($stmt->fetch()) {
        throw new Exception("A user with this phone number already exists.");
    }

    // 3. Hash Password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 4. Start Database Transaction
    $db->beginTransaction();

    // 5. Insert into user_tbl
    // We set status to 'pending' as per the workflow
    $stmt = $db->prepare("
        INSERT INTO user_tbl (full_name, phone_no, password, status, created_at)
        VALUES (:full_name, :phone, :password, 'pending', CURRENT_TIMESTAMP)
    ");
    $stmt->execute([
        ':full_name' => $fullName,
        ':phone' => $normalizedPhone,
        ':password' => $hashedPassword
    ]);

    $userId = $db->lastInsertId();

    // 6. Insert into user_role_tbl
    $roleId = ($role === 'landlord') ? 1 : 2; // Based on your SQL (1=Landlord, 2=Tenant)
    $stmt = $db->prepare("INSERT INTO user_role_tbl (user_id, role_id) VALUES (:user_id, :role_id)");
    $stmt->execute([':user_id' => $userId, ':role_id' => $roleId]);

    // 7. Handle Role-Specific Data and Uploads
    if ($role === 'landlord') {
        // Define directories
        $uploadDir = "../uploads/landlord_docs/user_{$userId}/";
        $dbDir = "uploads/landlord_docs/user_{$userId}/";
        
        // Handle all file uploads
        $paths = [];
        $fileFields = [
            'land_title', 'building_permit', 'business_permit', 'mayors_permit',
            'fire_safety_permit', 'barangay_cert', 'occupancy_permit',
            'sanitary_permit', 'dti_permit'
        ];
        
        foreach ($fileFields as $field) {
            $paths[$field] = isset($_FILES[$field]) ? handleUpload($_FILES[$field], $uploadDir, $dbDir, $field) : null;
        }

        // Insert into landlord_info_tbl
        $stmt = $db->prepare("
            INSERT INTO landlord_info_tbl (
                user_id, age, address, occupation, land_title, building_permit, 
                business_permit, mayors_permit, fire_safety_permit, barangay_cert, 
                occupancy_permit, sanitary_permit, dti_permit
            ) VALUES (
                :user_id, :age, :address, :occupation, :land_title, :building_permit,
                :business_permit, :mayors_permit, :fire_safety_permit, :barangay_cert,
                :occupancy_permit, :sanitary_permit, :dti_permit
            )
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':age' => $_POST['landlord_age'] ?: null,
            ':address' => $_POST['landlord_address'] ?: null,
            ':occupation' => $_POST['landlord_occupation'] ?: null,
            ':land_title' => $paths['land_title'],
            ':building_permit' => $paths['building_permit'],
            ':business_permit' => $paths['business_permit'],
            ':mayors_permit' => $paths['mayors_permit'],
            ':fire_safety_permit' => $paths['fire_safety_permit'],
            ':barangay_cert' => $paths['barangay_cert'],
            ':occupancy_permit' => $paths['occupancy_permit'],
            ':sanitary_permit' => $paths['sanitary_permit'],
            ':dti_permit' => $paths['dti_permit']
        ]);

    } else { // $role === 'tenant'
        // Define directories
        $uploadDir = "../uploads/tenant_docs/user_{$userId}/";
        $dbDir = "uploads/tenant_docs/user_{$userId}/";

        // Handle all file uploads
        $paths = [];
        $fileFields = ['tenant_id_photo', 'tenant_birth_certificate', 'tenant_photo', 'tenant_proof_of_income'];
        
        foreach ($fileFields as $field) {
            $paths[$field] = isset($_FILES[$field]) ? handleUpload($_FILES[$field], $uploadDir, $dbDir, $field) : null;
        }

        // Calculate age from birthdate if provided
        $age = null;
        if (!empty($_POST['tenant_birthdate'])) {
            try {
                $birthDate = new DateTime($_POST['tenant_birthdate']);
                $today = new DateTime('today');
                $age = $birthDate->diff($today)->y;
            } catch (Exception $e) {
                // Ignore if date is invalid, age will remain null
            }
        }

        // Insert into tenant_info_tbl
        $stmt = $db->prepare("
            INSERT INTO tenant_info_tbl (
                user_id, birthdate, age, gender, email, id_type, id_number, id_photo,
                birth_certificate, tenant_photo, occupation, employer_name, monthly_income,
                proof_of_income, monthly_rent, emergency_name, emergency_contact, relationship
            ) VALUES (
                :user_id, :birthdate, :age, :gender, :email, :id_type, :id_number, :id_photo,
                :birth_certificate, :tenant_photo, :occupation, :employer_name, :monthly_income,
                :proof_of_income, :monthly_rent, :emergency_name, :emergency_contact, :relationship
            )
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':birthdate' => $_POST['tenant_birthdate'] ?: null,
            ':age' => $age,
            ':gender' => $_POST['tenant_gender'] ?: null,
            ':email' => $_POST['tenant_email'] ?: null,
            ':id_type' => $_POST['tenant_id_type'] ?: null,
            ':id_number' => $_POST['tenant_id_number'] ?: null,
            ':id_photo' => $paths['tenant_id_photo'],
            ':birth_certificate' => $paths['tenant_birth_certificate'],
            ':tenant_photo' => $paths['tenant_photo'],
            ':occupation' => $_POST['tenant_occupation'] ?: null,
            ':employer_name' => $_POST['tenant_employer_name'] ?: null,
            ':monthly_income' => $_POST['tenant_monthly_income'] ?: null,
            ':proof_of_income' => $paths['tenant_proof_of_income'],
            ':monthly_rent' => $_POST['tenant_monthly_rent'] ?: null, 
            ':emergency_name' => $_POST['tenant_emergency_name'] ?: null,
            ':emergency_contact' => $_POST['tenant_emergency_contact'] ?: null,
            ':relationship' => $_POST['tenant_relationship'] ?: null
        ]);
    }

    // 8. Commit Transaction
    $db->commit();

    // 9. Success
    unset($_SESSION['old_input']); // Clear saved form data
    $_SESSION['signup_success'] = "Application submitted successfully! Please wait for approval.";
    header("Location: $errorRedirect"); // Redirect back to signup page to show success
    exit;

} catch (Exception $e) {
    // 10. Handle Errors
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['signup_error'] = "Error: " . $e->getMessage();
    header("Location: $errorRedirect");
    exit;
}