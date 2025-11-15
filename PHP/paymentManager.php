<?php
class PaymentManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * (For Landlord: addLease.php)
     * Creates a new payment record in the database.
     */

        /**
     * (For Landlord Dashboard)
     * Gets all payments under this landlord (across all properties and leases).
     */
    public function getPaymentsByLandlord(int $landlordId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.payment_id,
                    p.amount,
                    p.payment_status AS status,
                    p.payment_date,
                    p.balance_after_payment,
                    l.lease_id,
                    u.full_name AS tenant_name,
                    un.unit_name,
                    pr.property_name
                FROM payment_tbl p
                INNER JOIN lease_tbl l ON p.lease_id = l.lease_id
                INNER JOIN user_tbl u ON l.user_id = u.user_id
                INNER JOIN unit_tbl un ON l.unit_id = un.unit_id
                INNER JOIN property_tbl pr ON un.property_id = pr.property_id
                WHERE pr.user_id = :landlord_id
                ORDER BY p.payment_date DESC
            ");
            $stmt->execute([':landlord_id' => $landlordId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error fetching landlord payments: " . $e->getMessage();
            return [];
        }
    }

    public function createPayment(int $leaseId, float $amount, string $paymentDate, string $status, ?string $receiptPath, string $notes, float $balanceAfterPayment): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payment_tbl (
                    lease_id, amount, payment_date, payment_status, 
                    receipt_upload, notes, balance_after_payment, user_id
                ) VALUES (
                    :lease_id, :amount, :payment_date, :status,
                    :receipt, :notes, :balance_after,
                    (SELECT user_id FROM lease_tbl WHERE lease_id = :lease_id LIMIT 1)
                )
            ");

            return $stmt->execute([
                ':lease_id' => $leaseId,
                ':amount' => $amount,
                ':payment_date' => $paymentDate,
                ':status' => $status,
                ':receipt' => $receiptPath,
                ':notes' => $notes,
                ':balance_after' => $balanceAfterPayment
            ]);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error creating payment: " . $e->getMessage();
            return false;
        }
    }

    /**
     * (For Tenant Dashboard)
     * Gets all payments for a specific tenant (including Pending tenants).
     */
    public function getAllTenantPaymentsByLandlord(int $landlordId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT p.payment_id, p.payment_date, p.amount, p.payment_status, 
                       p.receipt_upload, p.notes, p.balance_after_payment,
                       u.full_name AS tenant_name,
                       l.lease_id, l.unit_id,
                       (SELECT unit_name FROM unit_tbl WHERE unit_id = l.unit_id) AS unit_name
                FROM payment_tbl p
                INNER JOIN lease_tbl l ON p.lease_id = l.lease_id
                INNER JOIN user_tbl u ON l.user_id = u.user_id
                WHERE l.landlord_id = :landlord_id
                  AND p.payment_status = 'Confirmed'
                ORDER BY p.payment_date ASC, p.payment_id ASC
            ");
            $stmt->execute([':landlord_id' => $landlordId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error fetching tenant payments: " . $e->getMessage();
            return [];
        }
    }

    /**
     * (For Tenant Dashboard)
     * Gets all payments by a specific tenant.
     */
       public function getPaymentsByTenant(int $tenantId): array {
        $stmt = $this->db->prepare("
            SELECT p.payment_id, p.payment_date, p.payment_status AS status, p.receipt_upload,
                   l.lease_id, l.unit_id, u.full_name AS tenant_name,
                   (SELECT unit_name FROM unit_tbl WHERE unit_id = l.unit_id) AS unit_name,
                   p.amount, p.notes, p.balance_after_payment
            FROM payment_tbl p
            INNER JOIN lease_tbl l ON p.lease_id = l.lease_id
            INNER JOIN user_tbl u ON l.user_id = u.user_id
            WHERE l.user_id = :tenant_id
            ORDER BY p.payment_date ASC, p.payment_id ASC
        ");
        $stmt->execute([":tenant_id" => $tenantId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Note: The balance logic here might be simplified
        // if balance_after_payment is now reliably stored.
        return $payments;
    }

    /**
     * (For Tenant Dashboard)
     * Gets the next lease with a balance due.
     */
     public function getNextDuePayment(int $tenantId): ?array {
        $stmt = $this->db->prepare("
            SELECT l.lease_id, l.unit_id, l.lease_end_date, l.balance, 
                   (SELECT unit_name FROM unit_tbl WHERE unit_id = l.unit_id) AS unit_name
            FROM lease_tbl l
            WHERE l.user_id = :tenant_id
              AND l.lease_status = 'Active'
              AND l.balance > 0
            ORDER BY l.lease_start_date ASC
            LIMIT 1
        ");
        $stmt->execute([":tenant_id" => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function addPayment(int $leaseId, int $tenantId, float $amount, ?string $receiptUpload = null, string $status = 'Ongoing'): bool {
        try {
            $this->db->beginTransaction();

            // Get current lease balance
            $stmt = $this->db->prepare("SELECT balance, unit_id FROM lease_tbl WHERE lease_id = :lease_id AND user_id = :user_id FOR UPDATE");
            $stmt->execute([':lease_id' => $leaseId, ':user_id' => $tenantId]);
            $lease = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lease) {
                $this->db->rollBack();
                $_SESSION['tenant_error'] = "Lease not found or you are not authorized.";
                return false;
            }

            $currentBalance = (float) $lease['balance'];
            $amount = round($amount, 2); // ensure two decimals
            
            // Insert payment
            $stmt = $this->db->prepare("
                INSERT INTO payment_tbl (
                    lease_id, user_id, unit_id, payment_date, amount, 
                    receipt_upload, payment_status, balance_after_payment, notes
                )
                VALUES (
                    :lease_id, :user_id, :unit_id, :payment_date, :amount, 
                    :receipt_upload, :status, :balance_after, :notes
                )
            ");
            $stmt->execute([
                ':lease_id' => $leaseId,
                ':user_id' => $tenantId,
                ':unit_id' => $lease['unit_id'],
                ':payment_date' => date('Y-m-d'),
                ':amount' => $amount,
                ':receipt_upload' => $receiptUpload,
                ':status' => $status,
                ':balance_after' => $currentBalance, // The balance *before* this payment is confirmed
                ':notes' => 'Tenant submitted payment.'
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['tenant_error'] = "Payment failed: " . $e->getMessage();
            return false;
        }
    }

    /**
     * (For Landlord Dashboard)
     * Updates the status of a payment.
     */
    public function updatePaymentStatus(int $paymentId, string $status): bool {
        $valid = ['Confirmed', 'Ongoing', 'Late', 'Rejected'];
        if (!in_array($status, $valid)) return false;

        try {
            $this->db->beginTransaction();

            // Get payment amount and lease_id
            $stmt = $this->db->prepare("SELECT lease_id, amount, payment_status FROM payment_tbl WHERE payment_id = :id");
            $stmt->execute([':id' => $paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $this->db->rollBack();
                $_SESSION['landlord_error'] = "Payment not found.";
                return false;
            }

            $leaseId = $payment['lease_id'];
            $amount = (float)$payment['amount'];
            $oldStatus = $payment['payment_status'];

            // --- Balance Update Logic ---
            if ($status === 'Confirmed' && $oldStatus !== 'Confirmed') {
                // Payment is being CONFIRMED. Subtract amount from lease balance.
                $stmt = $this->db->prepare("UPDATE lease_tbl SET balance = balance - :amount WHERE lease_id = :lease_id");
                $stmt->execute([':amount' => $amount, ':lease_id' => $leaseId]);
            
            } elseif ($oldStatus === 'Confirmed' && $status !== 'Confirmed') {
                // Payment was PREVIOUSLY Confirmed but is now being REJECTED or set to Ongoing.
                // We must ADD the amount back to the lease balance.
                $stmt = $this->db->prepare("UPDATE lease_tbl SET balance = balance + :amount WHERE lease_id = :lease_id");
                $stmt->execute([':amount' => $amount, ':lease_id' => $leaseId]);
            }
            // --- End Balance Logic ---
            
            // Get the new lease balance *after* the update
            $stmt = $this->db->prepare("SELECT balance FROM lease_tbl WHERE lease_id = :lease_id");
            $stmt->execute([':lease_id' => $leaseId]);
            $newBalance = $stmt->fetchColumn();

            // Update payment status AND the 'balance_after_payment' record
            $stmt = $this->db->prepare("
                UPDATE payment_tbl 
                SET payment_status = :status, balance_after_payment = :balance 
                WHERE payment_id = :id
            ");
            $stmt->execute([
                ':status' => $status, 
                ':balance' => $newBalance,
                ':id' => $paymentId
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION['landlord_error'] = "Failed to update payment status: " . $e->getMessage();
            return false;
        }
    }

    /**
     * (For Landlord Dashboard)
     * Gets all payments for a specific lease.
     */
    public function getPaymentsByLease(int $leaseId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT payment_id, payment_date, amount, payment_status AS status, 
                       receipt_upload, notes, balance_after_payment
                FROM payment_tbl
                WHERE lease_id = :lease_id
                ORDER BY payment_date ASC, payment_id ASC
            ");
            $stmt->execute([':lease_id' => $leaseId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Database error fetching payments: " . $e->getMessage();
            return [];
        }
    }
}
?>