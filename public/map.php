<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$stmt = $pdo->query("
    SELECT object_type, issue_type, gps_lat, gps_lng, damage_level, is_closed, created_at 
    FROM app_reports 
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
    .leaflet-popup-content{font-size:.95rem;line-height:1.4;max-width:260px;margin:6px 0;}
    .popup-card{background:var(--surface);border:1px solid #1f2330;border-radius:var(--radius);padding:12px 14px;color:var(--text);font-size:.9rem;}
    .popup-card b{display:block;font-size:1rem;margin-bottom:4px;}
    .popup-meta{color:var(--muted);font-size:.8rem;margin-top:6px;}
    .leaflet-popup-content-wrapper{background:transparent;box-shadow:none;}
    .leaflet-popup-tip{background:var(--surface);}
    .back-btn{position:absolute;top:12px;left:12px;z-index:1000;}
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

  // funkcja do wyboru koloru (zielony 1 → czerwony 5)
  function colorFor(level){
    switch(parseInt(level,10)){
      case 1: return "#22c55e"; // zielony
      case 2: return "#84cc16";
      case 3: return "#eab308";
      case 4: return "#f97316";
      case 5: return "#ef4444"; // czerwony
      default: return "#64748b"; // szary fallback
    }
  }

  reports.forEach(r=>{
    const lvl = parseInt(r.damage_level,10);
    const marker = L.circleMarker([r.gps_lat,r.gps_lng],{
      radius: 8,
      color: colorFor(lvl),
      fillColor: colorFor(lvl),
      weight: 2,
      opacity: 1,
      fillOpacity: 0.9
    }).addTo(map);

    const popup = `
      <div class="popup-card">
        <b>${r.object_type}</b>
        Uszkodzenie: ${r.issue_type}<br>
        Stopień: ${lvl}<br>
        Status: ${parseInt(r.is_closed)===1?'Zamknięte ✅':'Otwarte'}<br>
        <div class="popup-meta">📅 ${r.created_at}</div>
      </div>
    `;
    marker.bindPopup(popup);
  });
  </script>
</body>
</html>
