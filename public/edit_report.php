<?php
require_once '../includes/auth_admin.php';
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$id) {
    die("Brak ID zgłoszenia.");
}

// Pobierz dane zgłoszenia użytkownika
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$report = $stmt->fetch();

if (!$report) {
    die("Brak dostępu lub zgłoszenia.");
}
if (!isset($report['is_closed']) || $report['is_closed']) {
    die("Zgłoszenie jest już zamknięte.");
}

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $object_type = $_POST['object_type'];
    $issue_type = $_POST['issue_type'];
    $gps_lat = $_POST['gps_lat'];
    $gps_lng = $_POST['gps_lng'];
    $damage_level = $_POST['damage_level'];
    $description = $_POST['description'];

    $photo_name = $report['photo']; // domyślnie stare zdjęcie

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $new_photo = uniqid() . '_' . basename($_FILES['photo']['name']);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $new_photo;

        $file_type = mime_content_type($_FILES['photo']['tmp_name']);
        if (str_starts_with($file_type, 'image/')) {
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
            $photo_name = $new_photo;
        } else {
            echo "Nieprawidłowy typ pliku. Tylko obrazy.";
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE reports SET object_type = ?, issue_type = ?, gps_lat = ?, gps_lng = ?, photo = ?, damage_level = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([
        $object_type, $issue_type, $gps_lat, $gps_lng, $photo_name,
        $damage_level, $description, $id, $user_id
    ]);

    header("Location: dashboard.php?updated=" . time());
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja zgłoszenia</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form input, form select, form textarea, form button {
            display: block;
            margin-bottom: 10px;
            width: 100%;
            max-width: 500px;
        }
    </style>
</head>
<body>
<h2>Edycja zgłoszenia</h2>

<form method="post" enctype="multipart/form-data">
    <label>Typ obiektu:</label>
    <select name="object_type" required>
        <?php
        $types = ["Wał przeciwpowodziowy", "Jaz", "Przepust", "Śluza", "Zbiornik", "Inny"];
        foreach ($types as $type) {
            $selected = $type === $report['object_type'] ? 'selected' : '';
            echo "<option value='$type' $selected>$type</option>";
        }
        ?>
    </select>

    <label>Rodzaj uszkodzenia:</label>
    <input type="text" name="issue_type" value="<?= htmlspecialchars($report['issue_type']) ?>" required>

    <label>Pozycja na mapie:</label>
    <div id="map" style="height: 300px;"></div>
    <input type="hidden" name="gps_lat" id="gps_lat" value="<?= $report['gps_lat'] ?>" required>
    <input type="hidden" name="gps_lng" id="gps_lng" value="<?= $report['gps_lng'] ?>" required>
    <p id="coords_display"></p>

    <label>Aktualne zdjęcie:</label>
    <img src="../uploads/<?= htmlspecialchars($report['photo']) ?>" width="200"><br>

    <label>Nowe zdjęcie (opcjonalnie):</label>
    <input type="file" name="photo" accept="image/*">

    <label>Stopień uszkodzenia (1–5):</label>
    <input type="number" name="damage_level" min="1" max="5" value="<?= $report['damage_level'] ?>" required>

    <label>Opis:</label>
    <textarea name="description"><?= htmlspecialchars($report['description']) ?></textarea>

    <button type="submit">Zapisz zmiany</button>
</form>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var lat = parseFloat(document.getElementById('gps_lat').value);
    var lng = parseFloat(document.getElementById('gps_lng').value);
    var map = L.map('map').setView([lat, lng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    var marker = L.marker([lat, lng]).addTo(map);

    map.on('click', function (e) {
        lat = e.latlng.lat.toFixed(6);
        lng = e.latlng.lng.toFixed(6);

        marker.setLatLng(e.latlng);

        document.getElementById('gps_lat').value = lat;
        document.getElementById('gps_lng').value = lng;
        document.getElementById('coords_display').innerText = "Nowa pozycja: " + lat + ", " + lng;
    });

    document.getElementById('coords_display').innerText = "Aktualna pozycja: " + lat + ", " + lng;
});
</script>
</body>
</html>
