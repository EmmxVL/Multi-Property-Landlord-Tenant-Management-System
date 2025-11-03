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

<main class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 p-6">
  <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-lg border border-slate-200 max-w-3xl w-full p-10 text-center">
    <h1 class="text-3xl font-bold text-blue-900 mb-4">Developers</h1>
    <p class="text-slate-700 text-sm leading-relaxed">
      Unitly was developed by a team of passionate BSU students aiming to create an innovative housing management platform.
      Our goal is to bridge the gap between traditional property management and modern digital solutions.
    </p>
    <div class="mt-5 text-sm text-slate-600">
      <p><strong>Lead Developer:</strong> Ralph Emmerson Lucero</p>
      <p><strong>UI/UX & Frontend:</strong> Sean Del Rosario</p>
      <p><strong>Backend & Database:</strong> Filemon Mendoza</p>
    </div>
    <div class="text-center mt-6">
  <a href="<?= $dashboardLink ?>" 
     class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl transition">
     ← Back to Dashboard
  </a>
</div>

  </div>
</main>
<?php include '../assets/footer.php'; ?>
