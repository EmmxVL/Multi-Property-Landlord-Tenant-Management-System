<?php
class TenantManager {
    private $db;
    private $userId;

    public function __construct($db, $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    public function addTenant($propertyId, $unitId, $tenantName, $contact) {
        $stmt = $this->db->prepare("
            INSERT INTO tenant_tbl (property_id, unit_id, tenant_name, contact, user_id)
            VALUES (:property_id, :unit_id, :tenant_name, :contact, :user_id)
        ");
        return $stmt->execute([
            ':property_id' => $propertyId,
            ':unit_id' => $unitId,
            ':tenant_name' => $tenantName,
            ':contact' => $contact,
            ':user_id' => $this->userId
        ]);
    }

    public function deleteTenant($tenantId) {
        $stmt = $this->db->prepare("
            DELETE FROM tenant_tbl WHERE tenant_id = :tenant_id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $this->userId
        ]);
    }

    public function getTenantsByProperty($propertyId) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.unit_name
            FROM tenant_tbl t
            JOIN unit_tbl u ON t.unit_id = u.unit_id
            WHERE t.property_id = :property_id AND t.user_id = :user_id
        ");
        $stmt->execute([
            ':property_id' => $propertyId,
            ':user_id' => $this->userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>