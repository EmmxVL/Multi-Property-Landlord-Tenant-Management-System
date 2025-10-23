<?php
session_start();
require_once "dbConnect.php";

// ✅ Restrict access to Admins only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST["full_name"]);
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);

    if (empty($fullName) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Check if phone number already exists
            $checkQuery = "SELECT * FROM user_tbl WHERE phone_no = :phone";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(":phone", $phone);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $error = "A user with that phone number already exists.";
            } else {
                // ✅ Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert into user_tbl
                $insertUser = "INSERT INTO user_tbl (full_name, password, phone_no)
                               VALUES (:full_name, :password, :phone)";
                $stmt = $db->prepare($insertUser);
                $stmt->bindParam(":full_name", $fullName);
                $stmt->bindParam(":password", $hashedPassword);
                $stmt->bindParam(":phone", $phone);
                $stmt->execute();

                // Get the last inserted user_id
                $userId = $db->lastInsertId();

                // Assign Landlord role (role_id = 1)
                $insertRole = "INSERT INTO user_role_tbl (role_id, user_id, role_type)
                               VALUES (1, :user_id, '1')";
                $stmt2 = $db->prepare($insertRole);
                $stmt2->bindParam(":user_id", $userId);
                $stmt2->execute();

                $success = "✅ Landlord account created successfully!";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Create Landlord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4 text-center">Admin Dashboard</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Create Landlord Account</h5>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Landlord</button>
            </form>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>
</body>
</html>
