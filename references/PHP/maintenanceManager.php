<?php
class MaintenanceManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    //  Tenant: Create maintenance request
    public function createRequest(int $unit_id, int $user_id, string $description): bool {
        $stmt = $this->db->prepare("
            INSERT INTO maintenance_tbl (unit_id, user_id, description, maintenance_status, maintenance_start_date)
            VALUES (:unit_id, :user_id, :description, 'Ongoing', NOW())
        ");
        return $stmt->execute([
            ':unit_id' => $unit_id,
            ':user_id' => $user_id,
            ':description' => $description
        ]);
    }


    //  Tenant: View all requests
    public function getRequestsByTenant(int $user_id): array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.unit_name
            FROM maintenance_tbl m
            JOIN unit_tbl u ON m.unit_id = u.unit_id
            WHERE m.user_id = :user_id
            ORDER BY m.request_id DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    //  Landlord: View all requests for their units
    public function getRequestsByLandlord(int $landlord_id): array {
        $stmt = $this->db->prepare("
            SELECT 
                m.*, 
                u.unit_name,
                p.property_name
            FROM maintenance_tbl m
            JOIN unit_tbl u ON m.unit_id = u.unit_id
            JOIN property_tbl p ON u.property_id = p.property_id
            WHERE p.user_id = :landlord_id
            ORDER BY m.request_id DESC
        ");
        $stmt->execute([':landlord_id' => $landlord_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //  Landlord: Update maintenance status
    public function updateStatus(int $request_id, string $status): bool {
        $end_date = null;

        // ✅ Only set end_date when status is Completed or Rejected
        if (in_array($status, ['Completed', 'Rejected'])) {
            $end_date = date('Y-m-d H:i:s');
        }

        $stmt = $this->db->prepare("
            UPDATE maintenance_tbl
            SET maintenance_status = :status, maintenance_end_date = :end_date
            WHERE request_id = :request_id
        ");
        return $stmt->execute([
            ':status' => $status,
            ':end_date' => $end_date,
            ':request_id' => $request_id
        ]);
    }

    //  Delete request (optional)
    public function deleteRequest(int $request_id): bool {
        $stmt = $this->db->prepare("DELETE FROM maintenance_tbl WHERE request_id = :request_id");
        return $stmt->execute([':request_id' => $request_id]);
    }
}
