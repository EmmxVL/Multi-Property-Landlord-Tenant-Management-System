<?php
session_start();
require_once "dbConnect.php";
require_once "UnitManager.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$propertyId = isset($_GET["property_id"]) ? (int)$_GET["property_id"] : 0;

if ($propertyId <= 0) {
    echo "Invalid property.";
    exit;
}

$db = (new Database())->getConnection();
$unitManager = new UnitManager($db, $userId);

// ✅ Add new unit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_unit"])) {
    $unitName = trim($_POST["unit_name"]);
    $rent = (int)$_POST["rent"];
    $unitManager->addUnit($propertyId, $unitName, $rent);
    $_SESSION["success"] = "Unit added successfully!";
    header("Location: manageUnit.php?property_id=$propertyId");
    exit;
}

// ✅ Delete unit
if (isset($_GET["delete"])) {
    $unitId = (int)$_GET["delete"];
    $unitManager->deleteUnit($unitId);
    $_SESSION["success"] = "Unit deleted successfully!";
    header("Location: manageUnit.php?property_id=$propertyId");
    exit;
}

// ✅ Fetch all units for this property
$units = $unitManager->getUnitsByProperty($propertyId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Units</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/script.js" defer></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

<!-- Header -->
<?php include '../assets/header.php'; ?>

<main class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-10">
  <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-lg border border-slate-200">
    
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-2 mb-6">
      🏘 <span>Manage Units</span>
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

    <!-- Add Unit Form -->
    <form method="POST" class="space-y-4 mb-8 bg-slate-50 p-6 rounded-xl border border-slate-200">
      <div>
        <label for="unit_name" class="block text-sm font-semibold text-slate-700 mb-1">Unit Name</label>
        <input type="text" id="unit_name" name="unit_name"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="e.g., Room 101" required>
      </div>

      <div>
        <label for="rent" class="block text-sm font-semibold text-slate-700 mb-1">Rent Amount</label>
        <input type="number" id="rent" name="rent"
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
               placeholder="e.g., 10000" required>
      </div>

      <div class="flex justify-end">
        <button type="submit" name="add_unit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
          ➕ Add Unit
        </button>
      </div>
    </form>

    <!-- Units List -->
    <h2 class="text-2xl font-semibold text-slate-800 mb-4 flex items-center gap-2">
      🧩 Units in this Property
    </h2>

    <?php if (count($units) > 0): ?>
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm text-slate-700">
          <thead class="bg-slate-100 border-b border-slate-300 text-slate-800 font-semibold">
            <tr>
              <th class="p-3 text-left">Unit Name</th>
              <th class="p-3 text-left">Rent</th>
              <th class="p-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($units as $u): ?>
              <tr class="hover:bg-blue-50 transition-all duration-150 border-b border-slate-200">
                <td class="p-3 font-medium text-slate-800"><?= htmlspecialchars($u["unit_name"]) ?></td>
                <td class="p-3">₱<?= number_format($u["rent"], 2) ?></td>
                <td class="p-3 text-center space-x-2">
                  <a href="updateUnit.php?unit_id=<?= $u['unit_id'] ?>&property_id=<?= $propertyId ?>" 
                     class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                    Edit
                  </a>
                  <span class="text-slate-400">|</span>
                  <a href="#" 
                     class="delete-btn text-red-600 hover:text-red-800 font-medium hover:underline"
                     data-id="<?= $u['unit_id'] ?>"
                     data-property="<?= $propertyId ?>">
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
        <p class="text-slate-500 font-medium text-lg mb-1">No units yet</p>
        <p class="text-slate-400 text-sm">Add new units using the form above.</p>
      </div>
    <?php endif; ?>

    <!-- Back to Properties -->
    <div class="mt-8 text-center">
      <a href="manageProperties.php"
         class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-all duration-150">
        ← Back to Properties
      </a>
    </div>
  </div>
</main>

<!-- Footer -->
<?php include '../assets/footer.php'; ?>

<!-- ✅ SweetAlert Delete Confirmation -->
<script>
document.querySelectorAll('.delete-btn').forEach(button => {
  button.addEventListener('click', e => {
    e.preventDefault();
    const unitId = button.getAttribute('data-id');
    const propertyId = button.getAttribute('data-property');

    Swal.fire({
      title: "Delete Unit?",
      text: "This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc2626",
      cancelButtonColor: "#6b7280",
      confirmButtonText: "Yes, delete it",
      cancelButtonText: "Cancel"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `?delete=${unitId}&property_id=${propertyId}`;
      }
    });
  });
});
</script>

</body>
</html>
