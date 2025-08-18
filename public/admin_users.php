<?php
require_once '../includes/auth_admin.php'; // sprawdza czy user jest adminem
require_once '../config/db.php';

// Aktualizacja użytkownika
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET is_approved = ?, is_admin = ? WHERE id = ?");
    $stmt->execute([$is_approved, $is_admin, $user_id]);
}

// Pobranie listy użytkowników
$stmt = $pdo->query("SELECT id, name, surname, email, is_approved, is_admin FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel administracyjny - Użytkownicy</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        form { margin: 0; }
        button { padding: 5px 10px; }
    </style>
</head>
<body>
    <h1>Panel administracyjny - Zarządzanie użytkownikami</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Imię i nazwisko</th>
                <th>Email</th>
                <th>Approved</th>
                <th>Admin</th>
                <th>Akcja</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name'] . " " . $u['surname']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['is_approved'] ? '✅' : '❌' ?></td>
                    <td><?= $u['is_admin'] ? '⭐' : '—' ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <label>
                                <input type="checkbox" name="is_approved" <?= $u['is_approved'] ? 'checked' : '' ?>> Approved
                            </label>
                            <label>
                                <input type="checkbox" name="is_admin" <?= $u['is_admin'] ? 'checked' : '' ?>> Admin
                            </label>
                            <button type="submit">Zapisz</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">⬅ Powrót</a></p>
</body>
</html>
