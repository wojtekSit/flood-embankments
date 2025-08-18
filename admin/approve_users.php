<?php
require_once '../includes/auth_admin.php';

// Zatwierdzanie użytkownika
if (isset($_GET['approve_id'])) {
    $id = (int)$_GET['approve_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: approve_users.php");
    exit;
}

// Lista użytkowników oczekujących
$stmt = $pdo->query("SELECT id, name, surname, email, phone FROM users WHERE is_approved = 0");
$users = $stmt->fetchAll();
?>

<h2>Użytkownicy oczekujący na zatwierdzenie</h2>
<?php if (empty($users)): ?>
    <p>Brak oczekujących użytkowników.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Email</th>
            <th>Telefon</th>
            <th>Akcja</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['surname']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['phone']) ?></td>
                <td>
                    <a href="approve_users.php?approve_id=<?= $user['id'] ?>">✅ Zatwierdź</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
