<?php
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;
$editable = isset($_GET['editable']) && $_GET['editable'] === 'true';
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Property Map</title>

  <!-- Leaflet CSS & JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <style>
    html, body { margin: 0; padding: 0; height: 100%; }
    #map { width: 100%; height: 100%; border-radius: 12px; }
    #saveBtn {
      display: none;
      position: absolute;
      top: 10px; left: 10px;
      background: #2563eb;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 8px;
      cursor: pointer;
      z-index: 1000;
    }
  </style>
</head>
<body>
  <button id="saveBtn">💾 Save Location</button>
  <div id="map"></div>

  <script>
    const lat = <?= $lat ?> || 14.5995;   // Default Manila
    const lng = <?= $lng ?> || 120.9842;
    const editable = <?= $editable ? 'true' : 'false' ?>;
    const propertyId = <?= $property_id ?>;

    // Initialize map
    const map = L.map('map').setView([lat, lng], 15);

    // OpenStreetMap free tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Marker
    const marker = L.marker([lat, lng], { draggable: editable }).addTo(map);

    // Enable save button if editable
    if (editable) {
      const saveBtn = document.getElementById('saveBtn');
      saveBtn.style.display = 'block';

      // Move marker when clicking on map
      map.on('click', function(e) {
        marker.setLatLng(e.latlng);
      });

      // Save updated coordinates
      saveBtn.addEventListener('click', async () => {
        const { lat, lng } = marker.getLatLng();

        try {
          const response = await fetch('updateProperties.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `property_id=${propertyId}&latitude=${lat}&longitude=${lng}`
          });

          const text = await response.text();
          alert(text || 'Location updated successfully.');
        } catch (err) {
          alert('Error saving location.');
          console.error(err);
        }
      });
    }
  </script>
</body>
</html>
