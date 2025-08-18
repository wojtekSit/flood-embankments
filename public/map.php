<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$stmt = $pdo->query("
    SELECT object_type, issue_type, gps_lat, gps_lng, damage_level, is_closed, created_at 
    FROM reports 
    WHERE gps_lat IS NOT NULL AND gps_lng IS NOT NULL
");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mapa zgłoszeń</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <link rel="stylesheet" href="/public/app.css?v=1">
  <style>
    html,body{margin:0;height:100%}
    #map{height:100%;width:100%}
    /* custom popup style */
    .leaflet-popup-content{
      font-size: 0.95rem;
      line-height: 1.4;
      max-width: 260px;
      margin: 6px 0;
    }
    .popup-card{
      background: var(--surface);
      border: 1px solid #1f2330;
      border-radius: var(--radius);
      padding: 12px 14px;
      color: var(--text);
      font-size: .9rem;
    }
    .popup-card b{display:block; font-size:1rem; margin-bottom:4px;}
    .popup-meta{color: var(--muted); font-size:.8rem; margin-top:6px;}
    .leaflet-popup-content-wrapper{
      background:transparent;
      box-shadow:none;
    }
    .leaflet-popup-tip{background:var(--surface);}
    /* return button */
    .back-btn{
      position:absolute;top:12px;left:12px;z-index:1000;
    }
  </style>
</head>
<body>
  <div id="map"></div>
  <a href="dashboard.php" class="back-btn">
    <button class="btn btn--ghost">← Wróć do panelu</button>
  </a>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
  const map = L.map('map').setView([52.0,19.0],6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
  }).addTo(map);

  const reports = <?= json_encode($reports, JSON_UNESCAPED_UNICODE) ?>;

  reports.forEach(r=>{
    const marker = L.marker([r.gps_lat,r.gps_lng]).addTo(map);
    const popup = `
      <div class="popup-card">
        <b>${r.object_type}</b>
        Uszkodzenie: ${r.issue_type}<br>
        Stopień: ${r.damage_level}<br>
        Status: ${parseInt(r.is_closed)===1?'Zamknięte ✅':'Otwarte'}<br>
        <div class="popup-meta">📅 ${r.created_at}</div>
      </div>
    `;
    marker.bindPopup(popup);
  });
  </script>
</body>
</html>
