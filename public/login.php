<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

$error = ""; // tylko do ładnego wyświetlania komunikatów

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, name, password, is_approved, is_admin FROM app_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Użytkownik nie istnieje.";
    } elseif (!$user['is_approved']) {
        $error = "Konto nie zostało jeszcze zatwierdzone przez administratora.";
    } elseif (!password_verify($password, $user['password'])) {
        $error = "Nieprawidłowe hasło.";
    } else {
        session_regenerate_id(true); // bezpieczeństwo
        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin']  = (int)$user['is_admin'];
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Logowanie</title>
  <link rel="stylesheet" href="/app/public/app.css?v=1">
</head>
<body>
  <main class="auth-wrap">
    <section class="card auth-card">
      <header class="section-header" style="margin-bottom:16px;">
        <h1 class="site-title" style="font-size:1.25rem;margin:0;">Zaloguj się</h1>
      </header>

      <?php if (!empty($error)): ?>
        <div class="alert alert--error" role="alert" aria-live="assertive">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="form" autocomplete="on" novalidate>
        <div class="form__group">
          <label for="email" class="form__label">Email</label>
          <input id="email" name="email" class="input"
                 type="email" placeholder="twoj@email.pl"
                 required autocomplete="email" autofocus>
        </div>

        <div class="form__group">
          <label for="password" class="form__label">Hasło</label>
          <input id="password" name="password" class="input"
                 type="password" placeholder="••••••••"
                 required autocomplete="current-password">
        </div>

        <div class="form__actions">
          <button class="btn" type="submit">Zaloguj się</button>
        </div>
      </form>

      <div class="row gap-sm" style="margin-top:12px;">
        <a class="btn btn--ghost" href="./register.php">Zarejestruj się</a>
      </div>
    </section>
  </main>
</body>
</html>
