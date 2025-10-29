<?php
session_start();
require_once "dbConnect.php";

// ✅ Restrict access to Landlords only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// ✅ Get tenant ID from URL
$tenantId = (int)($_GET['id'] ?? 0);

// Fetch tenant info
$stmt = $db->prepare("SELECT * FROM tenant_info WHERE user_id = :user_id LIMIT 1");
$stmt->execute([':user_id' => $tenantId]);
$tenantInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenantInfo) {
    $_SESSION['error'] = "Tenant information not found.";
    header("Location: manageTenants.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
<?php include '../assets/header.php'; ?>

<main class="py-10 flex justify-center">
    <div class="max-w-3xl w-full bg-white p-8 rounded-2xl shadow-lg border border-slate-200">

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Tenant Information</h1>

        <div class="space-y-4">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($tenantInfo['full_name']) ?></p>
            <p><strong>Birthdate:</strong> <?= htmlspecialchars($tenantInfo['birthdate']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($tenantInfo['gender']) ?></p>
            <p><strong>Contact Number:</strong> <?= htmlspecialchars($tenantInfo['contact_number']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($tenantInfo['email']) ?></p>
            <p><strong>Occupation:</strong> <?= htmlspecialchars($tenantInfo['occupation']) ?></p>
            <p><strong>Employer Name:</strong> <?= htmlspecialchars($tenantInfo['employer_name']) ?></p>
            <p><strong>Monthly Income:</strong> ₱<?= number_format($tenantInfo['monthly_income'], 2) ?></p>
            <p><strong>Emergency Contact:</strong> <?= htmlspecialchars($tenantInfo['emergency_name']) ?> (<?= htmlspecialchars($tenantInfo['relationship']) ?>) - <?= htmlspecialchars($tenantInfo['emergency_contact']) ?></p>

            <!-- Show uploaded files if available -->
            <?php if (!empty($tenantInfo['id_photo'])): ?>
                <p><strong>ID Photo:</strong> 
                    <a href="../../<?= $tenantInfo['id_photo'] ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                </p>
            <?php endif; ?>
            <?php if (!empty($tenantInfo['birth_certificate'])): ?>
                <p><strong>Birth Certificate:</strong> 
                    <a href="../../<?= $tenantInfo['birth_certificate'] ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                </p>
            <?php endif; ?>
            <?php if (!empty($tenantInfo['tenant_photo'])): ?>
                <p><strong>Tenant Photo:</strong> 
                    <a href="../../<?= $tenantInfo['tenant_photo'] ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                </p>
            <?php endif; ?>
        </div>

        <div class="mt-6">
            <a href="manageTenants.php" class="text-blue-600 hover:text-blue-800 font-medium">← Back to Tenants</a>
        </div>
    </div>
</main>

<?php include '../assets/footer.php'; ?>
</body>
</html>
