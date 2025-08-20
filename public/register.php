<?php
require_once '../config/db.php';

$msg = "";
$msg_type = "success"; // or "error"

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $surname  = $_POST['surname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO app_users (name, surname, email, phone, password) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $surname, $email, $phone, $password]);
        $msg = "Rejestracja zakończona sukcesem. Poczekaj na zatwierdzenie konta.";
        $msg_type = "success";
    } catch (PDOException $e) {
        $msg = "Błąd: " . $e->getMessage();
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Rejestracja</title>
  <link rel="stylesheet" href="/public/app.css?v=1">
</head>
<body>
  <main class="auth-wrap">
    <section class="card auth-card">
      <header class="section-header" style="margin-bottom:16px;">
        <h1 class="site-title" style="font-size:1.25rem;margin:0;">Utwórz konto</h1>
      </header>

      <?php if (!empty($msg)): ?>
        <div class="alert <?= $msg_type === 'error' ? 'alert--error' : 'alert--success' ?>" role="alert" aria-live="assertive">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="form" autocomplete="on" novalidate>
        <div class="form__group">
          <label for="name" class="form__label">Imię</label>
          <input id="name" name="name" class="input" type="text" placeholder="Jan"
                 required autocomplete="given-name" autofocus>
        </div>

        <div class="form__group">
          <label for="surname" class="form__label">Nazwisko</label>
          <input id="surname" name="surname" class="input" type="text" placeholder="Kowalski"
                 required autocomplete="family-name">
        </div>

        <div class="form__group">
          <label for="email" class="form__label">Email</label>
          <input id="email" name="email" class="input" type="email" placeholder="twoj@email.pl"
                 required autocomplete="email">
        </div>

        <div class="form__group">
          <label for="phone" class="form__label">Telefon</label>
          <input id="phone" name="phone" class="input" type="tel" placeholder="500 600 700"
                 autocomplete="tel">
        </div>

        <div class="form__group">
          <label for="password" class="form__label">Hasło</label>
          <input id="password" name="password" class="input" type="password" placeholder="••••••••"
                 required autocomplete="new-password">
        </div>

        <div class="form__actions">
          <button class="btn" type="submit">Zarejestruj się</button>
        </div>
      </form>

      <div class="row gap-sm" style="margin-top:12px;">
        <a class="btn btn--ghost" href="login.php">Przejdź do logowania</a>
      </div>
    </section>
  </main>
</body>
</html>
