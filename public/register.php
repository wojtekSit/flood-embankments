<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, surname, email, phone, password) VALUES (?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$name, $surname, $email, $phone, $password]);
        echo "Rejestracja zakończona sukcesem. Poczekaj na zatwierdzenie konta.";
    } catch (PDOException $e) {
        echo "Błąd: " . $e->getMessage();
    }
}
?>

<form method="post">
    <input type="text" name="name" placeholder="Imię" required>
    <input type="text" name="surname" placeholder="Nazwisko" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="tel" name="phone" placeholder="Telefon">
    <input type="password" name="password" placeholder="Hasło" required>
    <button type="submit">Zarejestruj się</button>
</form>
<a href="login.php">
    <button type="button">Przejdź do logowania</button>
</a>