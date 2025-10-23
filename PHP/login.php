<?php
session_start();
require_once "dbConnect.php";

$database = new Database();
$db = $database->getConnection();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"]);
    $password = trim($_POST["password"]);

    // Check user existence
    $query = "SELECT * FROM user_tbl WHERE phone_no = :phone";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":phone", $phone);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ✅ Supports both hashed and plain passwords
        $dbPassword = $user["password"];
        $isValid = password_verify($password, $dbPassword) || $password === $dbPassword;

        if ($isValid) {
            // Fetch user roles
            $roleQuery = "SELECT r.role_name FROM user_role_tbl ur
                          JOIN role_tbl r ON ur.role_id = r.role_id
                          WHERE ur.user_id = :user_id";
            $roleStmt = $db->prepare($roleQuery);
            $roleStmt->bindParam(":user_id", $user["user_id"]);
            $roleStmt->execute();
            $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["full_name"] = $user["full_name"];

            // If multiple roles (e.g., Admin + Landlord)
            if (count($roles) > 1) {
                $_SESSION["roles"] = $roles;
                header("Location: role_selection.php");
                exit;
            } else {
                $_SESSION["role"] = $roles[0];

                // Redirect based on role
                switch ($roles[0]) {
                    case "Admin":
                        header("Location: admin.php");
                        break;
                    case "Landlord":
                        header("Location: landlord.php");
                        break;
                    case "Tenant":
                        header("Location: tenant.php");
                        break;
                    default:
                        $error = "Invalid role.";
                }
                exit;
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that phone number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Login</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
