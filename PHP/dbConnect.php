<?php
class Database {
    private $baseUrl = "https://twclndmhuifqjqmgpyfs.supabase.co/rest/v1/";
    private $apiKey = "eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR3Y2xuZG1odWlmcWpxbWdweWZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE1NDkxNjcsImV4cCI6MjA3NzEyNTE2N30"; // ⚠️ replace privately
    private $headers;

    public function __construct() {
        $this->headers = [
            "apikey: {$this->apiKey}",
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];
    }

    private function request($method, $table, $data = null, $query = "") {
        $url = $this->baseUrl . $table . $query;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            "status" => $httpCode,
            "data" => json_decode($response, true)
        ];
    }

    // === CRUD ===
    public function select($table, $query = "?select=*") {
        return $this->request("GET", $table, null, $query);
    }

    public function insert($table, $data) {
        return $this->request("POST", $table, $data);
    }

    public function update($table, $data, $filter) {
        return $this->request("PATCH", $table, $data, "?$filter");
    }

    public function delete($table, $filter) {
        return $this->request("DELETE", $table, null, "?$filter");
    }
}
?>

