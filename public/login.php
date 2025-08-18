<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, name, password, is_approved, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Użytkownik nie istnieje.";
    } elseif (!$user['is_approved']) {
        echo "Konto nie zostało jeszcze zatwierdzone przez administratora.";
    } elseif (!password_verify($password, $user['password'])) {
        echo "Nieprawidłowe hasło.";
    } else {
        session_regenerate_id(true); // bezpieczeństwo
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['is_admin']   = (int)$user['is_admin'];     
        header('Location: dashboard.php');
        exit;
    }
}
?>
<form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Hasło" required>
    <button type="submit">Zaloguj się</button>
</form>
<a href="register.php">
    <button type="button">Zarejestruj się</button>
</a>
