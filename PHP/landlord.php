<?php
session_start();
// --- Add error display for debugging ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";

$database = new Database();
$db = $database->getConnection();

/**
 * Helper function to send error response via Session + Redirect.
 */
function sendLandlordError($message, $location = '../landlord_dashboard.php') {
    $_SESSION['landlord_error'] = $message;
    header("Location: $location");
    exit;
}

// --- 1. Check Role and Request Method ---
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php"); // Use login_page.php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Disallow direct GET access
    header("Location: ../landlord_dashboard.php");
    exit;
}

// --- 2. Get and Validate Inputs ---
$fullName = trim($_POST["full_name"] ?? '');
$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');

if (empty($fullName) || empty($phone) || empty($password)) {
    sendLandlordError("All fields (Full Name, Phone, Password) are required.");
}

// --- 3. Normalize Phone Number (Handles '09...' vs '+63...') ---
// Assumes database stores '09...' format. Change if needed.
$normalizedPhone = $phone;
$normalizedPhone = preg_replace('/[^+0-9]/', '', $normalizedPhone);
if (strpos($normalizedPhone, '+63') === 0) {
    $normalizedPhone = '0' . substr($normalizedPhone, 3);
}
if (strlen($normalizedPhone) === 10 && strpos($normalizedPhone, '9') === 0) {
    $normalizedPhone = '0' . $normalizedPhone;
}
// --- End Normalization ---

// --- 4. Main Logic ---
try {
    // Check if phone number already exists
    $checkQuery = "SELECT user_id FROM user_tbl WHERE phone_no = :phone LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([':phone' => $normalizedPhone]); // Use normalized phone

    if ($checkStmt->rowCount() > 0) {
        sendLandlordError("A user with that phone number already exists.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into user_tbl (assuming you might add first/last name later)
    $names = preg_split('/\s+/', $fullName, 2);
    $firstName = $names[0] ?? '';
    $lastName = $names[1] ?? '';

    $insertUser = "INSERT INTO user_tbl (full_name, first_name, last_name, password, phone_no)
                   VALUES (:full_name, :first_name, :last_name, :password, :phone)";
    $stmt = $db->prepare($insertUser);
    $stmt->execute([
        ':full_name' => $fullName,
        ':first_name' => $firstName, // Add if your table has it
        ':last_name' => $lastName,   // Add if your table has it
        ':password' => $hashedPassword,
        ':phone' => $normalizedPhone // Use normalized phone
    ]);

    // Get the last inserted user_id
    $userId = $db->lastInsertId();

    // Assign Tenant role (Verify role_id=3 is Tenant in your role_tbl)
    // Also verify if role_type column exists and is needed.
    $tenantRoleId = 3; // <-- IMPORTANT: Double-check this ID in your role_tbl
    $insertRole = "INSERT INTO user_role_tbl (role_id, user_id) VALUES (:role_id, :user_id)";
    // If you have role_type: INSERT INTO user_role_tbl (role_id, user_id, role_type) VALUES (:role_id, :user_id, '2')
    $stmt2 = $db->prepare($insertRole);
    $stmt2->execute([
        ':role_id' => $tenantRoleId,
        ':user_id' => $userId
        // ':role_type' => '2' // Add this if your table requires it
        ]);

    // --- Send Success Feedback ---
    $_SESSION['landlord_success'] = "Tenant account created successfully!";
    header("Location: ../landlord_dashboard.php");
    exit;

} catch (PDOException $e) {
    // Show detailed error for debugging, make generic in production
    sendLandlordError("Database error: " . $e->getMessage());
} catch (Exception $e) {
    sendLandlordError("Server error: " . $e->getMessage());
}
?>