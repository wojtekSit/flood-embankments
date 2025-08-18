<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

// Pobierz wszystkie zgłoszenia z koordynatami
$stmt = $pdo->query("SELECT object_type, issue_type, gps_lat, gps_lng, damage_level, is_closed, created_at FROM reports WHERE gps_lat IS NOT NULL AND gps_lng IS NOT NULL");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mapa zgłoszeń</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { margin: 0; font-family: sans-serif; }
        #map { height: 100vh; width: 100vw; }
    </style>
</head>
<body>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const map = L.map('map').setView([52.0, 19.0], 6); // środek Polski

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Lista zgłoszeń z PHP → JS
const reports = <?= json_encode($reports, JSON_UNESCAPED_UNICODE) ?>;

reports.forEach(report => {
    const marker = L.marker([report.gps_lat, report.gps_lng]).addTo(map);
    const popup = `
        <b>${report.object_type}</b><br>
        Uszkodzenie: ${report.issue_type}<br>
        Stopień: ${report.damage_level}<br>
        Status: ${parseInt(report.is_closed) === 1 ? 'Zamknięte ✅' : 'Otwarte'}<br>
        Data: ${report.created_at}
    `;
    marker.bindPopup(popup);
});
</script>

</body>
<a href="dashboard.php" style="position:absolute; top:10px; left:10px; z-index:1000;">
    <button style="padding: 6px 12px;">← Wróć do panelu</button>
</a>

</html>
