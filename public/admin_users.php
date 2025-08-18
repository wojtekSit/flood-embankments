<?php
require_once '../includes/auth_admin.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    $is_admin    = isset($_POST['is_admin']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE users SET is_approved=?, is_admin=? WHERE id=?");
    $stmt->execute([$is_approved, $is_admin, $user_id]);
}

$stmt = $pdo->query("SELECT id, name, surname, email, is_approved, is_admin FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Panel administracyjny - Użytkownicy</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/public/app.css?v=1">
  <style>
    .checkbox-label{
      display:flex;
      align-items:center;
      gap:6px;
      font-size:.85rem;
      color:var(--text);
    }
    .checkbox-label input[type=checkbox]{
      width:16px;
      height:16px;
      accent-color: var(--accent);
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="site-title">👥 Zarządzanie użytkownikami</h1>
      <a href="dashboard.php" class="btn btn--ghost">⬅ Powrót</a>
    </div>
  </header>

  <main class="container space-lg">
    <div class="card">
      <div class="table-responsive">
        <table class="table">
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
              <td><?= htmlspecialchars($u['name']." ".$u['surname']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= $u['is_approved'] ? '✅' : '❌' ?></td>
              <td><?= $u['is_admin'] ? '⭐' : '—' ?></td>
              <td>
                <form method="post" class="inline gap-sm">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <label class="checkbox-label">
                    <input type="checkbox" name="is_approved" <?= $u['is_approved']?'checked':'' ?>>
                    Approved
                  </label>
                  <label class="checkbox-label">
                    <input type="checkbox" name="is_admin" <?= $u['is_admin']?'checked':'' ?>>
                    Admin
                  </label>
                  <button type="submit" class="btn btn--ghost">💾 Zapisz</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>
