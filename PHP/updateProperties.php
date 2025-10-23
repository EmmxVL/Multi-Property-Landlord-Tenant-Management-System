<?php
session_start();
require_once "dbConnect.php";
require_once "PropertyManager.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$propertyManager = new PropertyManager($db, $userId);

$id = (int)($_GET["id"] ?? 0);
$property = $propertyManager->getPropertyById($id);

if (!$property) {
    die("Property not found or unauthorized.");
}

// ✅ Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["property_name"]);
    $location = trim($_POST["location"]);

    if ($propertyManager->updateProperty($id, $name, $location)) {
        header("Location: manageProperties.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Property</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen">
    <div class="max-w-lg mx-auto bg-white mt-10 p-6 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">✏️ Edit Property</h1>

        <form method="POST" class="space-y-4">
            <input type="text" name="property_name" value="<?= htmlspecialchars($property["property_name"]) ?>" class="w-full border p-2 rounded" required>
            <input type="text" name="location" value="<?= htmlspecialchars($property["location"]) ?>" class="w-full border p-2 rounded" required>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Save Changes</button>
        </form>

        <div class="mt-6">
            <a href="manageProperties.php" class="text-blue-600 hover:underline">← Back to Properties</a>
        </div>
    </div>
</body>
</html>
