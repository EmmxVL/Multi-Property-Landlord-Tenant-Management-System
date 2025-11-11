<?php


class Auth {
    /** @var PDO */
    private PDO $db;

    /** @var bool */
    private bool $isAjax;

    /** @var string */
    private string $errorLocation; // For redirecting errors

    // *** FIXED: Changed default error location to the user login page ***
    public function __construct(PDO $db, bool $isAjax = false, string $errorLocation = '../login_page_user.php') {
        $this->db = $db;
        $this->isAjax = $isAjax;
        $this->errorLocation = $errorLocation; // Set error redirect path
    }

    /* ---------------------------------------------------------------------- */
    /* Utility methods                                                       */
    /* ---------------------------------------------------------------------- */

    /**
     * Sends an error response.
     * @param string $message The error message to send.
     */
    private function sendError(string $message): void {
        if ($this->isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $_SESSION['login_error'] = $message;
            // Use the dynamic error location set in the constructor
            header("Location: {$this->errorLocation}");
        }
        exit;
    }

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

    /* ---------------------------------------------------------------------- */
    /* Core Authentication Logic                                             */
    /* ---------------------------------------------------------------------- */

    public function getUserByPhone(string $phone): ?array {
        $stmt = $this->db->prepare("SELECT * FROM user_tbl WHERE phone_no = :phone LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function verifyPassword(array $user, string $password): bool {
        $dbPassword = $user['password'] ?? '';
        $isValid = false;
        $needsRehash = false;

        if (password_verify($password, $dbPassword)) {
            $isValid = true;
            if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT)) {
                $needsRehash = true;
            }
        } elseif ($password === $dbPassword && !password_get_info($dbPassword)['algo']) {
            // old unhashed password support
            $isValid = true;
            $needsRehash = true;
        }

        if ($isValid && $needsRehash) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $this->db->prepare("UPDATE user_tbl SET password = :hash WHERE user_id = :id");
            $update->execute([':hash' => $newHash, ':id' => $user['user_id']]);
        }

        return $isValid;
    }

    public function getUserRoles(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.role_name 
            FROM user_role_tbl ur
            JOIN role_tbl r ON ur.role_id = r.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return !empty($roles) ? $roles : ['Tenant']; // Default role
    }

    private function determineRedirect(array $roles): string {
        if (count($roles) > 1) {
            $_SESSION["roles"] = $roles;
            // Note: This file needs to exist in the root directory
            return '../role_selection.php'; 
        }

        $_SESSION["role"] = $roles[0];
        // Paths are relative to this file (Auth.php, which is in PHP/)
        switch (strtolower($roles[0])) {
            case 'admin':
                return 'dashboard/admin_dashboard.php';
            case 'landlord':
                return 'dashboard/landlord_dashboard.php';
            case 'tenant':
                return 'dashboard/tenant_dashboard.php';
            default:
                // This will use the errorLocation set in the constructor
                $this->sendError('Invalid role configuration: ' . $roles[0]);
        }
        return '';
    }

    /* ---------------------------------------------------------------------- */
    /* Public: Main login function                                           */
    /* ---------------------------------------------------------------------- */

    /**
     * Handles the full login process.
     * @param string $phone The user's phone number.
     * @param string $password The user's password.
     * @param array $allowedRoles A list of roles allowed to log in via this call.
     */
     public function login(string $phone, string $password, array $allowedRoles = []): void {
        $normalizedPhone = $this->normalizePhone($phone);

        $user = $this->getUserByPhone($normalizedPhone);
        if (!$user) {
            $this->sendError('No account found with that phone number.');
        }

        // *** THIS IS THE SECURITY FIX ***
        // Checks the 'status' column before doing anything else.
        if ($user['status'] !== 'approved') {
            if ($user['status'] === 'pending') {
                $this->sendError('Your application is still pending approval.');
            } elseif ($user['status'] === 'rejected') {
                $this->sendError('Your application has been rejected. Please contact support.');
            } else {
                // This completes the line that was cut off
                $this->sendError('Your account is not active. Please contact support.');
            }
        }
        // *** END OF FIX ***

        if (!$this->verifyPassword($user, $password)) {
            $this->sendError('Invalid password.');
        }

        $roles = $this->getUserRoles((int)$user["user_id"]);

        // Role-based login page check
        if (!empty($allowedRoles)) {
            // Find roles the user HAS that are ALSO in the ALLOWED list
            $commonRoles = array_intersect(
                array_map('strtolower', $roles),
                array_map('strtolower', $allowedRoles)
            );

            if (empty($commonRoles)) {
                // User has roles, but none match the allowed roles for this login page
                $this->sendError('Access Denied. Please use the correct login page for your role.');
            }
        }
        // *** END CHECK ***

        // Ensure session is started before using $_SESSION
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["full_name"] = $user["full_name"];

        $redirect = $this->determineRedirect($roles);

        if ($this->isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => ltrim($redirect, './')]);
        } else {
            header("Location: $redirect");
        }
        exit;
    }
}