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
    $latitude = !empty($_POST["latitude"]) ? (float)$_POST["latitude"] : null;
    $longitude = !empty($_POST["longitude"]) ? (float)$_POST["longitude"] : null;

    if ($propertyManager->addProperty($name, $location, $latitude, $longitude)) {
        $_SESSION["success"] = "Property added successfully!";
    } else {
        $_SESSION["error"] = "Failed to add property. Please try again.";
    }

    header("Location: manageProperties.php");
    exit;
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
  <title>Manage Properties | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script>
  <script src="../assets/admin.js" defer></script>
  <!-- ✅ Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    #map { height: 320px; border-radius: 12px; z-index: 0; }
  </style>
</head>

<?php include '../assets/header.php'; ?>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- Main Section -->
  <main class="flex-grow flex justify-center py-12 px-6">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-5xl rounded-3xl shadow-lg border border-slate-200 p-10 transition-all duration-300 hover:shadow-2xl">

      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-extrabold text-blue-900 flex items-center gap-2">
            🏠 <span>Manage Properties</span>
          </h1>
          <p class="text-slate-600 text-sm mt-1">Add, view, and manage your listed properties with ease.</p>
        </div>
        <a href="dashboard/landlord_dashboard.php" class="text-sm font-medium text-blue-700 hover:text-indigo-700 hover:underline transition">
          ← Back to Dashboard
        </a>
      </div>

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
      <form method="POST" class="bg-white/60 backdrop-blur-sm border border-slate-200 rounded-2xl p-6 shadow-sm mb-10">
        <h2 class="text-xl font-bold text-slate-800 mb-5 flex items-center gap-2">➕ Add New Property</h2>

        <div class="grid md:grid-cols-2 gap-5">
  <div>
    <label for="property_name" class="block text-sm font-medium text-slate-700 mb-1">Property Name</label>
    <input type="text" id="property_name" name="property_name"
           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
           placeholder="e.g., Sunset Apartments" required>
  </div>
  <div>
    <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location</label>
    <input type="text" id="location" name="location"
           class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all duration-200"
           placeholder="e.g., Lipa City, Batangas" required>
  </div>
  
  <!-- ✅ Leaflet Map Picker - Now properly spans both columns -->
  <div class="md:col-span-2 mt-4">
    <div class="flex justify-center">
      <div class="w-full max-w-md">
        <label class="block text-sm font-medium text-slate-700 mb-2 text-center">📍 Pin Location on Map</label>
        <div id="map" class="w-full rounded-2xl border border-slate-300" style="height: 400px;"></div>
      </div>
    </div>
  </div>

  <!-- Hidden Lat/Lng Inputs -->
  <input type="hidden" id="latitude" name="latitude">
  <input type="hidden" id="longitude" name="longitude">
</div>


        <div class="flex justify-center mt-6">
          <button type="submit" name="add_property"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
            ➕ Add Property
          </button>
        </div>
      </form>

      <!-- Properties List -->
      <h2 class="text-2xl font-extrabold text-blue-900 mb-6 flex items-center gap-2">🏢 Your Properties</h2>

      <?php if (count($properties) > 0): ?>
        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-sm text-slate-700 rounded-xl overflow-hidden shadow-sm">
            <thead class="bg-blue-50 border-b border-blue-100 text-slate-800 font-semibold">
              <tr>
                <th class="p-3 text-left">Property Name</th>
                <th class="p-3 text-left">Location</th>
                <th class="p-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white/70">
              <?php foreach ($properties as $p): ?>
                <tr class="hover:bg-indigo-50 transition-all duration-200 border-b border-slate-200">
                  <td class="p-3 font-medium text-blue-700">
                    <a href="manageUnit.php?property_id=<?= $p['property_id'] ?>" class="hover:underline">
                      <?= htmlspecialchars($p["property_name"]) ?>
                    </a>
                  </td>
                  <td class="p-3"><?= htmlspecialchars($p["location_name"]) ?></td>
                  <td class="p-3 text-center space-x-2">
                    <a href="updateProperties.php?id=<?= $p['property_id'] ?>"
                       class="text-blue-600 hover:text-blue-800 font-medium hover:underline">Edit</a>
                    <span class="text-slate-400">|</span>
                    <a href="#"
                       class="delete-btn text-red-600 hover:text-red-800 font-medium hover:underline"
                       data-id="<?= $p['property_id'] ?>">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <p class="text-slate-500 font-medium text-lg mb-1">No properties yet</p>
          <p class="text-slate-400 text-sm">Add your first property using the form above.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

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

  <!-- ✅ Leaflet JS -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
  let map = L.map('map').setView([13.940, 121.163], 13); // Default to Lipa City

  // OpenStreetMap base layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  let marker;

  // Add marker on map click
  map.on('click', function(e) {
    const { lat, lng } = e.latlng;

    // Remove existing marker if any
    if (marker) map.removeLayer(marker);

    // Add new marker
    marker = L.marker([lat, lng]).addTo(map);

    // Update hidden input fields
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
  });
  </script>

</body>
</html>
