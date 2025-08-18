<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("Brak ID zgłoszenia.");
}

$report_id = (int)$_GET['id'];
$user_id   = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM reports WHERE id=? AND user_id=?");
$stmt->execute([$report_id, $user_id]);
$report = $stmt->fetch();

if (!$report) {
    $error = "Brak dostępu do zgłoszenia.";
} elseif ($report['is_closed']) {
    $error = "Zgłoszenie już jest zamknięte.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $stmt = $pdo->prepare("UPDATE reports SET is_closed=1 WHERE id=?");
    $stmt->execute([$report_id]);
    header("Location: dashboard.php?closed=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Zamknij zgłoszenie</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/public/app.css?v=1">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="site-title">🔒 Zamknij zgłoszenie</h1>
      <a href="dashboard.php" class="btn btn--ghost">⬅ Powrót</a>
    </div>
  </header>

  <main class="container space-lg">
    <div class="card">
      <?php if (!empty($error)): ?>
        <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
        <p><a href="dashboard.php" class="btn btn--ghost">Wróć do panelu</a></p>
      <?php else: ?>
        <h2 class="h2">Czy na pewno chcesz zamknąć to zgłoszenie?</h2>
        <ul class="list">
          <li><strong>Typ:</strong> <?= htmlspecialchars($report['object_type']) ?></li>
          <li><strong>Uszkodzenie:</strong> <?= htmlspecialchars($report['issue_type']) ?></li>
          <li><strong>Data:</strong> <?= htmlspecialchars($report['created_at']) ?></li>
        </ul>
        <form method="post" class="form__actions">
          <button type="submit" class="btn btn--danger">✅ Tak, zamknij</button>
          <a href="dashboard.php" class="btn btn--ghost">Anuluj</a>
        </form>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
