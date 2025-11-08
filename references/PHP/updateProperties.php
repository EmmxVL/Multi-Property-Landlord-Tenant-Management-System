<?php
session_start();
require_once "dbConnect.php";
require_once "PropertyManager.php";

// ✅ Restrict access to landlords only
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

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["property_name"]);
    $location = trim($_POST["location"]);
    $latitude = isset($_POST["latitude"]) ? floatval($_POST["latitude"]) : null;
    $longitude = isset($_POST["longitude"]) ? floatval($_POST["longitude"]) : null;

    if ($propertyManager->updateProperty($id, $name, $location, $latitude, $longitude)) {
        header("Location: manageProperties.php");
        exit;
    } else {
        echo "<script>alert('Failed to update property. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unitly - Edit Property</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        #map { width: 100%; height: 320px; border-radius: 12px; border: 1px solid #d1d5db; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    <!-- Header -->
    <?php include '../assets/header.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-grow max-w-3xl mx-auto px-6 py-12 w-full">
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                ✏️ Edit Property
            </h1>

            <form method="POST" class="space-y-6">
                <!-- Property Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Property Name</label>
                    <input type="text" name="property_name"
                        value="<?= htmlspecialchars($property["property_name"]) ?>"
                        class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                </div>

                <!-- Location Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
                    <input type="text" name="location"
                        value="<?= htmlspecialchars($property["location_name"] ?? '') ?>"
                        class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                </div>

                <!-- Hidden Coordinates -->
                <input type="hidden" name="latitude" id="latitude" value="<?= $property['latitude'] ?? '' ?>">
                <input type="hidden" name="longitude" id="longitude" value="<?= $property['longitude'] ?? '' ?>">

                <!-- Map -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">📍 Set Location on Map</label>
                    <div id="map"></div>
                    <p class="text-xs text-slate-500 mt-2">
                        Click anywhere or drag the marker to update the coordinates.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="manageProperties.php"
                       class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium px-5 py-2 rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../assets/footer.php'; ?>

    <!-- Leaflet Map Script -->
    <script>
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        const defaultLat = parseFloat(latInput.value) || 14.5995;  // Manila default
        const defaultLng = parseFloat(lngInput.value) || 120.9842;

        const map = L.map('map').setView([defaultLat, defaultLng], 15);

        // Add OpenStreetMap tiles (free)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Add marker
        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        // Update hidden fields when marker moves
        marker.on('dragend', function (e) {
            const latlng = e.target.getLatLng();
            latInput.value = latlng.lat.toFixed(6);
            lngInput.value = latlng.lng.toFixed(6);
        });

        // When clicking on map
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);
        });
    </script>

</body>
</html>
