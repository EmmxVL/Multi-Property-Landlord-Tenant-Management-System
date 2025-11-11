<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "dbConnect.php";

// Check for admin session
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    $_SESSION['admin_error'] = "Unauthorized action.";
    header("Location: dashboard/admin_dashboard.php");
    exit;
}

class AdminManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function sendError(string $message, string $location = 'dashboard/admin_dashboard.php'): void {
        $_SESSION['admin_error'] = $message;
        header("Location: $location");
        exit;
    }

    private function sendSuccess(string $message, string $location = 'dashboard/admin_dashboard.php'): void {
        $_SESSION['admin_success'] = $message;
        header("Location: $location");
        exit;
    }

    private function normalizePhone(string $phone): string {
        $phone = preg_replace('/[^+0-9]/', '', trim($phone));
        if (strpos($phone, '+63') === 0) {
            $phone = '0' . substr($phone, 3);
        }
        if (strlen($phone) === 10 && strpos($phone, '9') === 0) {
            $phone = '0' . $phone;
        }
        return $phone;
    }

    /**
     * Admin manually adds a Landlord.
     * This account is automatically approved.
     */
    public function addLandlord(string $fullName, string $phone, string $password): void {
        if (empty($fullName) || empty($phone) || empty($password)) {
            $this->sendError("All fields (Full Name, Phone, Password) are required.");
        }

        $normalizedPhone = $this->normalizePhone($phone);
        $userId = 0; // Initialize to satisfy linter

        try {
            $this->db->beginTransaction();

            // Prevent duplicate phone
            $check = $this->db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = :phone LIMIT 1");
            $check->execute([':phone' => $normalizedPhone]);
            if ($check->rowCount() > 0) {
                $this->sendError("A user with that phone number already exists.");
            }

            // Insert landlord with 'approved' status
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO user_tbl (full_name, phone_no, password, status, created_at)
                VALUES (:full_name, :phone, :password, 'approved', CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                ':full_name' => $fullName,
                ':phone' => $normalizedPhone,
                ':password' => $hashed
            ]);
            $userId = $this->db->lastInsertId();

            // Assign role_id = 1 (Landlord)
            $roleStmt = $this->db->prepare("
                INSERT INTO user_role_tbl (user_id, role_id)
                VALUES (:user_id, 1)
            ");
            $roleStmt->execute([':user_id' => $userId]);

            // Create a blank info record for them
            $infoStmt = $this->db->prepare("INSERT INTO landlord_info_tbl (user_id) VALUES (:user_id)");
            $infoStmt->execute([':user_id' => $userId]);

            $this->db->commit();
            $this->sendSuccess("Landlord account created successfully.");

        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->sendError("Database error while adding landlord: " . $e->getMessage());
        }
    }

    /**
     * Admin deletes a Landlord account.
     * This will cascade and delete all related data AND files.
     */
    public function deleteLandlord(int $userId): void {
        if ($userId <= 0) {
            $this->sendError("Invalid User ID.");
        }

        try {
            $this->db->beginTransaction();
            

            $stmt = $this->db->prepare("SELECT * FROM landlord_info_tbl WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($info) {
                // 2. Delete each file
                foreach ($info as $key => $value) {
                    // Check if the value is a string and looks like a file path
                    if (is_string($value) && strpos($value, 'uploads/landlord_docs/') === 0) {
                        $fullPath = "../" . $value;
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                }

                // 3. Delete the user's directory
                $userDir = "../uploads/landlord_docs/user_{$userId}";
                if (is_dir($userDir)) {
                    @rmdir($userDir); // Suppress error if not empty, though it should be
                }
            }
            // --- END File Logic ---

            // 4. Delete user from database (cascading delete will handle roles, info, etc.)
            $stmt = $this->db->prepare("DELETE FROM user_tbl WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);

            if ($stmt->rowCount() > 0) {
                $this->db->commit();
                $this->sendSuccess("Landlord account and all associated files deleted successfully.");
            } else {
                $this->db->rollBack();
                $this->sendError("Could not find landlord to delete.");
            }

        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->sendError("Database error while deleting landlord: " . $e->getMessage());
        }
    }
}

// --- Main Request Handler ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $manager = new AdminManager($db);

    $action = $_POST['action'] ?? '';

    if ($action === 'add_landlord') {
        $manager->addLandlord(
            $_POST['full_name'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['password'] ?? ''
        );
    } elseif ($action === 'delete_landlord') {
        $manager->deleteLandlord(
            (int)($_POST['user_id'] ?? 0)
        );
    } else {
        $_SESSION['admin_error'] = "Invalid or missing action.";
        header("Location: dashboard/admin_dashboard.php");
        exit;
    }
} else {
    // Redirect if accessed directly
    header("Location: dashboard/admin_dashboard.php");
    exit;
}
?>