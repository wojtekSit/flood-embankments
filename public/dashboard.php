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

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $object_type = $_POST['object_type'];
    $issue_type = $_POST['issue_type'];
    $gps_lat = $_POST['gps_lat'];
    $gps_lng = $_POST['gps_lng'];
    $damage_level = $_POST['damage_level'];
    $description = $_POST['description'];

    // WALIDACJA GPS
    if (empty($gps_lat) || empty($gps_lng)) {
        $errors[] = "Musisz wybrać lokalizację na mapie.";
    }

    // WALIDACJA ZDJĘCIA
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
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, object_type, issue_type, gps_lat, gps_lng, photo, damage_level, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, $object_type, $issue_type, $gps_lat, $gps_lng,
            $photo_name, $damage_level, $description
        ]);
        $success = "Zgłoszenie zostało zapisane.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Formularz zgłoszenia</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form input, form select, form textarea, form button {
            display: block;
            margin-bottom: 10px;
            width: 100%;
            max-width: 500px;
        }
        table { margin-top: 40px; }
    </style>
</head>
<body>

<h2>Nowe zgłoszenie</h2>
<?php
if (!empty($errors)) foreach ($errors as $e) echo "<p style='color:red;'>$e</p>";
if ($success) echo "<p style='color:green;'>$success</p>";
?>

<form method="post" enctype="multipart/form-data">
    <label>Typ obiektu:</label>
    <select name="object_type" required>
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

    <label>Rodzaj uszkodzenia:</label>
    <input type="text" name="issue_type" required>

    <label>Wybierz lokalizację na mapie:</label>
    <div id="map" style="height: 300px; margin-bottom: 10px;"></div>
    <button type="button" onclick="getLocation()">📍 Użyj mojej lokalizacji</button>

    <input type="hidden" name="gps_lat" id="gps_lat" required>
    <input type="hidden" name="gps_lng" id="gps_lng" required>
    <p id="coords_display"></p>

    <label>Zdjęcie:</label>
    <input type="file" name="photo" accept="image/*" required>

    <label>Stopień uszkodzenia (1-5):</label>
    <input type="number" name="damage_level" min="1" max="5" required>

    <label>Opis:</label>
    <textarea name="description"></textarea>

    <button type="submit">Wyślij zgłoszenie</button>
</form>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
let map;           // JEDYNA zmienna mapy
let marker = null; // JEDYNY marker

document.addEventListener("DOMContentLoaded", function () {
  map = L.map('map').setView([52.0, 19.0], 6);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);

  // Klik na mapie: przesuń / utwórz ten sam marker
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
<hr>
<h2>Twoje zgłoszenia</h2>
<a href="map.php">
    <button type="button">Zobacz mapę wszystkich twoich zgłoszeń</button>
</a>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Typ</th>
        <th>Uszkodzenie</th>
        <th>GPS</th>
        <th>Zdjęcie</th>
        <th>Stopień</th>
        <th>Data</th>
        <th>Akcja</th> <!-- NOWA KOLUMNA -->
    </tr>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    foreach ($stmt as $row) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['object_type']}</td>";
        echo "<td>{$row['issue_type']}</td>";
        echo "<td>{$row['gps_lat']}, {$row['gps_lng']}</td>";
        echo "<td><img src='../uploads/{$row['photo']}?v=" . time() . "' width='100'></td>";
        echo "<td>{$row['damage_level']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>";
        if (!$row['is_closed']) {
            echo "<a href='edit_report.php?id={$row['id']}'>Edytuj</a> | ";
            echo "<a href='close_report.php?id={$row['id']}' onclick=\"return confirm('Na pewno zamknąć zgłoszenie?')\">Zamknij</a>";
        } else {
            echo "Zamknięte ✅";
        }
        echo "</td>";
        
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>
<p style="text-align: right;">
    Zalogowany jako <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
    | <a href="logout.php">Wyloguj się</a>
</p>

