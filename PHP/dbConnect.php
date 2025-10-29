<?php
class Database {
    private $host = "localhost";
    private $db_name = "landlord_tenant_db(version 3)";
    private $username = "root";
    private $password = "";
    private $conn = null;

    public function getConnection() {
        if ($this->conn) return $this->conn;
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $this->conn;
        } catch (PDOException $e) {
            // In production, log error instead of echo
            die("DB connection failed: " . $e->getMessage());
        }
    }
}
?>