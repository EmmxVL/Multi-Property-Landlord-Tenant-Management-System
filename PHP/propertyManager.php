<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class PropertyManager {
    private PDO $db;
    private int $userId;

    public function __construct(PDO $db, int $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    /* -------------------- CREATE -------------------- */
    public function addProperty(string $propertyName, string $location): bool {
        if (empty($propertyName) || empty($location)) {
            $_SESSION["error"] = "All fields are required.";
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO property_tbl (user_id, property_name, location)
            VALUES (:user_id, :property_name, :location)
        ");
        return $stmt->execute([
            ":user_id" => $this->userId,
            ":property_name" => $propertyName,
            ":location" => $location
        ]);
    }

    /* -------------------- READ -------------------- */
    public function getProperties(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM property_tbl
            WHERE user_id = :user_id
            ORDER BY property_id DESC
        ");
        $stmt->execute([":user_id" => $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPropertyById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM property_tbl
            WHERE property_id = :id AND user_id = :user_id
        ");
        $stmt->execute([":id" => $id, ":user_id" => $this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* -------------------- UPDATE -------------------- */
    public function updateProperty(int $id, string $propertyName, string $location): bool {
        if (empty($propertyName) || empty($location)) {
            $_SESSION["error"] = "All fields are required.";
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE property_tbl
            SET property_name = :property_name, location = :location
            WHERE property_id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            ":property_name" => $propertyName,
            ":location" => $location,
            ":id" => $id,
            ":user_id" => $this->userId
        ]);
    }

    /* -------------------- DELETE -------------------- */
    public function deleteProperty(int $id): bool {
        $stmt = $this->db->prepare("
            DELETE FROM property_tbl
            WHERE property_id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            ":id" => $id,
            ":user_id" => $this->userId
        ]);
    }
}
?>
