<?php
require_once "dbConnect.php";

$database = new Database();
$db = $database->getConnection();

$fullName = "System Admin";
$phone = "09999999999";
$password = "admin123"; // You can change this
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Insert into user_tbl
    $query = "INSERT INTO user_tbl (full_name, password, phone_no) 
              VALUES (:full_name, :password, :phone)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":full_name", $fullName);
    $stmt->bindParam(":password", $hashedPassword);
    $stmt->bindParam(":phone", $phone);
    $stmt->execute();

    $userId = $db->lastInsertId();

    // Assign Admin role (role_id = 3)
    $roleQuery = "INSERT INTO user_role_tbl (role_id, user_id)
                  VALUES (3, :user_id)";
    $roleStmt = $db->prepare($roleQuery);
    $roleStmt->bindParam(":user_id", $userId);
    $roleStmt->execute();

    echo "✅ Admin account created successfully with hashed password!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
