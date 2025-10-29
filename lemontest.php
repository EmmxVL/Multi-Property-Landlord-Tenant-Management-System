<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === Include Database Class ===
$dbPath = __DIR__ . "/PHP/dbConnect.php";

if (file_exists($dbPath)) {
    echo "🔍 Including: $dbPath<br>";
    require_once $dbPath;
    echo "✅ dbConnect.php loaded successfully<br><br>";
} else {
    die("❌ Error: dbConnect.php not found at $dbPath");
}

// === Test Supabase Connection ===
try {
    $db = new Database();
    $table = "user_tbl";

    echo "✅ File loaded, testing Supabase connection...<br>";
    echo "🔍 Checking table: $table<br><br>";

    $response = $db->select($table);

    echo "HTTP Status: " . $response["status"] . "<br><br>";

    if ($response["status"] == 200) {
        echo "✅ Query successful!<br><br>";
        echo "<pre>";
        print_r($response["data"]);
        echo "</pre>";
    } else {
        echo "⚠️ Error fetching data from $table (HTTP {$response["status"]})<br><br>";
        echo "<pre>";
        print_r($response["data"]);
        echo "</pre>";
    }

} catch (Throwable $e) {
    echo "❌ Fatal Error: " . $e->getMessage();
}
?>
