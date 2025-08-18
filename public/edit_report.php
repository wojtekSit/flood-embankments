<?php
require_once '../includes/auth_admin.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    die("Brak ID zgłoszenia.");
}

$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$report = $stmt->fetch();

if (!$report) {
    die("Brak dostępu lub zgłoszenia.");
}
if (!isset($report['is_closed']) || $report['is_closed']) {
    die("Zgłoszenie jest już zamknięte.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $object_type = $_POST['object_type'];
    $issue_type  = $_POST['issue_type'];
    $gps_lat     = $_POST['gps_lat'];
    $gps_lng     = $_POST['gps_lng'];
    $damage_level= $_POST['damage_level'];
    $description = $_POST['description'];

    $photo_name = $report['photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $new_photo = uniqid().'_'.basename($_FILES['photo']['name']);
        $target_dir = "../uploads/";
        $target_file = $target_dir.$new_photo;

        $file_type = mime_content_type($_FILES['photo']['tmp_name']);
        if (str_starts_with($file_type, 'image/')) {
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
            $photo_name = $new_photo;
        } else {
            echo "Nieprawidłowy typ pliku. Tylko obrazy.";
            exit;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE reports 
        SET object_type=?, issue_type=?, gps_lat=?, gps_lng=?, photo=?, damage_level=?, description=? 
        WHERE id=? AND user_id=?
    ");
    $stmt->execute([
        $object_type,$issue_type,$gps_lat,$gps_lng,$photo_name,
        $damage_level,$description,$id,$user_id
    ]);

    header("Location: dashboard.php?updated=".time());
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Edycja zgłoszenia</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <link rel="stylesheet" href="/public/app.css?v=1">
  <style>
    .map{
      height:320px;
      border-radius:12px;
      overflow:hidden;
      border:1px solid #23283a;
      margin-bottom:var(--space-4);
    }
    .leaflet-container{border-radius:12px;}
    .leaflet-control-zoom,.leaflet-control-attribution{margin:8px;}
    .preview img{
      max-width:160px;
      border-radius:12px;
      border:1px solid #23283a;
    }
  </style>
</head>
<body>
<header class="site-header">
  <div class="container">
    <h1 class="site-title">Edycja zgłoszenia</h1>
    <a href="dashboard.php" class="btn btn--ghost">← Wróć</a>
  </div>
</header>

<main class="container space-lg">
  <div class="card">
    <form method="post" enctype="multipart/form-data" class="form">
      <div class="form__group">
        <label class="form__label">Typ obiektu</label>
        <select name="object_type" required class="input">
          <?php
          $types=["Wał przeciwpowodziowy","Jaz","Przepust","Śluza","Zbiornik","Inny"];
          foreach($types as $t){
            $sel=$t===$report['object_type']?'selected':'';
            echo "<option value='$t' $sel>$t</option>";
          }
          ?>
        </select>
      </div>

      <div class="form__group">
        <label class="form__label">Rodzaj uszkodzenia</label>
        <input type="text" name="issue_type" class="input" value="<?= htmlspecialchars($report['issue_type']) ?>" required>
      </div>

      <div class="form__group">
        <label class="form__label">Pozycja na mapie</label>
        <div id="map" class="map"></div>
        <input type="hidden" name="gps_lat" id="gps_lat" value="<?= $report['gps_lat'] ?>" required>
        <input type="hidden" name="gps_lng" id="gps_lng" value="<?= $report['gps_lng'] ?>" required>
        <p id="coords_display" class="muted"></p>
      </div>

      <div class="form__group">
        <label class="form__label">Aktualne zdjęcie</label>
        <div class="preview">
          <img src="../uploads/<?= htmlspecialchars($report['photo']) ?>" alt="Aktualne zdjęcie">
        </div>
      </div>

      <div class="form__group">
        <label class="form__label">Nowe zdjęcie (opcjonalnie)</label>
        <input type="file" name="photo" accept="image/*" class="input">
      </div>

      <div class="form__group">
        <label class="form__label">Stopień uszkodzenia (1–5)</label>
        <input type="number" name="damage_level" min="1" max="5" class="input" value="<?= $report['damage_level'] ?>" required>
      </div>

      <div class="form__group">
        <label class="form__label">Opis</label>
        <textarea name="description" class="input"><?= htmlspecialchars($report['description']) ?></textarea>
      </div>

      <div class="form__actions">
        <button type="submit" class="btn">💾 Zapisz zmiany</button>
      </div>
    </form>
  </div>
</main>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener("DOMContentLoaded",()=>{
  let lat=parseFloat(document.getElementById('gps_lat').value);
  let lng=parseFloat(document.getElementById('gps_lng').value);
  const map=L.map('map').setView([lat,lng],14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
  }).addTo(map);
  let marker=L.marker([lat,lng]).addTo(map);
  map.on('click',e=>{
    lat=+e.latlng.lat.toFixed(6);
    lng=+e.latlng.lng.toFixed(6);
    marker.setLatLng(e.latlng);
    document.getElementById('gps_lat').value=lat;
    document.getElementById('gps_lng').value=lng;
    document.getElementById('coords_display').innerText=`Nowa pozycja: ${lat}, ${lng}`;
  });
  document.getElementById('coords_display').innerText=`Aktualna pozycja: ${lat}, ${lng}`;
});
</script>
</body>
</html>
