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

    // ✅ Admin creates landlord
    public function createLandlord(string $fullName, string $phone, string $password): void {
        $this->createUser($fullName, $phone, $password, 1, 'dashboard/admin_dashboard.php');
    }

    // ✅ Landlord creates tenant
    public function createTenant(string $fullName, string $phone, string $password): void {
        $this->createUser($fullName, $phone, $password, 2, 'dashboard/landlord_dashboard.php');
    }

    // ✅ Shared user creation logic
    private function createUser(string $fullName, string $phone, string $password, int $roleId, string $redirect): void {
        if (empty($fullName) || empty($phone) || empty($password)) {
            $this->sendError("All fields (Full Name, Phone, Password) are required.", $redirect);
        }

        $normalizedPhone = $this->normalizePhone($phone);

        try {
            // Check existing phone
            $check = $this->db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = :phone LIMIT 1");
            $check->execute([':phone' => $normalizedPhone]);
            if ($check->rowCount() > 0) {
                $this->sendError("A user with that phone number already exists.", $redirect);
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO user_tbl (full_name, phone_no, password)
                VALUES (:full_name, :phone, :password)
            ");
            $stmt->execute([
                ':full_name' => $fullName,
                ':phone' => $normalizedPhone,
                ':password' => $hashedPassword
            ]);

            $userId = $this->db->lastInsertId();

            // Assign role
            $assign = $this->db->prepare("
                INSERT INTO user_role_tbl (role_id, user_id)
                VALUES (:role_id, :user_id)
            ");
            $assign->execute([
                ':role_id' => $roleId,
                ':user_id' => $userId
            ]);

            if ($roleId === 1) {
                $_SESSION['admin_success'] = "Landlord account created successfully.";
            } else {
                $_SESSION['landlord_success'] = "Tenant account created successfully!";
            }

            header("Location: $redirect");
            exit;

        } catch (PDOException $e) {
            $this->sendError("Database error: " . $e->getMessage(), $redirect);
        } catch (Exception $e) {
            $this->sendError("Server error: " . $e->getMessage(), $redirect);
        }
    }
}
?>
