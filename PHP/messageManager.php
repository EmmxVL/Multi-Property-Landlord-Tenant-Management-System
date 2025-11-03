<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class MessageManager {
    private PDO $db;
    private string $apiToken = "API KEY"; 
    private string $singleUrl = "https://sms.iprogtech.com/api/v1/sms_messages";
    private string $bulkUrl = "https://sms.iprogtech.com/api/v1/sms_messages/send_bulk";

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /** Normalize phone to 63 format */
    private function normalizePhone(string $phone): string {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '63' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '63')) {
            $phone = '63' . $phone;
        }
        return $phone;
    }

    /** Send single message */
    public function sendMessage(int $landlord_id, int $tenant_id, int $unit_id, string $message): bool {
        if (empty($message)) return false;

        $stmt = $this->db->prepare("SELECT phone_no FROM user_tbl WHERE user_id = :tenant_id");
        $stmt->execute([':tenant_id' => $tenant_id]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tenant || empty($tenant['phone_no'])) return false;

        $phone = $this->normalizePhone($tenant['phone_no']);

        // Log message
        $insert = $this->db->prepare("
            INSERT INTO message_tbl (unit_id, user_id, message, date_sent, message_status)
            VALUES (:unit_id, :user_id, :message, NOW(), 'Pending')
        ");
        $insert->execute([
            ':unit_id' => $unit_id,
            ':user_id' => $tenant_id,
            ':message' => $message
        ]);

        return $this->sendSMS($phone, $message);
    }

    /** Send message to all active tenants */
    public function sendBulkMessage(int $landlord_id, string $message): bool {
        if (empty($message)) return false;

        // ✅ Select all active tenants under this landlord, including their unit_id
        $stmt = $this->db->prepare("
            SELECT DISTINCT 
                u.user_id, 
                u.phone_no, 
                l.unit_id
            FROM user_tbl u
            INNER JOIN lease_tbl l ON u.user_id = l.user_id
            INNER JOIN unit_tbl un ON l.unit_id = un.unit_id
            INNER JOIN property_tbl p ON un.property_id = p.property_id
            WHERE p.user_id = :landlord_id 
            AND l.lease_status = 'Active'
        ");
        $stmt->execute([':landlord_id' => $landlord_id]);
        $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($tenants)) return false;

        // ✅ Normalize phone numbers
        $phoneNumbers = array_map(fn($t) => $this->normalizePhone($t['phone_no']), $tenants);
        $phoneString = implode(',', $phoneNumbers);

        // ✅ Send message via bulk API
        $url = $this->bulkUrl . "?api_token=" . urlencode($this->apiToken)
            . "&message=" . urlencode($message)
            . "&phone_number=" . urlencode($phoneString);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        // ✅ Log messages to database, now with correct unit_id for each tenant
        $insert = $this->db->prepare("
            INSERT INTO message_tbl (unit_id, user_id, message, date_sent, message_status)
            VALUES (:unit_id, :user_id, :message, NOW(), 'Pending')
        ");

        foreach ($tenants as $t) {
            $insert->execute([
                ':unit_id' => $t['unit_id'],
                ':user_id' => $t['user_id'],
                ':message' => $message
            ]);
        }

        return !empty($response);
    }


    /** Get recent messages by landlord */
    public function getRecentMessagesByLandlord(int $landlord_id, int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT 
                m.message,
                m.date_sent,
                u.full_name AS tenant_name
            FROM message_tbl m
            JOIN user_tbl u ON m.user_id = u.user_id
            JOIN lease_tbl l ON u.user_id = l.user_id
            JOIN unit_tbl un ON l.unit_id = un.unit_id
            JOIN property_tbl p ON un.property_id = p.property_id
            WHERE p.user_id = :landlord_id
            ORDER BY m.date_sent DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':landlord_id', $landlord_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Internal helper for single SMS */
    private function sendSMS(string $phone, string $message): bool {
        $url = $this->singleUrl . "?api_token=" . urlencode($this->apiToken)
            . "&message=" . urlencode($message)
            . "&phone_number=" . urlencode($phone);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return !empty($response);
    }

}
?>