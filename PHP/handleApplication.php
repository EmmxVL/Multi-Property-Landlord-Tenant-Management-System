<?php
session_start();
require_once "dbConnect.php";

// Check for admin session
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    $_SESSION['admin_error'] = "Unauthorized action.";
    header("Location: manageApplications.php");
    exit;
}

$userId = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($userId <= 0 || ($action !== 'approve' && $action !== 'reject')) {
    $_SESSION['admin_error'] = "Invalid action or user ID.";
    header("Location: manageApplications.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    if ($action === 'approve') {
        // --- APPROVE ACTION ---
        $stmt = $db->prepare("UPDATE user_tbl SET status = 'approved' WHERE user_id = :user_id AND status = 'pending'");
        $stmt->execute([':user_id' => $userId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['admin_success'] = "Application approved successfully. The user can now log in.";
        } else {
            $_SESSION['admin_error'] = "Could not approve application. It might have been already processed.";
        }

    } elseif ($action === 'reject') {
        // --- REJECT ACTION ---
        // This is more complex: we must delete files, then the database record.

        // 1. Get user role and file paths
        $stmt = $db->prepare("SELECT r.role_name FROM user_role_tbl ur JOIN role_tbl r ON ur.role_id = r.role_id WHERE ur.user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        $files = [];
        $fileDir = '';
        if ($role && $role['role_name'] === 'Landlord') {
            $stmt = $db->prepare("SELECT * FROM landlord_info_tbl WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            $fileDir = "../uploads/landlord_docs/user_{$userId}/";
            if ($info) {
                $files = array_filter(array_values($info), 'is_string'); // Get all string values (paths)
            }
        } elseif ($role && $role['role_name'] === 'Tenant') {
            $stmt = $db->prepare("SELECT * FROM tenant_info_tbl WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            $fileDir = "../uploads/tenant_docs/user_{$userId}/";
            if ($info) {
                $files = array_filter(array_values($info), 'is_string');
            }
        }

        // 2. Delete files
        foreach ($files as $filePath) {
            if (strpos($filePath, 'uploads/') === 0) { // Check if it's an upload path
                $fullPath = "../" . $filePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        // 3. Delete directory
        if (is_dir($fileDir)) {
            @rmdir($fileDir); // @ suppresses error if dir not empty, but it should be
        }

        // 4. Delete user from database (cascading delete will handle the rest)
        $stmt = $db->prepare("DELETE FROM user_tbl WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        
        $_SESSION['admin_success'] = "Application rejected and all data has been permanently deleted.";
    }

} catch (PDOException $e) {
    $_SESSION['admin_error'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['admin_error'] = "Error: " . $e->getMessage();
}

header("Location: manageApplications.php");
exit;