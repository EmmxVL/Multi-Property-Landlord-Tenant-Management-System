<?php
session_start();
// --- Add error display for debugging ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";

$database = new Database();
$db = $database->getConnection();

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/**
 * Helper function to send a consistent error response.
 * This works for both your standard form (using Sessions)
 * and an AJAX form (using JSON).
 */
function sendErrorResponse($message, $isAjax, $location = '../login_page.php') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    } else {
        $_SESSION['login_error'] = $message;
        header("Location: $location");
    }
    exit;
}

// --- 1. Check Request Method ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // direct GET -> go back to frontend
    header("Location: ../login_page.php");
    exit;
}

// --- 2. Get and Validate Inputs ---
$phone = trim($_POST["phone"] ?? '');
$password = trim($_POST["password"] ?? '');

if ($phone === '' || $password === '') {
    sendErrorResponse('Phone and password are required.', $isAjax);
}

// --- 3. Normalize Phone Number (Handles '09...' vs '+63...') ---
// This assumes your database stores numbers in the '09...' format.
$normalizedPhone = $phone;
// Remove any non-numeric characters except '+'
$normalizedPhone = preg_replace('/[^+0-9]/', '', $normalizedPhone);
// Convert '+639...' to '09...'
if (strpos($normalizedPhone, '+63') === 0) {
    $normalizedPhone = '0' . substr($normalizedPhone, 3);
}
// Convert '9...' to '09...' (if it's 10 digits starting with 9)
if (strlen($normalizedPhone) === 10 && strpos($normalizedPhone, '9') === 0) {
    $normalizedPhone = '0' . $normalizedPhone;
}
// --- End Normalization ---


// --- 4. Main Authentication Logic ---
try {
    // Find user by normalized phone
    $query = "SELECT * FROM user_tbl WHERE phone_no = :phone LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':phone' => $normalizedPhone]);

    if ($stmt->rowCount() !== 1) {
        sendErrorResponse('No account found with that phone number.', $isAjax);
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbPassword = $user['password'] ?? '';

    // --- Secure Password Check + Rehash Logic ---
    $isValid = false;
    $needsRehash = false;

    if (password_verify($password, $dbPassword)) {
        // Valid modern hash
        $isValid = true;
        if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT)) {
            $needsRehash = true;
        }
    } elseif ($password === $dbPassword && !password_get_info($dbPassword)['algo']) {
        // Valid legacy plain-text password
        $isValid = true;
        $needsRehash = true; // Must rehash
    }
    // --- End Password Check ---

    if (!$isValid) {
        sendErrorResponse('Invalid password.', $isAjax);
    }

    // --- (SECURITY) Auto-upgrade plain-text passwords ---
    if ($needsRehash) {
        try {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE user_tbl SET password = :hash WHERE user_id = :id");
            $updateStmt->execute([':hash' => $newHash, ':id' => $user['user_id']]);
        } catch (Exception $e) {
            // Don't stop login, just log this
            // error_log('Password rehash failed for user ' . $user['user_id']);
        }
    }

    // --- 5. Fetch Roles (Using your new script's logic) ---
    // Note: Make sure your `role_tbl` key is `role_id`. If it's `id`, change r.role_id to r.id
    $roleQuery = "SELECT r.role_name FROM user_role_tbl ur
                  JOIN role_tbl r ON ur.role_id = r.role_id 
                  WHERE ur.user_id = :user_id";
    $roleStmt = $db->prepare($roleQuery);
    $roleStmt->execute([':user_id' => $user["user_id"]]);
    $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($roles)) {
        $roles = ['Tenant']; // Default role if none found
    }

    // --- 6. Set Session and Redirect ---
    session_regenerate_id(true); // Prevent session fixation
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["full_name"] = $user["full_name"];

    $redirect = ''; // Initialize redirect path

    // Multi-role check
    if (count($roles) > 1) {
        $_SESSION["roles"] = $roles;
        $redirect = '../role_selection.php'; // Path relative to login.php
    } else {
        // Single role
        $_SESSION["role"] = $roles[0];
        
        // --- FIX: Convert role to lowercase for a case-insensitive check ---
        $roleName = strtolower($roles[0]); 
        
        // Redirect based on the single role
        switch ($roleName) {
            case "admin": // check for lowercase
                $redirect = '../admin_dashboard.php';
                break;
            case "landlord": // check for lowercase
                $redirect = '../landlord_dashboard.php';
                break;
            case "tenant": // check for lowercase
                $redirect = '../tenant_dashboard.php';
                break;
            default:
                // This will now only hit if the role is something else entirely
                sendErrorResponse('Invalid user role configuration: ' . $roles[0], $isAjax);
        }
    }
    
    // --- 7. Send Final Response ---
    if ($isAjax) {
        header('Content-Type: application/json');
        // AJAX expects a path relative to the root or page
        echo json_encode(['success' => true, 'redirect' => ltrim($redirect, './')]);
    } else {
        // Standard form post
        header("Location: $redirect");
    }
    exit;

} catch (Exception $e) {
    // --- Catch-all Error ---
    // Show the real error for debugging
    sendErrorResponse('Server error: ' . $e->getMessage(), $isAjax);
}
?>