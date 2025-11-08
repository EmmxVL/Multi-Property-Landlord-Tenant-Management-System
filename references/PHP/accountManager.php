<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AccountManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    private function sendError(string $message, string $location = 'dashboard/admin_dashboard.php'): void {
        $_SESSION['admin_error'] = $message;
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

    // ✅ Admin adds a Landlord
    public function addLandlord(string $fullName, string $phone, string $password): bool {
        if (empty($fullName) || empty($phone) || empty($password)) {
            $this->sendError("All fields (Full Name, Phone, Password) are required.");
        }

        $normalizedPhone = $this->normalizePhone($phone);

        try {
            // Prevent duplicate phone
            $check = $this->db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = :phone LIMIT 1");
            $check->execute([':phone' => $normalizedPhone]);
            if ($check->rowCount() > 0) {
                $this->sendError("A landlord with that phone number already exists.");
            }

            // Insert landlord
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO user_tbl (full_name, phone_no, password)
                VALUES (:full_name, :phone, :password)
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

            $_SESSION['admin_success'] = "Landlord account created successfully.";
            header("Location: dashboard/admin_dashboard.php");
            exit;

        } catch (PDOException $e) {
            $this->sendError("Database error while adding landlord: " . $e->getMessage());
        } catch (Exception $e) {
            $this->sendError("Server error: " . $e->getMessage());
        }

        return false;
    }

    // ✅ Landlord creates a Tenant
    public function createTenant(int $landlordId, string $fullName, string $phone, string $password): bool {
    if ($landlordId <= 0 || empty($fullName) || empty($phone) || empty($password)) {
        $this->sendError("All fields (Full Name, Phone, Password) are required.", "manageTenants.php");
        return false;
    }

    $normalizedPhone = $this->normalizePhone($phone);

    try {
        // Check for existing tenant phone number
        $check = $this->db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = :phone LIMIT 1");
        $check->execute([':phone' => $normalizedPhone]);
        if ($check->rowCount() > 0) {
            $this->sendError("A tenant with that phone number already exists.", "manageTenants.php");
            return false;
        }

        // Insert tenant record
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO user_tbl (full_name, phone_no, password, landlord_id)
            VALUES (:full_name, :phone, :password, :landlord_id)
        ");
        $stmt->execute([
            ':full_name' => $fullName,
            ':phone' => $normalizedPhone,
            ':password' => $hashed,
            ':landlord_id' => $landlordId
        ]);

        $userId = $this->db->lastInsertId();

        // Assign role_id = 2 (Tenant)
        $roleStmt = $this->db->prepare("
            INSERT INTO user_role_tbl (user_id, role_id)
            VALUES (:user_id, 2)
        ");
        $roleStmt->execute([':user_id' => $userId]);

        $_SESSION['landlord_success'] = "Tenant account created successfully.";
        return true; // Let the caller redirect

    } catch (PDOException $e) {
        $this->sendError("Database error while creating tenant: " . $e->getMessage(), "manageTenants.php");
        return false;
    } catch (Exception $e) {
        $this->sendError("Server error: " . $e->getMessage(), "manageTenants.php");
        return false;
    }
}

}
?>
