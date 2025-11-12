<?php

class LeaseManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new lease record.
     * ACCEPTS A NULLABLE END DATE AND A STATUS.
     * Returns the new lease_id on success, or 0 on failure.
     */
    public function createLease(int $tenantId, int $unitId, string $startDate, ?string $endDate, float $balance = 0, string $status = 'Pending'): int {
        try {
            // Check if this unit already has an 'Active' lease
            if ($status === 'Active') {
                $checkStmt = $this->db->prepare("
                    SELECT lease_id FROM lease_tbl 
                    WHERE unit_id = :unit_id AND lease_status = 'Active'
                ");
                $checkStmt->execute([':unit_id' => $unitId]);
                if ($checkStmt->fetch()) {
                    throw new Exception("This unit already has an active lease.");
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO lease_tbl (lease_start_date, lease_end_date, balance, unit_id, user_id, lease_status)
                VALUES (:start_date, :end_date, :balance, :unit_id, :user_id, :status)
            ");
            $success = $stmt->execute([
                ':start_date' => $startDate,
                ':end_date'   => $endDate, // This can now be null
                ':balance'    => $balance,
                ':unit_id'    => $unitId,
                ':user_id'    => $tenantId,
                ':status'     => $status
            ]);

            if ($success) {
                return (int)$this->db->lastInsertId(); // Return the new lease ID
            } else {
                return 0;
            }

        } catch (PDOException $e) {
            // Handle duplicate entry (e.g., tenant already has a lease)
            if ($e->errorInfo[1] == 1062) {
                 $_SESSION['landlord_error'] = "This tenant is already assigned to an active lease.";
            } else {
                $_SESSION['landlord_error'] = "Database error: " . $e->getMessage();
            }
            return 0;
        } catch (Exception $e) {
             $_SESSION['landlord_error'] = $e->getMessage();
            return 0;
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
                    t.phone_no AS tenant_phone,
                    p.property_name,
                    l_loc.location_name AS location
                FROM lease_tbl l
                INNER JOIN unit_tbl u ON l.unit_id = u.unit_id
                INNER JOIN user_tbl t ON l.user_id = t.user_id
                INNER JOIN property_tbl p ON u.property_id = p.property_id
                LEFT JOIN location_tbl l_loc ON p.location_id = l_loc.location_id
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
                    p.property_name,
                    l_loc.location_name AS location
                FROM lease_tbl l
                INNER JOIN unit_tbl u ON l.unit_id = u.unit_id
                INNER JOIN property_tbl p ON u.property_id = p.property_id
                LEFT JOIN location_tbl l_loc ON p.location_id = l_loc.location_id
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
    public function updateLeaseStatus(int $leaseId, string $newStatus, ?string $endDate = null): bool {
        $validStatuses = ['Pending', 'Active', 'Terminated'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['landlord_error'] = "Invalid lease status.";
            return false;
        }

        try {
            // If terminating, also set the end date
            if ($newStatus === 'Terminated') {
                $sql = "UPDATE lease_tbl SET lease_status = :status, lease_end_date = :end_date WHERE lease_id = :lease_id";
                $params = [
                    ':status' => $newStatus,
                    ':end_date' => $endDate ?? date('Y-m-d'), // Set end date to today if not provided
                    ':lease_id' => $leaseId
                ];
            } else {
                // For 'Active' or 'Pending', just update the status (and set end_date to NULL for open-ended)
                $sql = "UPDATE lease_tbl SET lease_status = :status, lease_end_date = NULL WHERE lease_id = :lease_id";
                $params = [
                    ':status' => $newStatus,
                    ':lease_id' => $leaseId
                ];
            }
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Terminate lease manually.
     */
    public function terminateLease(int $leaseId): bool {
        // Automatically sets the end date to today
        return $this->updateLeaseStatus($leaseId, 'Terminated', date('Y-m-d'));
    }

    /**
     * Fetch a single lease by its ID.
     */
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
                p.property_name,
                l_loc.location_name AS location
            FROM lease_tbl l
            INNER JOIN unit_tbl u ON l.unit_id = u.unit_id
            INNER JOIN property_tbl p ON u.property_id = p.property_id
            LEFT JOIN location_tbl l_loc ON p.location_id = l_loc.location_id
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

    /**
     * Update lease balance.
     */
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