<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private PDO $db;
    private bool $isAjax;

    public function __construct(PDO $db, bool $isAjax = false) {
        $this->db = $db;
        $this->isAjax = $isAjax;
    }

    /* ---------------------------------------------------------------------- */
    /*  Utility methods                                                       */
    /* ---------------------------------------------------------------------- */

    private function sendError(string $message, string $location = '../login_page.php'): void {
        if ($this->isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $_SESSION['login_error'] = $message;
            header("Location: $location");
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
    /*  Core Authentication Logic                                             */
    /* ---------------------------------------------------------------------- */

    public function getUserByPhone(string $phone): ?array {
        $stmt = $this->db->prepare("SELECT * FROM user_tbl WHERE phone_no = :phone LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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
            $isValid = true;
            $needsRehash = true;
        }

        // Automatically rehash plain or outdated hashes
        if ($isValid && $needsRehash) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $this->db->prepare("UPDATE user_tbl SET password = :hash WHERE user_id = :id");
            $update->execute([':hash' => $newHash, ':id' => $user['user_id']]);
        }

        return $isValid;
    }

    public function getUserRoles(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.role_name FROM user_role_tbl ur
            JOIN role_tbl r ON ur.role_id = r.role_id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return !empty($roles) ? $roles : ['Tenant'];
    }

    private function determineRedirect(array $roles): string {
        if (count($roles) > 1) {
            $_SESSION["roles"] = $roles;
            return '../role_selection.php';
        }

        $_SESSION["role"] = $roles[0];
        switch (strtolower($roles[0])) {
            case 'admin':
                return '../PHP/dashboard/admin_dashboard.php';
            case 'landlord':
                return '../PHP/dashboard/landlord_dashboard.php';
            case 'tenant':
                return '../PHP/dashboard/tenant_dashboard.php';
            default:
                $this->sendError('Invalid role configuration: ' . $roles[0]);
                return '';
        }
    }

    /* ---------------------------------------------------------------------- */
    /*  Public: Main login function                                           */
    /* ---------------------------------------------------------------------- */

    public function login(string $phone, string $password): void {
        $normalizedPhone = $this->normalizePhone($phone);

        $user = $this->getUserByPhone($normalizedPhone);
        if (!$user) {
            $this->sendError('No account found with that phone number.');
        }

        if (!$this->verifyPassword($user, $password)) {
            $this->sendError('Invalid password.');
        }

        $roles = $this->getUserRoles($user["user_id"]);

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
