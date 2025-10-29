<?php
session_start();
require_once "dbConnect.php";
require_once "PropertyManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$db = (new Database())->getConnection();
$propertyManager = new PropertyManager($db, $userId);

// ✅ Add new property
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_property"])) {
    $name = trim($_POST["property_name"]);
    $location = trim($_POST["location"]);
    $propertyManager->addProperty($name, $location);
}

// ✅ Delete property
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $propertyManager->deleteProperty($id);
    $_SESSION["success"] = "Property deleted successfully!";
    header("Location: manageProperties.php");
    exit;
}

// ✅ Fetch landlord's properties
$properties = $propertyManager->getProperties();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Properties</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js" defer></script> 
    <script src="../assets/admin.js" defer></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

<!-- Header -->
<?php include '../assets/header.php'; ?>

<main class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-10">
  <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
    
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-2 mb-6">
      🏠 <span>Manage Properties</span>
    </h1>

    <!-- Alerts -->
    <?php if (!empty($_SESSION["error"])): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 text-sm">
        <?= htmlspecialchars($_SESSION["error"]) ?>
      </div>
      <?php unset($_SESSION["error"]); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION["success"])): ?>
      <script>
        Swal.fire({
          icon: "success",
          title: "Success!",
          text: "<?= addslashes($_SESSION['success']) ?>",
          confirmButtonColor: "#2563eb",
          timer: 1800,
          showConfirmButton: false
        });
      </script>
      <?php unset($_SESSION["success"]); ?>
    <?php endif; ?>

    <!-- Add Property Form -->
    <form method="POST" class="space-y-4 mb-8 bg-slate-50 p-6 rounded-xl border border-slate-200">
      <div>
        <label for="property_name" class="block text-sm font-semibold text-slate-700 mb-1">Property Name</label>
        <input type="text" id="property_name" name="property_name"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="e.g., Sunset Apartments" required>
      </div>

      <div>
        <label for="location" class="block text-sm font-semibold text-slate-700 mb-1">Location</label>
        <input type="text" id="location" name="location"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="e.g., Lipa City, Batangas" required>
      </div>

      <div class="flex justify-end">
        <button type="submit" name="add_property"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
          ➕ Add Property
        </button>
      </div>
    </form>

    <!-- Properties List -->
    <h2 class="text-2xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
      🧱 Your Properties
    </h2>

    <?php if (count($properties) > 0): ?>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm text-slate-700">
          <thead class="bg-slate-100 border-b border-slate-300 text-slate-800 font-semibold">
            <tr>
              <th class="p-3 text-left">Property Name</th>
              <th class="p-3 text-left">Location</th>
              <th class="p-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($properties as $p): ?>
              <tr class="hover:bg-blue-50 transition-all duration-150 border-b border-slate-200">
                <td class="p-3 font-medium text-blue-700">
                  <a href="manageUnit.php?property_id=<?= $p['property_id'] ?>" class="hover:underline">
                    <?= htmlspecialchars($p["property_name"]) ?>
                  </a>
                </td>
                <td class="p-3"><?= htmlspecialchars($p["location"]) ?></td>
                <td class="p-3 text-center space-x-2">
                  <a href="updateProperties.php?id=<?= $p['property_id'] ?>" 
                     class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                    Edit
                  </a>
                  <span class="text-slate-400">|</span>
                  <a href="#" 
                     class="delete-btn text-red-600 hover:text-red-800 font-medium hover:underline"
                     data-id="<?= $p['property_id'] ?>">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div class="text-center py-10">
        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <p class="text-slate-500 font-medium text-lg mb-1">No properties yet</p>
        <p class="text-slate-400 text-sm">Add your first property using the form above.</p>
      </div>
    <?php endif; ?>

    <!-- Back to Dashboard -->
    <div class="mt-8 text-center">
      <a href="dashboard/landlord_dashboard.php"
         class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-all duration-150">
        ← Back to Dashboard
      </a>
    </div>
  </div>
</main>

<!-- Footer -->
<?php include '../assets/footer.php'; ?>

<!-- SweetAlert Delete Confirmation -->
<script>
document.querySelectorAll('.delete-btn').forEach(button => {
  button.addEventListener('click', e => {
    e.preventDefault();
    const propertyId = button.getAttribute('data-id');

    Swal.fire({
      title: "Delete Property?",
      text: "This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc2626",
      cancelButtonColor: "#6b7280",
      confirmButtonText: "Yes, delete it",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `?delete=${propertyId}`;
      }
    });
  });
});
</script>

</body>
</html>
