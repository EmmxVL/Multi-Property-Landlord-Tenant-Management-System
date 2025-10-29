<?php
require_once "dbConnect.php";

class TenantInfoManager {
    private PDO $db;
    private string $uploadDir = "uploads/";

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // 🧮 Calculate age from birthdate
    private function calculateAge(string $birthdate): int {
        $dob = new DateTime($birthdate);
        $today = new DateTime();
        return $dob->diff($today)->y;
    }

    // 📂 Handle file upload safely
    private function uploadFile(array $file): ?string {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $filename = time() . "_" . basename($file['name']);
        $targetPath = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $targetPath;
        }

        return null;
    }

    // 🧱 CREATE Tenant Info
    public function createTenantInfo(array $data, array $files): bool {
        try {
            $age = $this->calculateAge($data['birthdate']);

            $stmt = $this->db->prepare("
                INSERT INTO tenant_info (
                    user_id, full_name, birthdate, age, gender, contact_number, email,
                    id_type, id_number, id_photo, birth_certificate, tenant_photo,
                    occupation, employer_name, monthly_income, proof_of_income,
                    property_id, unit_id, lease_start_date, lease_end_date, monthly_rent, lease_status,
                    emergency_name, emergency_contact, relationship
                ) VALUES (
                    :user_id, :full_name, :birthdate, :age, :gender, :contact_number, :email,
                    :id_type, :id_number, :id_photo, :birth_certificate, :tenant_photo,
                    :occupation, :employer_name, :monthly_income, :proof_of_income,
                    :property_id, :unit_id, :lease_start_date, :lease_end_date, :monthly_rent, :lease_status,
                    :emergency_name, :emergency_contact, :relationship
                )
            ");

            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':full_name' => $data['full_name'],
                ':birthdate' => $data['birthdate'],
                ':age' => $age,
                ':gender' => $data['gender'] ?? null,
                ':contact_number' => $data['contact_number'] ?? null,
                ':email' => $data['email'] ?? null,

                ':id_type' => $data['id_type'] ?? null,
                ':id_number' => $data['id_number'] ?? null,
                ':id_photo' => $this->uploadFile($files['id_photo']),
                ':birth_certificate' => $this->uploadFile($files['birth_certificate']),
                ':tenant_photo' => $this->uploadFile($files['tenant_photo']),

                ':occupation' => $data['occupation'] ?? null,
                ':employer_name' => $data['employer_name'] ?? null,
                ':monthly_income' => $data['monthly_income'] ?? null,
                ':proof_of_income' => $this->uploadFile($files['proof_of_income']),

                ':property_id' => $data['property_id'] ?? null,
                ':unit_id' => $data['unit_id'] ?? null,
                ':lease_start_date' => $data['lease_start_date'] ?? null,
                ':lease_end_date' => $data['lease_end_date'] ?? null,
                ':monthly_rent' => $data['monthly_rent'] ?? null,
                ':lease_status' => $data['lease_status'] ?? 'Pending',

                ':emergency_name' => $data['emergency_name'] ?? null,
                ':emergency_contact' => $data['emergency_contact'] ?? null,
                ':relationship' => $data['relationship'] ?? null,
            ]);

            return true;
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    // 🧾 READ Tenant Info
    public function getTenantInfo(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tenant_info WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        return $tenant ?: null;
    }

    // ✏️ UPDATE Tenant Info
    public function updateTenantInfo(int $userId, array $data, array $files): bool {
        try {
            $age = $this->calculateAge($data['birthdate']);

            // Prepare uploaded file paths
            $idPhoto = $this->uploadFile($files['id_photo']) ?? $data['existing_id_photo'] ?? null;
            $birthCert = $this->uploadFile($files['birth_certificate']) ?? $data['existing_birth_certificate'] ?? null;
            $tenantPhoto = $this->uploadFile($files['tenant_photo']) ?? $data['existing_tenant_photo'] ?? null;
            $proofIncome = $this->uploadFile($files['proof_of_income']) ?? $data['existing_proof_of_income'] ?? null;

            $stmt = $this->db->prepare("
                UPDATE tenant_info SET
                    full_name = :full_name,
                    birthdate = :birthdate,
                    age = :age,
                    gender = :gender,
                    contact_number = :contact_number,
                    email = :email,
                    id_type = :id_type,
                    id_number = :id_number,
                    id_photo = :id_photo,
                    birth_certificate = :birth_certificate,
                    tenant_photo = :tenant_photo,
                    occupation = :occupation,
                    employer_name = :employer_name,
                    monthly_income = :monthly_income,
                    proof_of_income = :proof_of_income,
                    property_id = :property_id,
                    unit_id = :unit_id,
                    lease_start_date = :lease_start_date,
                    lease_end_date = :lease_end_date,
                    monthly_rent = :monthly_rent,
                    lease_status = :lease_status,
                    emergency_name = :emergency_name,
                    emergency_contact = :emergency_contact,
                    relationship = :relationship
                WHERE user_id = :user_id
            ");

            $stmt->execute([
                ':full_name' => $data['full_name'],
                ':birthdate' => $data['birthdate'],
                ':age' => $age,
                ':gender' => $data['gender'] ?? null,
                ':contact_number' => $data['contact_number'] ?? null,
                ':email' => $data['email'] ?? null,
                ':id_type' => $data['id_type'] ?? null,
                ':id_number' => $data['id_number'] ?? null,
                ':id_photo' => $idPhoto,
                ':birth_certificate' => $birthCert,
                ':tenant_photo' => $tenantPhoto,
                ':occupation' => $data['occupation'] ?? null,
                ':employer_name' => $data['employer_name'] ?? null,
                ':monthly_income' => $data['monthly_income'] ?? null,
                ':proof_of_income' => $proofIncome,
                ':property_id' => $data['property_id'] ?? null,
                ':unit_id' => $data['unit_id'] ?? null,
                ':lease_start_date' => $data['lease_start_date'] ?? null,
                ':lease_end_date' => $data['lease_end_date'] ?? null,
                ':monthly_rent' => $data['monthly_rent'] ?? null,
                ':lease_status' => $data['lease_status'] ?? 'Pending',
                ':emergency_name' => $data['emergency_name'] ?? null,
                ':emergency_contact' => $data['emergency_contact'] ?? null,
                ':relationship' => $data['relationship'] ?? null,
                ':user_id' => $userId
            ]);

            return true;
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    // ❌ DELETE Tenant Info
    public function deleteTenantInfo(int $userId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM tenant_info WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $userId]);
            return true;
        } catch (PDOException $e) {
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
