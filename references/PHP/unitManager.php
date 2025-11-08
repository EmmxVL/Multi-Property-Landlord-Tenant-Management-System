<?php
class UnitManager {
    private PDO $db;
    private int $userId;

    public function __construct(PDO $db, int $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    // ✅ Add Unit
    public function addUnit(int $propertyId, string $unitName, int $rent): bool {
        if (empty($unitName) || empty($rent)) {
            $_SESSION["error"] = "Please fill in all fields.";
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO unit_tbl (user_id, property_id, unit_name, rent)
            VALUES (:user_id, :property_id, :unit_name, :rent)
        ");
        return $stmt->execute([
            ':user_id' => $this->userId,
            ':property_id' => $propertyId,
            ':unit_name' => $unitName,
            ':rent' => $rent
        ]);
    }

    // ✅ Fetch Units for a Property
    public function getUnitsByProperty(int $propertyId): array {
        $stmt = $this->db->prepare("
            SELECT unit_id, unit_name, rent 
            FROM unit_tbl 
            WHERE property_id = :property_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':property_id' => $propertyId,
            ':user_id' => $this->userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Update Unit
    public function updateUnit(int $unitId, string $unitName, int $rent): bool {
        $stmt = $this->db->prepare("
            UPDATE unit_tbl 
            SET unit_name = :unit_name, rent = :rent 
            WHERE unit_id = :unit_id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':unit_name' => $unitName,
            ':rent' => $rent,
            ':unit_id' => $unitId,
            ':user_id' => $this->userId
        ]);
    }

    // ✅ Delete Unit
    public function deleteUnit(int $unitId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM unit_tbl 
            WHERE unit_id = :unit_id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':unit_id' => $unitId,
            ':user_id' => $this->userId
        ]);
    }
}
?>
