<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class PaymentManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /* -------------------- GET PAYMENTS BY TENANT -------------------- */
public function getPaymentsByTenant(int $tenantId): array {
    $stmt = $this->db->prepare("
        SELECT p.payment_id, p.payment_date, p.payment_status AS status, p.receipt_upload,
               l.lease_id, l.unit_id, u.full_name AS tenant_name,
               (SELECT unit_name FROM unit_tbl WHERE unit_id = l.unit_id) AS unit_name,
               p.amount
        FROM payment_tbl p
        INNER JOIN lease_tbl l ON p.lease_id = l.lease_id
        INNER JOIN user_tbl u ON p.user_id = u.user_id
        WHERE p.user_id = :tenant_id
        ORDER BY p.payment_date ASC, p.payment_id ASC
    ");
    $stmt->execute([":tenant_id" => $tenantId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $leaseBalances = [];

    foreach ($payments as &$payment) {
        $leaseId = $payment['lease_id'];

        if (!isset($leaseBalances[$leaseId])) {
            // Get original lease starting balance
            $stmt2 = $this->db->prepare("SELECT balance + IFNULL((SELECT SUM(amount) FROM payment_tbl WHERE lease_id = :lease_id),0) AS starting_balance FROM lease_tbl WHERE lease_id = :lease_id");
            $stmt2->execute([':lease_id' => $leaseId]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            $leaseBalances[$leaseId] = (float)($row['starting_balance'] ?? 0);
        }

        $leaseBalances[$leaseId] -= (float)$payment['amount'];
        $payment['balance_after_payment'] = $leaseBalances[$leaseId];
    }

    return $payments;
}



    /* -------------------- GET NEXT DUE PAYMENT -------------------- */
    public function getNextDuePayment(int $tenantId): ?array {
        $stmt = $this->db->prepare("
            SELECT l.lease_id, l.unit_id, l.lease_end_date, l.balance, 
                   (SELECT unit_name FROM unit_tbl WHERE unit_id = l.unit_id) AS unit_name
            FROM lease_tbl l
            WHERE l.user_id = :tenant_id
              AND l.lease_status = 'Active'
              AND l.balance > 0
            ORDER BY l.lease_end_date ASC
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
            $stmt = $this->db->prepare("SELECT balance, unit_id FROM lease_tbl WHERE lease_id = :lease_id FOR UPDATE");
            $stmt->execute([':lease_id' => $leaseId]);
            $lease = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lease) {
                $this->db->rollBack();
                $_SESSION['tenant_error'] = "Lease not found.";
                return false;
            }

            $currentBalance = (float) $lease['balance'];
            $amount = round($amount, 2); // ensure two decimals
            $newBalance = max(0, $currentBalance - $amount);

            // Insert payment
            $stmt = $this->db->prepare("
                INSERT INTO payment_tbl (lease_id, user_id, unit_id, payment_date, amount, receipt_upload, payment_status)
                VALUES (:lease_id, :user_id, :unit_id, :payment_date, :amount, :receipt_upload, :status)
            ");
            $stmt->execute([
                ':lease_id' => $leaseId,
                ':user_id' => $tenantId,
                ':unit_id' => $lease['unit_id'],
                ':payment_date' => date('Y-m-d'),
                ':amount' => $amount,
                ':receipt_upload' => $receiptUpload,
                ':status' => $status
            ]);

            // Update lease balance
            $stmt = $this->db->prepare("UPDATE lease_tbl SET balance = :balance WHERE lease_id = :lease_id");
            $stmt->execute([
                ':balance' => $newBalance,
                ':lease_id' => $leaseId
            ]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['tenant_error'] = "Payment failed: " . $e->getMessage();
            return false;
        }
    }

    /* -------------------- UPDATE PAYMENT STATUS -------------------- */
    public function updatePaymentStatus(int $paymentId, string $status): bool {
        $valid = ['Confirmed', 'Ongoing', 'Late'];
        if (!in_array($status, $valid)) return false;

        $stmt = $this->db->prepare("UPDATE payment_tbl SET payment_status = :status WHERE payment_id = :id");
        return $stmt->execute([':status' => $status, ':id' => $paymentId]);
    }



    public function getPaymentsByLease(int $leaseId): array {
        $stmt = $this->db->prepare("
            SELECT payment_id, payment_date, amount, payment_status AS status, receipt_upload
            FROM payment_tbl
            WHERE lease_id = :lease_id
            ORDER BY payment_date ASC, payment_id ASC
        ");
        $stmt->execute([':lease_id' => $leaseId]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute balance after each payment dynamically
        $stmt2 = $this->db->prepare("SELECT balance FROM lease_tbl WHERE lease_id = :lease_id");
        $stmt2->execute([':lease_id' => $leaseId]);
        $lease = $stmt2->fetch(PDO::FETCH_ASSOC);
        $balance = (float)($lease['balance'] ?? 0);

        // Compute starting balance = current lease balance + sum of payments
        $stmt3 = $this->db->prepare("SELECT SUM(amount) AS total_paid FROM payment_tbl WHERE lease_id = :lease_id");
        $stmt3->execute([':lease_id' => $leaseId]);
        $totalPaid = (float)($stmt3->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0);
        $runningBalance = $balance + $totalPaid;

        foreach ($payments as &$payment) {
            $runningBalance -= (float)$payment['amount'];
            $payment['balance_after_payment'] = $runningBalance;
        }

        return $payments;
    }

}
?>
