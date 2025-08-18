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

// CSRF (good practice)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        $errors[] = "Nieprawidłowy token formularza. Odśwież stronę i spróbuj ponownie.";
    }
}

if (empty($errors) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize
    $object_type  = trim($_POST['object_type'] ?? '');
    $issue_type   = trim($_POST['issue_type'] ?? '');
    $gps_lat      = trim($_POST['gps_lat'] ?? '');
    $gps_lng      = trim($_POST['gps_lng'] ?? '');
    $damage_level = (int)($_POST['damage_level'] ?? 0);
    $description  = trim($_POST['description'] ?? '');

    // validate GPS
    if ($gps_lat === '' || $gps_lng === '') {
        $errors[] = "Musisz wybrać lokalizację na mapie.";
    } else {
        if (!is_numeric($gps_lat) || !is_numeric($gps_lng)) {
            $errors[] = "Nieprawidłowe współrzędne.";
        } else {
            $lat = (float)$gps_lat;
            $lng = (float)$gps_lng;
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                $errors[] = "Współrzędne poza zakresem.";
            }
        }
    }

    // validate damage
    if ($damage_level < 1 || $damage_level > 5) {
        $errors[] = "Stopień uszkodzenia musi być w zakresie 1–5.";
    }

    // validate photo (size/type)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        $file_type = mime_content_type($_FILES['photo']['tmp_name']);
        $max_bytes = 6 * 1024 * 1024; // 6MB

        if (!in_array($file_type, $allowed, true)) {
            $errors[] = "Nieprawidłowy format zdjęcia.";
        } elseif ($_FILES['photo']['size'] > $max_bytes) {
            $errors[] = "Plik jest zbyt duży (max 6MB).";
        }
    } else {
        $errors[] = "Zdjęcie jest wymagane.";
    }

    // insert if ok
    if (empty($errors)) {
        $photo_name = uniqid('img_', true) . '.jpg';
        $target_dir = dirname(__DIR__) . "/uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $target_file = $target_dir . $photo_name;

        // Re-encode to JPEG (strips EXIF) — optional hardening
        $img = imagecreatefromstring(file_get_contents($_FILES['photo']['tmp_name']));
        if ($img) {
            imagejpeg($img, $target_file, 85);
            imagedestroy($img);
        } else {
            $errors[] = "Nie można przetworzyć obrazu.";
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
                $gps_lng,  // POINT(lng, lat) = (X, Y)
                $gps_lat,
                $photo_name,
                $damage_level,
                $description
            ]);
            $success = "Zgłoszenie zostało zapisane.";
        }
    }
}

// prepare CSRF for next request
$_SESSION['csrf'] = bin2hex(random_bytes(16));
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Nowe zgłoszenie</title>
  <link rel="preconnect" href="https://unpkg.com">
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
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

        <div class="form__group">
          <label for="object_type" class="form__label">Typ obiektu</label>
          <select id="object_type" name="object_type" class="input" required>
            <option value="Wał przeciwpowodziowy">Wał przeciwpowodziowy</option>
            <option value="Jaz">Jaz</option>
            <option value="Przepust">Przepust</option>
            <option value="Śluza">Śluza</option>
            <option value="RowyMelioracyjne">Rowy melioracyjne</option>
            <option value="Zbiornik">Zbiornik retencyjny</option>
            <option value="WylotKanalizacjiDeszczowej">Wylot kanalizacji deszczowej</option>
            <option value="BarieraMobilna">Bariera mobilna / Szandory</option>
            <option value="RowyOdwadniajace">Rowy odwadniające / Drenaże osiedlowe</option>
            <option value="StudzienkiKratkiSciekowe">Studzienki i kratki ściekowe</option>
            <option value="RowyCieki">Zastawka w rowie lub małym cieku</option>
            <option value="Przempompownia">Przepompownia osiedlowa</option>
            <option value="Inny">Inny obiekt hydrotechniczny</option>
          </select>
        </div>

        <div class="form__group">
          <label for="issue_type" class="form__label">Rodzaj uszkodzenia</label>
          <input id="issue_type" name="issue_type" class="input" type="text" required placeholder="np. rozmycie skarpy, zapadnięcie, zator…">
        </div>

        <div class="form__group">
          <label class="form__label">Lokalizacja</label>
          <div id="map" class="map" aria-label="Mapa wyboru lokalizacji"></div>
          <div class="row gap-sm">
            <button type="button" id="btnGeo" class="btn btn--ghost">📍 Użyj mojej lokalizacji</button>
            <span id="coords_display" class="muted" aria-live="polite"></span>
          </div>
          <input type="hidden" name="gps_lat" id="gps_lat" required>
          <input type="hidden" name="gps_lng" id="gps_lng" required>
        </div>

        <div class="form__group">
          <label for="photo" class="form__label">Zdjęcie</label>
          <input id="photo" name="photo" class="input" type="file" accept="image/*" required>
          <div id="photo_preview" class="preview" hidden>
            <img alt="Podgląd zdjęcia" id="photo_img">
            <span id="photo_name" class="muted"></span>
          </div>
        </div>

        <div class="form__group">
          <label for="damage_level" class="form__label">Stopień uszkodzenia (1–5)</label>
          <input id="damage_level" name="damage_level" class="input" type="number" inputmode="numeric" min="1" max="5" required>
        </div>

        <div class="form__group">
          <label for="description" class="form__label">Opis</label>
          <textarea id="description" name="description" class="input" rows="4" placeholder="Krótki opis sytuacji (opcjonalnie)"></textarea>
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
            foreach ($stmt as $row):
          ?>
            <tr>
              <td data-label="ID"><?= (int)$row['id'] ?></td>
              <td data-label="Typ"><?= htmlspecialchars($row['object_type']) ?></td>
              <td data-label="Uszkodzenie"><?= htmlspecialchars($row['issue_type']) ?></td>
              <td data-label="GPS"><?= htmlspecialchars($row['gps_lat']) ?>, <?= htmlspecialchars($row['gps_lng']) ?></td>
              <td data-label="Zdjęcie">
                <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>?v=<?= time() ?>" width="80" height="60" style="object-fit:cover;border-radius:8px;" alt="miniatura">
              </td>
              <td data-label="Stopień"><?= (int)$row['damage_level'] ?></td>
              <td data-label="Data"><?= htmlspecialchars($row['created_at']) ?></td>
              <td data-label="Akcja">
                <?php if (!$row['is_closed']): ?>
                  <a class="link" href="edit_report.php?id=<?= (int)$row['id'] ?>">Edytuj</a>
                  <span class="muted">·</span>
                  <a class="link" href="close_report.php?id=<?= (int)$row['id'] ?>" onclick="return confirm('Na pewno zamknąć zgłoszenie?')">Zamknij</a>
                <?php else: ?>
                  Zamknięte ✅
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="/public/app.js?v=1" defer></script>
</body>
</html>
