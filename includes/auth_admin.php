<?php
require_once 'auth.php'; 
require_once '../config/db.php';

$stmt = $pdo->prepare("SELECT is_admin FROM app_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    die("Brak dostępu – tylko administrator.");
}
