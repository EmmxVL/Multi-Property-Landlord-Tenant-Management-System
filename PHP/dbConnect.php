<?php
class Database {
    // 🔑 Your Supabase project URL and API key
    private $baseUrl = "https://twclndmhuifqjqmgpyfs.supabase.co/rest/v1/";
    private $apiKey = "eyJpc3MiOiJzdXBhYmFzZSIsInJlZiIsInJlZiI6InR3Y2xuZG1odWlmcWpxbWdweWZzIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE1NDkxNjcsImV4cCI6MjA3NzEyNTE2N30";

    // Supabase connection placeholder
    public $supabase;
    private $headers;

    public function __construct() {
        // ✅ Initialize headers
        $this->headers = [
            "apikey: {$this->apiKey}",
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];

        // ✅ Simulate a "connection" by verifying API works
        $this->supabase = true;
    }

    // 🔧 Internal request handler
    private function request($method, $table, $data = null, $query = "?select=*") {
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

    // 🧩 SELECT * FROM table
    public function getAll($table) {
        $res = $this->request("GET", $table);
        if ($res["status"] >= 200 && $res["status"] < 300) {
            return $res["data"];
        } else {
            echo "⚠️ Error fetching data from $table (HTTP {$res['status']})<br>";
            return [];
        }
    }

    // ➕ INSERT row(s)
    public function insert($table, $data) {
        return $this->request("POST", $table, $data);
    }

    // ✏️ UPDATE with filter (e.g., "id=eq.1")
    public function update($table, $data, $filter) {
        return $this->request("PATCH", $table, $data, "?$filter");
    }

    // ❌ DELETE with filter (e.g., "id=eq.1")
    public function delete($table, $filter) {
        return $this->request("DELETE", $table, null, "?$filter");
    }
}
?>
