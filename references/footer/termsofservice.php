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
<main class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-indigo-50 via-blue-100 to-blue-200 p-6">
  <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-lg border border-slate-200 max-w-4xl w-full p-10">
    <h1 class="text-3xl font-bold text-blue-900 mb-4 text-center">Terms of Service</h1>
    <p class="text-slate-700 text-sm leading-relaxed mb-3">
      By accessing and using Unitly, you agree to the following terms and conditions:
    </p>
    <ul class="list-disc pl-6 text-slate-700 text-sm space-y-2">
      <li>Users must provide accurate and updated information at all times.</li>
      <li>Landlords are responsible for maintaining property records and payment accuracy.</li>
      <li>Tenants must comply with all lease agreements and payment schedules.</li>
      <li>Unitly is not liable for disputes between landlords and tenants but serves as a communication tool.</li>
    </ul>
    <p class="text-slate-600 text-xs mt-4">Last updated: October 2025</p>
    <div class="text-center mt-6">
  <a href="<?= $dashboardLink ?>" 
     class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl transition">
     ← Back to Dashboard
  </a>
</div>


  </div>
</main>
<?php include '../assets/footer.php'; ?>
