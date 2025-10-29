<?php
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
            // Normalize phone
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
?>
