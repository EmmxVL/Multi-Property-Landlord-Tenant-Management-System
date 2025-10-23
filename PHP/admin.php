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
function sendAdminError($message, $location = '../admin_dashboard.php') {
    $_SESSION['admin_error'] = $message;
    header("Location: $location");
    exit;
}

// --- 1. Check Role and Request Method ---
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../login_page.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin_dashboard.php");
    exit;
}

// --- 2. Get and Validate Inputs ---
$fullName = trim($_POST["full_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$password = trim($_POST["password"] ?? "");

if (empty($fullName) || empty($phone) || empty($password)) {
    sendAdminError("All fields (Full Name, Phone, Password) are required.");
}

// --- 3. Normalize Phone Number ---
// Assumes database stores '09...' format. Adjust if needed.
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
    // Check existing phone using normalized number
    $checkQuery = "SELECT user_id FROM user_tbl WHERE phone_no = :phone LIMIT 1"; // Use user_id if id is ambiguous
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([':phone' => $normalizedPhone]);

    if ($checkStmt->rowCount() > 0) {
        sendAdminError("A user with that phone number already exists.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Try to split full name into first/last
    $names = preg_split('/\s+/', $fullName, 2);
    $firstName = $names[0] ?? '';
    $lastName = $names[1] ?? '';

    // Insert user with normalized phone
    $stmt = $db->prepare("INSERT INTO user_tbl (first_name, last_name, password, phone_no, full_name)
                          VALUES (:first_name, :last_name, :password, :phone, :full_name)");
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name'  => $lastName,
        ':password'   => $hashedPassword,
        ':phone'      => $normalizedPhone, // Store normalized phone
        ':full_name'  => $fullName
    ]);

    $userId = $db->lastInsertId();

    // Assign Landlord role
    // IMPORTANT: Check your role_tbl. What is the actual ID for 'Landlord'? Using 2 as placeholder.
    $landlordRoleId = 2;
    // IMPORTANT: Does user_role_tbl have a role_type column? If not, remove it from query.
    $assignRole = "INSERT INTO user_role_tbl (role_id, user_id) VALUES (:role_id, :user_id)";
    // If you need role_type: INSERT INTO user_role_tbl (role_id, user_id, role_type) VALUES (:role_id, :user_id, '1')
    $rstmt = $db->prepare($assignRole);
    $rstmt->execute([
        ':role_id' => $landlordRoleId,
        ':user_id' => $userId
        // ':role_type' => '1' // Only if needed
        ]);

    $_SESSION['admin_success'] = "Landlord account created successfully.";
    header("Location: ../admin_dashboard.php");
    exit;

} catch (PDOException $e) {
    // Show detailed error for debugging
    sendAdminError("Database error: " . $e->getMessage());
} catch (Exception $e) {
    sendAdminError("Server error: " . $e->getMessage());
}
?>