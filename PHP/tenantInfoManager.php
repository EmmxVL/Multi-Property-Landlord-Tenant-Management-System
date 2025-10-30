<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "dbConnect.php";

class TenantInfoManager {
    private PDO $db;
    private string $uploadDir;

    public function __construct(PDO $db) {
        $this->db = $db;

        
        $this->uploadDir = __DIR__ . "/uploads/";

        // Create the folder if not existing
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /* ------------------------------------------------------------
     🧮 Calculate age from birthdate
    ------------------------------------------------------------ */
    private function calculateAge(string $birthdate): int {
        $dob = new DateTime($birthdate);
        $today = new DateTime();
        return $dob->diff($today)->y;
    }

    /* ------------------------------------------------------------
     📂 Handle file upload safely (returns web-accessible path)
    ------------------------------------------------------------ */
    private function uploadFile(array $file): ?string {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $filename = time() . "_" . basename($file['name']);
        $targetPath = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // ✅ Return path usable in <img src=""> — relative to project root
            return "uploads/" . $filename;
        }

        return null;
    }

    /* ------------------------------------------------------------
     🧱 CREATE Tenant Info
    ------------------------------------------------------------ */
    public function createTenantInfo(array $data, array $files): bool {
        try {
            $age = $this->calculateAge($data['birthdate']);

            $stmt = $this->db->prepare("
                INSERT INTO tenant_info (
                    user_id, full_name, birthdate, age, gender, phone_no, email,
                    id_type, id_number, id_photo, birth_certificate, tenant_photo,
                    occupation, employer_name, monthly_income, proof_of_income,
                    property_id, unit_id, lease_start_date, lease_end_date, lease_status,
                    emergency_name, emergency_contact, relationship
                ) VALUES (
                    :user_id, :full_name, :birthdate, :age, :gender, :phone_no, :email,
                    :id_type, :id_number, :id_photo, :birth_certificate, :tenant_photo,
                    :occupation, :employer_name, :monthly_income, :proof_of_income,
                    :property_id, :unit_id, :lease_start_date, :lease_end_date, :lease_status,
                    :emergency_name, :emergency_contact, :relationship
                )
            ");

            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':full_name' => $data['full_name'],
                ':birthdate' => $data['birthdate'],
                ':age' => $age,
                ':gender' => $data['gender'] ?? null,
                ':phone_no' => $data['phone_no'] ?? null,
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

    /* ------------------------------------------------------------
     🧾 READ Tenant Info
    ------------------------------------------------------------ */
    public function getTenantInfo(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                u.full_name, 
                t.email, 
                u.phone_no,
                t.birthdate, 
                t.age, 
                t.gender, 
                t.id_type, 
                t.id_number, 
                t.id_photo, 
                t.birth_certificate, 
                t.tenant_photo, 
                t.occupation, 
                t.employer_name, 
                t.monthly_income, 
                t.proof_of_income, 
                p.property_id, 
                un.unit_id, 
                l.lease_start_date, 
                l.lease_end_date, 
                t.monthly_rent, 
                l.lease_status, 
                t.emergency_name, 
                t.emergency_contact, 
                t.relationship
            FROM user_tbl u
            LEFT JOIN tenant_info t ON u.user_id = t.user_id
            LEFT JOIN lease_tbl l ON u.user_id = l.user_id
            LEFT JOIN unit_tbl un ON l.unit_id = un.unit_id
            LEFT JOIN property_tbl p ON un.property_id = p.property_id
            WHERE u.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        return $tenant ?: null;
    }

    /* ------------------------------------------------------------
     ✏️ UPDATE Tenant Info
    ------------------------------------------------------------ */
    public function updateTenantInfo(int $userId, array $data, array $files): bool {
        try {
            $age = $this->calculateAge($data['birthdate']);

            // ✅ Prepare uploaded file paths
            $idPhoto = $this->uploadFile($files['id_photo']) ?? $data['existing_id_photo'] ?? null;
            $birthCert = $this->uploadFile($files['birth_certificate']) ?? $data['existing_birth_certificate'] ?? null;
            $tenantPhoto = $this->uploadFile($files['tenant_photo']) ?? $data['existing_tenant_photo'] ?? null;
            $proofIncome = $this->uploadFile($files['proof_of_income']) ?? $data['existing_proof_of_income'] ?? null;

            $this->db->beginTransaction();

            // 1️⃣ Update user_tbl
            $stmtUser = $this->db->prepare("
                UPDATE user_tbl 
                SET full_name = :full_name, phone_no = :phone_no
                WHERE user_id = :user_id
            ");
            $stmtUser->execute([
                ':full_name' => $data['full_name'],
                ':phone_no' => $data['phone_no'] ?? null,
                ':user_id' => $userId
            ]);

            // 2️⃣ Update tenant_info
            $stmtTenant = $this->db->prepare("
                UPDATE tenant_info 
                SET
                    birthdate = :birthdate,
                    age = :age,
                    gender = :gender,
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
                    emergency_name = :emergency_name,
                    emergency_contact = :emergency_contact,
                    relationship = :relationship
                WHERE user_id = :user_id
            ");
            $stmtTenant->execute([
                ':birthdate' => $data['birthdate'],
                ':age' => $age,
                ':gender' => $data['gender'] ?? null,
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
                ':emergency_name' => $data['emergency_name'] ?? null,
                ':emergency_contact' => $data['emergency_contact'] ?? null,
                ':relationship' => $data['relationship'] ?? null,
                ':user_id' => $userId
            ]);

            // 3️⃣ Update lease_tbl safely (prevent FK error)
            if (!empty($data['unit_id'])) {
                $checkUnit = $this->db->prepare("SELECT COUNT(*) FROM unit_tbl WHERE unit_id = :unit_id");
                $checkUnit->execute([':unit_id' => $data['unit_id']]);
                $unitExists = $checkUnit->fetchColumn() > 0;

                if ($unitExists) {
                    $stmtLease = $this->db->prepare("
                        UPDATE lease_tbl 
                        SET
                            lease_start_date = :lease_start_date,
                            lease_end_date = :lease_end_date,
                            lease_status = :lease_status,
                            monthly_rent = :monthly_rent,
                            unit_id = :unit_id
                        WHERE user_id = :user_id
                    ");
                    $stmtLease->execute([
                        ':lease_start_date' => $data['lease_start_date'] ?? null,
                        ':lease_end_date' => $data['lease_end_date'] ?? null,
                        ':lease_status' => $data['lease_status'] ?? 'Pending',
                        ':monthly_rent' => $data['monthly_rent'] ?? null,
                        ':unit_id' => $data['unit_id'],
                        ':user_id' => $userId
                    ]);
                } else {
                    error_log("⚠️ Skipping lease_tbl update: invalid unit_id {$data['unit_id']}.");
                }
            }

            $this->db->commit();

            // ✅ Update session
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
                $_SESSION['full_name'] = $data['full_name'];
            }

            return true;

        } catch (PDOException $e) {
            $this->db->rollBack();
            echo "Database Error: " . $e->getMessage();
            return false;
        }
    }

    /* ------------------------------------------------------------
     ❌ DELETE Tenant Info
    ------------------------------------------------------------ */
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
