<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// 👇 Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../PHP/dbConnect.php";

class LandlordManager {
    private PDO $db;

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /* -------------------- READ -------------------- */
    public function getAllLandlords(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.full_name, u.phone_no
                FROM user_tbl u
                INNER JOIN user_role_tbl ur ON u.user_id = ur.user_id
                WHERE ur.role_id = 1
                ORDER BY u.full_name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Error fetching landlords: " . $e->getMessage();
            return [];
        }
    }

    /* -------------------- UPDATE -------------------- */
    public function updateLandlord(int $userId, string $fullName, string $phone, ?string $password = null): bool {
        try {
            $phone = $this->normalizePhone($phone);

            if ($password && trim($password) !== "") {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    UPDATE user_tbl 
                    SET full_name = :full_name, phone_no = :phone, password = :password 
                    WHERE user_id = :id
                ");
                $stmt->execute([
                    ':full_name' => $fullName,
                    ':phone' => $phone,
                    ':password' => $hashed,
                    ':id' => $userId
                ]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE user_tbl 
                    SET full_name = :full_name, phone_no = :phone
                    WHERE user_id = :id
                ");
                $stmt->execute([
                    ':full_name' => $fullName,
                    ':phone' => $phone,
                    ':id' => $userId
                ]);
            }

            return true;
        } catch (PDOException $e) {
            $_SESSION['admin_error'] = "Error updating landlord: " . $e->getMessage();
            return false;
        }
    }

    /* -------------------- DELETE -------------------- */
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

    /* -------------------- UTILITIES -------------------- */
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
}

/* ==========================================================
   🧩 ACTION HANDLER SECTION
   Handles POST actions: update / delete
   ========================================================== */

$database = new Database();
$db = $database->getConnection();
$manager = new LandlordManager($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($action === 'delete' && $userId > 0) {
        if ($manager->deleteLandlord($userId)) {
            $_SESSION['admin_success'] = "Landlord deleted successfully!";
        } else {
            $_SESSION['admin_error'] = $_SESSION['admin_error'] ?? "Failed to delete landlord.";
        }
        header("Location: ../PHP/dashboard/admin_dashboard.php");
        exit;
    }

    if ($action === 'update' && $userId > 0) {
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? null;

        if ($manager->updateLandlord($userId, $fullName, $phone, $password)) {
            $_SESSION['admin_success'] = "Landlord updated successfully!";
        } else {
            $_SESSION['admin_error'] = $_SESSION['admin_error'] ?? "Failed to update landlord.";
        }
        header("Location: ../PHP/dashboard/admin_dashboard.php");
        exit;
    }
}


?>
