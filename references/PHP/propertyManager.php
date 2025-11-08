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
    public function addProperty(string $propertyName, string $location, ?float $latitude = null, ?float $longitude = null): bool {
        if (empty($propertyName) || empty($location)) {
            $_SESSION["error"] = "All fields are required.";
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Insert location first
            $stmt = $this->db->prepare("
                INSERT INTO location_tbl (location_name, latitude, longitude)
                VALUES (:location_name, :latitude, :longitude)
            ");
            $stmt->execute([
                ":location_name" => $location,
                ":latitude" => $latitude,
                ":longitude" => $longitude
            ]);
            $locationId = $this->db->lastInsertId();

            // Insert property linked to that location
            $stmt = $this->db->prepare("
                INSERT INTO property_tbl (user_id, property_name, location_id)
                VALUES (:user_id, :property_name, :location_id)
            ");
            $stmt->execute([
                ":user_id" => $this->userId,
                ":property_name" => $propertyName,
                ":location_id" => $locationId
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION["error"] = "Database Error: " . $e->getMessage();
            return false;
        }
    }

    /* -------------------- READ -------------------- */
    public function getProperties(): array {
        $stmt = $this->db->prepare("
            SELECT p.property_id, p.property_name, l.location_name, l.latitude, l.longitude
            FROM property_tbl p
            LEFT JOIN location_tbl l ON p.location_id = l.location_id
            WHERE p.user_id = :user_id
            ORDER BY p.property_id DESC
        ");
        $stmt->execute([":user_id" => $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPropertyById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.property_id, p.property_name, l.location_name, l.latitude, l.longitude, l.location_id
            FROM property_tbl p
            LEFT JOIN location_tbl l ON p.location_id = l.location_id
            WHERE p.property_id = :id AND p.user_id = :user_id
        ");
        $stmt->execute([":id" => $id, ":user_id" => $this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* -------------------- UPDATE -------------------- */
   public function updateProperty(int $id, string $propertyName, string $location, ?float $latitude = null, ?float $longitude = null): bool {
    if (empty($propertyName) || empty($location)) {
        $_SESSION["error"] = "All fields are required.";
        return false;
    }

    try {
        $this->db->beginTransaction();

        // Get location_id of the property
        $stmt = $this->db->prepare("
            SELECT location_id FROM property_tbl
            WHERE property_id = :property_id AND user_id = :user_id
        ");
        $stmt->execute([
            ":property_id" => $id,
            ":user_id" => $this->userId
        ]);
        $locationId = $stmt->fetchColumn();

        if (!$locationId) {
            throw new Exception("Invalid property or unauthorized access.");
        }

        // ✅ DEBUG: Check what coordinates are being received
        error_log("Updating property $id with coordinates: Lat=$latitude, Lng=$longitude");

        // Update property name
        $stmt = $this->db->prepare("
            UPDATE property_tbl
            SET property_name = :property_name
            WHERE property_id = :property_id AND user_id = :user_id
        ");
        $stmt->execute([
            ":property_name" => $propertyName,
            ":property_id" => $id,
            ":user_id" => $this->userId
        ]);

        // Update linked location
        $stmt = $this->db->prepare("
            UPDATE location_tbl
            SET location_name = :location_name,
                latitude = :latitude,
                longitude = :longitude
            WHERE location_id = :location_id
        ");
        $stmt->execute([
            ":location_name" => $location,
            ":latitude" => $latitude,
            ":longitude" => $longitude,
            ":location_id" => $locationId
        ]);

        // ✅ DEBUG: Verify the update worked
        $checkStmt = $this->db->prepare("SELECT latitude, longitude FROM location_tbl WHERE location_id = :location_id");
        $checkStmt->execute([":location_id" => $locationId]);
        $updatedCoords = $checkStmt->fetch(PDO::FETCH_ASSOC);
        error_log("After update - Lat: {$updatedCoords['latitude']}, Lng: {$updatedCoords['longitude']}");

        $this->db->commit();
        return true;
    } catch (Exception $e) {
        $this->db->rollBack();
        $_SESSION["error"] = "Update failed: " . $e->getMessage();
        return false;
    }
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

    /* -------------------- DASHBOARD VIEW -------------------- */
    public function getDashboardPropertiesWithUnits(int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT 
                p.property_id, 
                p.property_name, 
                l.location_name AS location,
                COUNT(u.unit_id) AS unit_count
            FROM property_tbl p
            LEFT JOIN location_tbl l ON p.location_id = l.location_id
            LEFT JOIN unit_tbl u ON p.property_id = u.property_id
            WHERE p.user_id = :user_id
            GROUP BY p.property_id
            ORDER BY p.property_id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
