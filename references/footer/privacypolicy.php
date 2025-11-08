<?php
session_start();

// ✅ Check if user is logged in
if (!isset($_SESSION['role_name'])) {
    header("Location: ../login_page.php");
    exit;
}

// ✅ Auto-adjust access depending on session role
switch ($_SESSION['role_name']) {
    case 'Landlord':
        $dashboardLink = '../PHP/dashboard/landlord_dashboard.php';
        break;
    case 'Tenant':
        $dashboardLink = '../PHP/dashboard/tenant_dashboard.php';
        break;
    case 'Admin':
        $dashboardLink = '../PHP/dashboard/admin_dashboard.php';
        break;
    default:
        // Fallback if something goes wrong
        header("Location: ../login_page.php");
        exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>footer</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js" defer></script>
</head>

<main class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-lg mt-10">
  <a href="dashboard/tenant_dashboard.php" class="text-blue-600 hover:underline mb-4 inline-block">← Back to Dashboard</a>
  <h1 class="text-3xl font-bold mb-4 text-slate-800">Privacy Policy</h1>
  <p class="text-slate-600 mb-4">
    At Unitly, we value your privacy. This Privacy Policy explains how we collect, use, and protect your personal information 
    when you use our platform.
  </p>
  <ul class="list-disc ml-6 text-slate-600 space-y-2">
    <li>We collect basic personal information (name, contact, proof documents) only for rental and verification purposes.</li>
    <li>Your data is securely stored and never shared with third parties without your consent.</li>
    <li>Cookies are used to enhance user experience and improve our services.</li>
    <li>You have the right to access, correct, or request deletion of your personal data.</li>
  </ul>
  <div class="text-center mt-6">
  <a href="<?= $dashboardLink ?>" 
     class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl transition">
     ← Back to Dashboard
  </a>
</div>


</main>
<?php include 'assets/footer.php'; ?>
