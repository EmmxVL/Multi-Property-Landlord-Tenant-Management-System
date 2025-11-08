<?php
session_start();
require_once "dbConnect.php";
require_once "UnitManager.php";

// ✅ Only landlords can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: ../login_page.php");
    exit;
}

$userId = $_SESSION["user_id"];
$propertyId = isset($_GET["property_id"]) ? (int)$_GET["property_id"] : 0;
$unitId = isset($_GET["unit_id"]) ? (int)$_GET["unit_id"] : 0;

if ($propertyId <= 0 || $unitId <= 0) {
    echo "Invalid request.";
    exit;
}

$db = (new Database())->getConnection();
$unitManager = new UnitManager($db, $userId);

// ✅ Fetch unit info
$stmt = $db->prepare("SELECT * FROM unit_tbl WHERE unit_id = :unit_id AND user_id = :user_id");
$stmt->execute([':unit_id' => $unitId, ':user_id' => $userId]);
$unit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$unit) {
    echo "Unit not found or unauthorized.";
    exit;
}

// ✅ Handle Update Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_unit"])) {
    $unitName = trim($_POST["unit_name"]);
    $rent = (int)$_POST["rent"];

    if ($unitManager->updateUnit($unitId, $unitName, $rent)) {
        $_SESSION["success"] = "Unit updated successfully!";
        header("Location: manageUnit.php?property_id=$propertyId");
        exit;
    } else {
        $_SESSION["error"] = "Failed to update unit.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Unit | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script>
</head>

<?php include '../assets/header.php'; ?>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <main class="flex-grow flex items-center justify-center py-12 px-4">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-md rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-2xl">
      
      <!-- Title -->
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m-7 0h14" />
          </svg>
        </div>
        <h1 class="text-3xl font-extrabold text-blue-900">Update Unit</h1>
        <p class="text-slate-500 text-sm mt-1">Edit and save your unit details below.</p>
      </div>

      <!-- Alert -->
      <?php if (!empty($_SESSION["error"])): ?>
        <script>
          Swal.fire({
            icon: "error",
            title: "Error!",
            text: <?= json_encode($_SESSION["error"]) ?>,
            confirmButtonColor: "#2563eb"
          });
        </script>
        <?php unset($_SESSION["error"]); ?>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Unit Name</label>
          <input type="text" name="unit_name" value="<?= htmlspecialchars($unit['unit_name']) ?>"
                 class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                 required>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Rent Amount</label>
          <input type="number" name="rent" value="<?= htmlspecialchars($unit['rent']) ?>"
                 class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
                 required>
        </div>

        <div class="flex justify-between items-center pt-4">
          <a href="manageUnit.php?property_id=<?= $propertyId ?>"
             class="text-sm text-blue-600 hover:text-blue-800 hover:underline transition">
            ← Back to Units
          </a>
          <button type="submit" name="update_unit"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
            💾 Save Changes
          </button>
        </div>
      </form>
    </div>
  </main>

  <?php include '../assets/footer.php'; ?>

</body>
</html>
