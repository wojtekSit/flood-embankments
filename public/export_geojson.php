<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Brak autoryzacji.");
}

if (isset($_GET['download'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="reports_user_' . $user_id . '.geojson"');

    $stmt = $pdo->prepare("
        SELECT 
            JSON_OBJECT(
                'type', 'FeatureCollection',
                'features', JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'type', 'Feature',
                        'geometry', CAST(ST_AsGeoJSON(gps_point) AS JSON),
                        'properties', JSON_OBJECT(
                            'id', id,
                            'object_type', object_type,
                            'issue_type', issue_type,
                            'damage_level', damage_level,
                            'is_closed', is_closed,
                            'created_at', created_at
                        )
                    )
                )
            ) AS geojson
        FROM reports
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['geojson'] ?? '{"type":"FeatureCollection","features":[]}';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Eksport danych do GeoJSON</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container space-lg">
    <div class="card">
      <h2 class="h2">📤 Eksport danych do GeoJSON</h2>
      <p class="muted">
        Tutaj możesz pobrać swoje zgłoszenia w formacie <strong>GeoJSON</strong> (EPSG:4326).
        Plik otworzysz w <strong>PostGIS, QGIS, Pythonie (GeoPandas)</strong> lub innym narzędziu GIS.
      </p>
      <div class="form__actions">
        <a href="export_geojson.php?download=1" class="btn">⬇ Pobierz GeoJSON</a>
        <a href="dashboard.php" class="btn btn--ghost">← Powrót</a>
      </div>
    </div>
  </div>
</body>
</html>
