<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class LeaseManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new lease record.
     * Automatically sets status to "Pending" by default.
     */
    public function createLease(int $tenantId, int $unitId, string $startDate, string $endDate, int $balance = 0): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO lease_tbl (lease_start_date, lease_end_date, balance, unit_id, user_id, lease_status)
                VALUES (:start_date, :end_date, :balance, :unit_id, :user_id, 'Pending')
            ");
            return $stmt->execute([
                ':start_date' => $startDate,
                ':end_date'   => $endDate,
                ':balance'    => $balance,
                ':unit_id'    => $unitId,
                ':user_id'    => $tenantId
            ]);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Fetch all leases for a landlord (through their units).
     */
    public function getLeasesByLandlord(int $landlordId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    l.lease_id,
                    l.lease_start_date,
                    l.lease_end_date,
                    l.balance,
                    l.lease_status,
                    u.unit_name,
                    t.full_name AS tenant_name,
                    t.phone_no AS tenant_phone
                FROM lease_tbl l
                INNER JOIN unit_tbl u ON l.unit_id = u.unit_id
                INNER JOIN user_tbl t ON l.user_id = t.user_id
                INNER JOIN property_tbl p ON u.property_id = p.property_id
                WHERE p.user_id = :landlord_id
                ORDER BY l.lease_start_date DESC
            ");
            $stmt->execute([':landlord_id' => $landlordId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Fetch leases for a specific tenant.
     */
    public function getLeasesByTenant(int $tenantId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    l.*, 
                    u.unit_name, 
                    p.property_name
                FROM lease_tbl l
                INNER JOIN unit_tbl u ON l.unit_id = u.unit_id
                INNER JOIN property_tbl p ON u.property_id = p.property_id
                WHERE l.user_id = :tenant_id
                ORDER BY l.lease_start_date DESC
            ");
            $stmt->execute([':tenant_id' => $tenantId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['tenant_error'] = "Database error: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Update lease status (e.g. from Pending → Active → Terminated)
     */
    public function updateLeaseStatus(int $leaseId, string $newStatus): bool {
        $validStatuses = ['Pending', 'Active', 'Terminated'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['landlord_error'] = "Invalid lease status.";
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE lease_tbl 
                SET lease_status = :status
                WHERE lease_id = :lease_id
            ");
            return $stmt->execute([
                ':status' => $newStatus,
                ':lease_id' => $leaseId
            ]);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Terminate lease manually.
     */
    public function terminateLease(int $leaseId): bool {
        return $this->updateLeaseStatus($leaseId, 'Terminated');
    }
      /**
 * Fetch a single lease by its ID.
 */
// LeaseManager.php
    public function getLeaseByIdForTenant(int $leaseId, int $tenantId): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                l.lease_id,
                l.lease_start_date,
                l.lease_end_date,
                l.balance,
                l.unit_id,
                l.user_id,
                l.lease_status,
                u.unit_name,
                p.property_name
            FROM lease_tbl l
            INNER JOIN unit_tbl u ON l.unit_id = u.unit_id
            INNER JOIN property_tbl p ON u.property_id = p.property_id
            WHERE l.lease_id = :lease_id
            AND l.user_id = :tenant_id
            AND l.lease_status = 'Active'
            LIMIT 1
        ");
        $stmt->execute([
            ':lease_id' => $leaseId,
            ':tenant_id' => $tenantId
        ]);
        $lease = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lease ?: null;
    }


    public function updateLeaseBalance(int $leaseId, float $newBalance): bool {
    try {
        $stmt = $this->db->prepare("UPDATE lease_tbl SET balance = :balance WHERE lease_id = :lease_id");
        return $stmt->execute([
            ':balance' => $newBalance,
            ':lease_id' => $leaseId
        ]);
    } catch (PDOException $e) {
        $_SESSION['tenant_error'] = "Failed to update lease balance: " . $e->getMessage();
        return false;
    }
}

}
?>
