<?php



class TenantManager {
    private PDO $db;
    private int $landlordId;

    public function __construct(PDO $db, int $landlordId) {
        $this->db = $db;
        $this->landlordId = $landlordId;
    }

    /* ------------------------------------------------------------
     * 헬 HELPER FUNCTIONS
     * ------------------------------------------------------------ */

    /**
     * Calculates age from a birthdate.
     */
    private function calculateAge(string $birthdate): ?int {
        try {
            $dob = new DateTime($birthdate);
            $today = new DateTime();
            return $dob->diff($today)->y;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Normalizes a Philippine phone number.
     */
    private function normalizePhone(string $phone): string {
        $phone = preg_replace('/[^+0-9]/', '', trim($phone));
        if (strpos($phone, '+63') === 0) {
            $phone = '0' . substr($phone, 3);
        }
        if (strlen($phone) === 10 && strpos($phone, '9') === 0) {
            $phone = '0' . $phone;
        }
        return $phone;
    }

    /**
     * Securely handles a file upload for a specific tenant.
     * Deletes the old file if one exists.
     * Returns the new database-safe path.
     */
    private function _handleUpload(array $file, int $tenantUserId, string $fieldName): ?string {
        // Check for error
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                return null; // No new file uploaded, this is fine
            }
            throw new Exception("File upload error for {$fieldName}: " . $file['error']);
        }

        $uploadDir = "../uploads/tenant_docs/user_{$tenantUserId}/";
        $dbDir = "uploads/tenant_docs/user_{$tenantUserId}/";

        // 1. Get old file path from DB
        // Note: We use the $fieldName to dynamically check the correct column
        $stmt = $this->db->prepare("SELECT $fieldName FROM tenant_info_tbl WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $tenantUserId]);
        $oldFilePath = $stmt->fetchColumn();

        // 2. Delete old file from server
        if ($oldFilePath && file_exists("../" . $oldFilePath)) {
            @unlink("../" . $oldFilePath);
        }

        // 3. Create new directory if needed
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception("Failed to create upload directory.");
            }
        }

        // 4. Save new file
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        // Use fieldName in the unique name for clarity
        $uniqueName = $fieldName . '_' . uniqid() . '.' . $extension;
        $serverPath = $uploadDir . $uniqueName;
        $databasePath = $dbDir . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $serverPath)) {
            throw new Exception("Failed to move uploaded file for {$fieldName}.");
        }

        return $databasePath;
    }
    
    /* ------------------------------------------------------------
     * 📖 READ FUNCTIONS
     * ------------------------------------------------------------ */

    /**
     * Gets ALL tenants (full_name, phone_no, status) assigned to this landlord.
     * Used for the main "Manage Tenants" list.
     */
    public function getTenantsInfo(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.full_name, u.phone_no, u.status
                FROM user_tbl u
                INNER JOIN user_role_tbl ur ON ur.user_id = u.user_id
                WHERE ur.role_id = 2
                  AND u.landlord_id = :landlord_id
                ORDER BY u.full_name ASC
            ");
            $stmt->execute([':landlord_id' => $this->landlordId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Error fetching tenants: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Gets only approved tenants who are NOT on an active lease.
     * Used for the "Add Lease" dropdown.
     */
    public function getAvailableTenants(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.user_id, u.full_name, u.phone_no
                FROM user_tbl u
                JOIN user_role_tbl ur ON u.user_id = ur.user_id
                WHERE ur.role_id = 2
                  AND u.landlord_id = :landlord_id
                  AND u.status = 'approved'
                  AND u.user_id NOT IN (
                      SELECT l.user_id FROM lease_tbl l WHERE l.lease_status = 'Active' AND l.user_id IS NOT NULL
                  )
                ORDER BY u.full_name ASC
            ");
            $stmt->execute([':landlord_id' => $this->landlordId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Error fetching available tenants: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Gets all info for a single tenant (for the edit page).
     * Joins user_tbl and tenant_info_tbl.
     */
    public function getSingleTenantDetails(int $tenantUserId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.full_name, u.phone_no, t.*
                FROM user_tbl u
                LEFT JOIN tenant_info_tbl t ON u.user_id = t.user_id
                WHERE u.user_id = :tenant_user_id
                  AND u.landlord_id = :landlord_id
                  AND (SELECT ur.role_id FROM user_role_tbl ur WHERE ur.user_id = u.user_id LIMIT 1) = 2
            ");
            $stmt->execute([
                ':tenant_user_id' => $tenantUserId,
                ':landlord_id' => $this->landlordId
            ]);
            $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if tenant_info_tbl record exists, if not, create one
            if ($tenant && $tenant['user_id'] === null && $tenantUserId > 0) {
                $this->db->prepare("INSERT INTO tenant_info_tbl (user_id) VALUES (:user_id)")->execute([':user_id' => $tenantUserId]);
                // Re-fetch
                $stmt->execute([
                    ':tenant_user_id' => $tenantUserId,
                    ':landlord_id' => $this->landlordId
                ]);
                $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return $tenant ?: null;

        } catch (PDOException $e) {
            $_SESSION['landlord_error'] = "Error fetching tenant details: " . $e->getMessage();
            return null;
        }
    }
    
    /* ------------------------------------------------------------
     * ✏️ UPDATE FUNCTION
     * ------------------------------------------------------------ */

    /**
     * Updates a tenant's full profile (user_tbl and tenant_info_tbl).
     * This is used by the landlord's "Edit Tenant" page.
     */
    public function updateTenantInfo(int $tenantUserId, array $data, array $files): bool {
        try {
            // Re-check landlord ownership just in case
            $checkStmt = $this->db->prepare("SELECT 1 FROM user_tbl WHERE user_id = :user_id AND landlord_id = :landlord_id");
            $checkStmt->execute([':user_id' => $tenantUserId, ':landlord_id' => $this->landlordId]);
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("You are not authorized to edit this tenant.");
            }

            $this->db->beginTransaction();

            // --- 1. Update user_tbl ---
            $stmtUser = $this->db->prepare("
                UPDATE user_tbl 
                SET full_name = :full_name, phone_no = :phone_no
                WHERE user_id = :user_id
            ");
            $stmtUser->execute([
                ':full_name' => $data['full_name'],
                ':phone_no' => $this->normalizePhone($data['phone_no'] ?? ''),
                ':user_id' => $tenantUserId
            ]);

            // --- 2. Update tenant_info_tbl ---
            
            // Handle file uploads
            $paths = [];
            
            // ⭐ FIX 1: Corrected 'tenant_birth_certificate' to 'birth_certificate'
            //          and 'tenant_proof_of_income' to 'proof_of_income'
            //          to match the form fields in editTenant.php
            $fileFields = ['tenant_id_photo', 'birth_certificate', 'tenant_photo', 'proof_of_income'];
            
            foreach ($fileFields as $field) {
                if (isset($files[$field]) && $files[$field]['error'] === UPLOAD_ERR_OK) {
                    // This is a new file upload. Handle it.
                    // The _handleUpload function will delete the old file.
                    // Note: 'tenant_id_photo' field name maps to 'id_photo' db column.
                    // We must pass the correct DB column name to _handleUpload.
                    
                    $dbColumn = $field;
                    if ($field === 'tenant_id_photo') {
                         $dbColumn = 'id_photo';
                    }

                    $paths[$field] = $this->_handleUpload($files[$field], $tenantUserId, $dbColumn);

                } else {
                    // No new file uploaded. Keep the existing file path.
                    // The field name from $fileFields matches the 'existing_' hidden input name.
                    $paths[$field] = $data['existing_' . $field] ?? null;
                }
            }
            
            // Calculate age
            $age = !empty($data['birthdate']) ? $this->calculateAge($data['birthdate']) : null;

            // Prepare the UPDATE statement for tenant_info_tbl
            $stmtTenant = $this->db->prepare("
                UPDATE tenant_info_tbl
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
                    monthly_rent = :monthly_rent,
                    emergency_name = :emergency_name,
                    emergency_contact = :emergency_contact,
                    relationship = :relationship
                WHERE user_id = :user_id
            ");
            
            $stmtTenant->execute([
                ':birthdate' => $data['birthdate'] ?: null,
                ':age' => $age,
                ':gender' => $data['gender'] ?: null,
                ':email' => $data['email'] ?: null,
                ':id_type' => $data['id_type'] ?: null,
                ':id_number' => $data['id_number'] ?: null,
                ':id_photo' => $paths['tenant_id_photo'], // Maps to :id_photo
                
                // ⭐ FIX 2: Corrected array key from $paths['tenant_birth_certificate']
                //          to $paths['birth_certificate'] to match the $fileFields array
                ':birth_certificate' => $paths['birth_certificate'], // Maps to :birth_certificate
                
                ':tenant_photo' => $paths['tenant_photo'], // Maps to :tenant_photo
                ':occupation' => $data['occupation'] ?: null,
                ':employer_name' => $data['employer_name'] ?: null,
                ':monthly_income' => $data['monthly_income'] ?: null,
                ':proof_of_income' => $paths['proof_of_income'], // Maps to :proof_of_income
                ':monthly_rent' => $data['monthly_rent'] ?: null,
                ':emergency_name' => $data['emergency_name'] ?: null,
                ':emergency_contact' => $data['emergency_contact'] ?: null,
                ':relationship' => $data['relationship'] ?: null,
                ':user_id' => $tenantUserId
            ]);

            // --- 3. Update Password (if provided) ---
            if (!empty($data['password'])) {
                $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmtPass = $this->db->prepare("
                    UPDATE user_tbl SET password = :password
                    WHERE user_id = :user_id AND landlord_id = :landlord_id
                ");
                $stmtPass->execute([
                    ':password' => $hashed,
                    ':user_id' => $tenantUserId,
                    ':landlord_id' => $this->landlordId
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION["landlord_error"] = "Error updating tenant: " . $e->getMessage();
            return false;
        }
    }
    
    /* ------------------------------------------------------------
     * ❌ DELETE FUNCTION
     * ------------------------------------------------------------ */

    /**
     * Deletes a tenant's full profile (user, info, files, and directory).
     * This is used by the landlord's "Manage Tenants" page.
     */
    public function deleteTenant(int $tenantUserId): bool {
        try {
            // 1. Check if tenant has active leases
            $checkStmt = $this->db->prepare("
                SELECT 1 FROM lease_tbl 
                WHERE user_id = :user_id AND lease_status = 'Active'
            ");
            $checkStmt->execute([':user_id' => $tenantUserId]);
            if ($checkStmt->fetch()) {
                throw new Exception("Cannot delete tenant. They are still on an active lease. Please terminate their lease first.");
            }
            
            // 2. Re-check landlord ownership just in case
            $checkStmt = $this->db->prepare("SELECT 1 FROM user_tbl WHERE user_id = :user_id AND landlord_id = :landlord_id");
            $checkStmt->execute([':user_id' => $tenantUserId, ':landlord_id' => $this->landlordId]);
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("You are not authorized to delete this tenant.");
            }

            $this->db->beginTransaction();

            // 3. Get all file paths for this tenant
            $stmt = $this->db->prepare("SELECT * FROM tenant_info_tbl WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $tenantUserId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Delete files and directory
            if ($info) {
                foreach ($info as $key => $value) {
                    // Check if the value is a string and looks like a file path
                    if (is_string($value) && strpos($value, 'uploads/tenant_docs/') === 0) {
                        $fullPath = "../" . $value;
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                }
                // Delete the user's directory
                $userDir = "../uploads/tenant_docs/user_{$tenantUserId}";
                if (is_dir($userDir)) {
                    // Try to remove files first if any are left (e.g., non-db tracked)
                    // Note: A more robust solution would recursively delete, but rmdir works if unlink was successful
                    @rmdir($userDir); 
                }
            }

            // 5. Delete user from database (cascading delete will handle roles, info, etc.)
            $stmt = $this->db->prepare("DELETE FROM user_tbl WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $tenantUserId]);

            if ($stmt->rowCount() > 0) {
                $this->db->commit();
                return true;
            } else {
                throw new Exception("Could not find tenant to delete.");
            }

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $_SESSION["landlord_error"] = "Error deleting tenant: " . $e->getMessage();
            return false;
        }
    }

} // <-- This is the final closing brace for the class
?>