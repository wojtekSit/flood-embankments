<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/auth.php';
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// form handling (LOGIKA BEZ ZMIAN)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $object_type = $_POST['object_type'];
    $issue_type = $_POST['issue_type'];
    $gps_lat = $_POST['gps_lat'];
    $gps_lng = $_POST['gps_lng'];
    $damage_level = $_POST['damage_level'];
    $description = $_POST['description'];

    // validate GPS
    if (empty($gps_lat) || empty($gps_lng)) {
        $errors[] = "Musisz wybrać lokalizację na mapie.";
    }

    // validate photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_name = uniqid() . '_' . basename($_FILES['photo']['name']);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $photo_name;

        $file_type = mime_content_type($_FILES['photo']['tmp_name']);
        if (str_starts_with($file_type, 'image/')) {
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
        } else {
            $errors[] = "Nieprawidłowy format zdjęcia.";
        }
    } else {
        $errors[] = "Zdjęcie jest wymagane.";
    }

    if (empty($errors)) {
      $stmt = $pdo->prepare("
      INSERT INTO reports (
          user_id, object_type, issue_type,
          gps_lat, gps_lng, gps_point,
          photo, damage_level, description
      )
      VALUES (
          ?, ?, ?, ?, ?, ST_SRID(POINT(?, ?), 4326), ?, ?, ?
      )
  ");

  $stmt->execute([
      $user_id,
      $object_type,
      $issue_type,
      $gps_lat,
      $gps_lng,
      $gps_lng,
      $gps_lat, // lat musi byc drugie
      $photo_name,
      $damage_level,
      $description
  ]);
        $success = "Zgłoszenie zostało zapisane.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Formularz zgłoszenia</title>
  <link rel="stylesheet" href="/public/app.css?v=1" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="site-title">Zgłoś uszkodzenie</h1>
      <div class="user">
        Zalogowany jako <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
        <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
          · <a href="admin_users.php" class="link">Panel administracyjny</a>
        <?php endif; ?>
        · <a href="logout.php" class="link">Wyloguj się</a>
      </div>
    </div>
  </header>

  <main class="container space-lg">
    <?php if (!empty($errors)): ?>
      <div class="alert alert--error" role="alert" aria-live="assertive">
        <ul class="list">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert--success" role="status" aria-live="polite">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <section class="card">
      <form method="post" enctype="multipart/form-data" class="form" id="reportForm" novalidate>
        <div class="form__group">
          <label class="form__label" for="object_type">Typ obiektu</label>
          <select id="object_type" name="object_type" class="input" required>
            <option value="Wał przeciwpowodziowy">Wał przeciwpowodziowy</option>
            <option value="Jaz">Jaz</option>
            <option value="Przepust">Przepust</option>
            <option value="Śluza">Śluza</option>
            <option value="RowyMelioracyjne">RowyMelioracyjne</option>
            <option value="Zbiornik">Zbiornik retencyjny</option>
            <option value="WylotKanalizacjiDeszczowej">Wylot kanalizacji deszczowej</option>
            <option value="BarieraMobilna">Bariera mobilna/Szandory</option>
            <option value="RowyOdwadniajace">Rowy odwadniające/Drenaże osiedlowe</option>
            <option value="StudzienkiKratkiSciekowe">Studzienki i kratki ściekowe</option>
            <option value="RowyCieki">Zastawka w rowie lub małym cieku</option>
            <option value="Przempompownia">Przepompownia osiedlowa</option>
            <option value="Inny">Inny obiekt hydrotechniczny</option>
          </select>
        </div>

        <div class="form__group">
          <label class="form__label" for="issue_type">Rodzaj uszkodzenia</label>
          <input id="issue_type" class="input" type="text" name="issue_type" required placeholder="np. rozmycie skarpy, zator…">
        </div>

        <div class="form__group">
          <label class="form__label">Lokalizacja</label>
          <div id="map" class="map" aria-label="Mapa wyboru lokalizacji"></div>
          <div class="row gap-sm">
            <button type="button" class="btn btn--ghost" onclick="getLocation()">📍 Użyj mojej lokalizacji</button>
            <span id="coords_display" class="muted" aria-live="polite"></span>
          </div>
          <input type="hidden" name="gps_lat" id="gps_lat" required>
          <input type="hidden" name="gps_lng" id="gps_lng" required>
        </div>

        <div class="form__group">
          <label class="form__label" for="photo">Zdjęcie</label>
          <input id="photo" class="input" type="file" name="photo" accept="image/*" required>
          <div id="photo_preview" class="preview" hidden>
            <img alt="Podgląd zdjęcia" id="photo_img">
            <span id="photo_name" class="muted"></span>
          </div>
        </div>

        <div class="form__group">
          <label class="form__label" for="damage_level">Stopień uszkodzenia (1-5)</label>
          <input id="damage_level" class="input" type="number" name="damage_level" min="1" max="5" required>
        </div>

        <div class="form__group">
          <label class="form__label" for="description">Opis</label>
          <textarea id="description" class="input" name="description" rows="4" placeholder="Krótki opis (opcjonalnie)"></textarea>
        </div>

        <div class="form__actions">
          <button class="btn" type="submit">Wyślij zgłoszenie</button>
        </div>
      </form>
    </section>

    <section class="space-lg">
      <header class="section-header">
        <h2 class="h2">Raport PDF</h2>
      </header>
      <form method="get" action="report.php" target="_blank" class="row gap-sm">
        <?php
          // domyślny zakres – ostatnie 7 dni
          $today = date('Y-m-d');
          $week_ago = date('Y-m-d', strtotime('-7 days'));
        ?>
        <label class="inline">
          <span>Od</span>
          <input class="input" type="date" name="start" value="<?= $week_ago ?>">
        </label>
        <label class="inline">
          <span>Do</span>
          <input class="input" type="date" name="end" value="<?= $today ?>">
        </label>
        <button class="btn btn--ghost" type="submit">📄 Generuj raport PDF</button>
      </form>
    </section>

    <section class="space-lg">
      <header class="section-header">
        <h2 class="h2">Twoje zgłoszenia</h2>
        <a href="export_geojson.php" class="btn btn--ghost">Eksport do GeoJSON</a>
        <a href="map.php" class="btn btn--ghost">Zobacz mapę</a>
      </header>

      <div class="table-responsive">
        <table class="table">
          <thead>
          <tr>
            <th>ID</th>
            <th>Typ</th>
            <th>Uszkodzenie</th>
            <th>GPS</th>
            <th>Zdjęcie</th>
            <th>Stopień</th>
            <th>Data</th>
            <th>Akcja</th>
          </tr>
          </thead>
          <tbody>
          <?php
          $stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
          $stmt->execute([$user_id]);
          foreach ($stmt as $row) {
              echo "<tr>";
              echo "<td data-label='ID'>".(int)$row['id']."</td>";
              echo "<td data-label='Typ'>".htmlspecialchars($row['object_type'])."</td>";
              echo "<td data-label='Uszkodzenie'>".htmlspecialchars($row['issue_type'])."</td>";
              echo "<td data-label='GPS'>".htmlspecialchars($row['gps_lat']).", ".htmlspecialchars($row['gps_lng'])."</td>";
              echo "<td data-label='Zdjęcie'><img src='../uploads/".htmlspecialchars($row['photo'])."?v=".time()."' width='80' height='60' style='object-fit:cover;border-radius:8px;' alt='miniatura'></td>";
              echo "<td data-label='Stopień'>".(int)$row['damage_level']."</td>";
              echo "<td data-label='Data'>".htmlspecialchars($row['created_at'])."</td>";
              echo "<td data-label='Akcja'>";
              if (!$row['is_closed']) {
                  echo "<a class='link' href='edit_report.php?id=".(int)$row['id']."'>Edytuj</a> <span class='muted'>·</span> ";
                  echo "<a class='link' href='close_report.php?id=".(int)$row['id']."'>Zamknij</a>";
              } else {
                  echo "Zamknięte ✅";
              }
              echo "</td>";
              echo "</tr>";
          }
          ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="/public/app.js?v=1"></script>
  <script>
  let map;           // map variable
  let marker = null; // marker variable

  document.addEventListener("DOMContentLoaded", function () {
    map = L.map('map').setView([52.0, 19.0], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

    // klik na mapie przesuwa marker
    map.on('click', function (e) {
      const lat = +e.latlng.lat.toFixed(6);
      const lng = +e.latlng.lng.toFixed(6);
      setMarker(lat, lng);
      document.getElementById('gps_lat').value = lat;
      document.getElementById('gps_lng').value = lng;
      document.getElementById('coords_display').innerText = `📍 Wybrana lokalizacja: ${lat}, ${lng}`;
    });
  });

  // Geolokalizacja: ustaw marker i widok, NIE twórz mapy ponownie
  function getLocation() {
    if (!navigator.geolocation) {
      alert("Geolokalizacja nie jest wspierana przez tę przeglądarkę.");
      return;
    }
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const lat = +position.coords.latitude.toFixed(6);
        const lng = +position.coords.longitude.toFixed(6);
        setMarker(lat, lng);
        map.setView([lat, lng], 15);
        document.getElementById('gps_lat').value = lat;
        document.getElementById('gps_lng').value = lng;
        document.getElementById('coords_display').innerText = `📍 Twoja lokalizacja: ${lat}, ${lng}`;
      },
      function (error) {
        alert("Błąd pobierania lokalizacji: " + error.message);
      }
    );
  }

  // Jedyny mechanizm tworzenia/przesuwania markera
  function setMarker(lat, lng) {
    if (!map) {
      console.error("Mapa nie jest jeszcze gotowa!");
      return;
    }
    const latlng = L.latLng(lat, lng);
    if (marker) {
      marker.setLatLng(latlng);
    } else {
      marker = L.marker(latlng).addTo(map);
    }
  }

  // Walidacja przed wysyłką: wymagaj współrzędnych, skądkolwiek by nie były
  document.querySelector("form").addEventListener("submit", function (e) {
    const lat = document.getElementById('gps_lat').value;
    const lng = document.getElementById('gps_lng').value;
    if (!lat || !lng) {
      e.preventDefault();
      alert("Musisz wybrać lokalizację (kliknij na mapie lub użyj przycisku geolokalizacji).");
    }
  });
  </script>
</body>
</html>
