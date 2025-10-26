<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "dbConnect.php";

// ✅ Your existing class (unchanged)
class LandlordManager {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getAllLandlords(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.full_name, u.phone_no
                FROM user_tbl u
                JOIN user_role_tbl ur ON u.user_id = ur.user_id
                WHERE ur.role_id = 1
                ORDER BY u.full_name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function addLandlord(string $fullName, string $phone, string $password): bool {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO user_tbl (full_name, phone_no, password) VALUES (:full_name, :phone, :password)");
            $stmt->execute([
                ':full_name' => $fullName,
                ':phone' => $phone,
                ':password' => $hashed
            ]);
            $userId = $this->db->lastInsertId();
            $stmt = $this->db->prepare("INSERT INTO user_role_tbl (user_id, role_id) VALUES (:user_id, 1)");
            $stmt->execute([':user_id' => $userId]);
            return true;
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Error adding landlord: " . $e->getMessage();
            return false;
        }
    }

    public function updateLandlord(int $userId, string $fullName, string $phone, ?string $password = null): bool {
        try {
            $stmt = $this->db->prepare("UPDATE user_tbl SET full_name = :full_name, phone_no = :phone WHERE user_id = :id");
            $stmt->execute([
                ':full_name' => $fullName,
                ':phone' => $phone,
                ':id' => $userId
            ]);
            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE user_tbl SET password = :password WHERE user_id = :id");
                $stmt->execute([':password' => $hashed, ':id' => $userId]);
            }
            return true;
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Error updating landlord: " . $e->getMessage();
            return false;
        }
    }

    public function deleteLandlord(int $userId): bool {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM user_role_tbl WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            $stmt = $this->db->prepare("DELETE FROM user_tbl WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['admin_error'] = "Error deleting landlord: " . $e->getMessage();
            return false;
        }
    }
}

// ✅ CONTROLLER LOGIC (this was missing before)
$database = new Database();
$db = $database->getConnection();
$manager = new LandlordManager($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $userId = intval($_POST['user_id'] ?? 0);
        if ($manager->deleteLandlord($userId)) {
            $_SESSION['admin_success'] = "Landlord deleted successfully!";
        } else {
            $_SESSION['admin_error'] = $_SESSION['admin_error'] ?? "Failed to delete landlord.";
        }
    } else {
        $_SESSION['admin_error'] = "Invalid action.";
    }

    // ✅ Redirect back to dashboard
    ob_clean();
    header("Location: dashboard/admin_dashboard.php");
    exit;
} else {
    header("Location: dashboard/admin_dashboard.php");
    exit;
}
?>
