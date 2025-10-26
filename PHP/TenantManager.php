<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class TenantManager {
    private PDO $db;
    private int $landlordId;

    public function __construct(PDO $db, int $landlordId) {
        $this->db = $db;
        $this->landlordId = $landlordId;
    }

    /* -------------------- READ -------------------- */
    public function getTenants(): array {
        // If there's a landlord_tenant_tbl, filter by landlord
        if ($this->tenantLinkExists()) {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.full_name, u.phone_no
                FROM user_tbl u
                INNER JOIN landlord_tenant_tbl lt ON lt.tenant_id = u.user_id
                WHERE lt.landlord_id = :landlord_id
                ORDER BY u.full_name ASC
            ");
            $stmt->execute([":landlord_id" => $this->landlordId]);
        } else {
            // Otherwise show all tenants (role_id = 2)
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.full_name, u.phone_no
                FROM user_tbl u
                INNER JOIN user_role_tbl ur ON u.user_id = ur.user_id
                WHERE ur.role_id = 2
                ORDER BY u.full_name ASC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* -------------------- UPDATE -------------------- */
    public function updateTenant(int $tenantId, string $fullName, string $phone, ?string $password = null): bool {
        $normalizedPhone = $this->normalizePhone($phone);

        try {
            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    UPDATE user_tbl 
                    SET full_name = :name, phone_no = :phone, password = :password 
                    WHERE user_id = :id
                ");
                return $stmt->execute([
                    ":name" => $fullName,
                    ":phone" => $normalizedPhone,
                    ":password" => $hashed,
                    ":id" => $tenantId
                ]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE user_tbl 
                    SET full_name = :name, phone_no = :phone 
                    WHERE user_id = :id
                ");
                return $stmt->execute([
                    ":name" => $fullName,
                    ":phone" => $normalizedPhone,
                    ":id" => $tenantId
                ]);
            }
        } catch (PDOException $e) {
            $_SESSION["error"] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /* -------------------- DELETE -------------------- */
    public function deleteTenant(int $tenantId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_tbl WHERE user_id = :id");
            return $stmt->execute([":id" => $tenantId]);
        } catch (PDOException $e) {
            $_SESSION["error"] = "Error deleting tenant: " . $e->getMessage();
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

    private function tenantLinkExists(): bool {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'landlord_tenant_tbl'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
