<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    die("Brak ID zgłoszenia.");
}

$report_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
// verify czy zgloszenie nalezy do uzytkownika
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? AND user_id = ?");
$stmt->execute([$report_id, $user_id]);
$report = $stmt->fetch();

if (!$report) {
    die("Brak dostępu do zgłoszenia.");
}

if ($report['is_closed']) {
    die("Zgłoszenie już jest zamknięte.");
}

// zamykanie zgloszen
$stmt = $pdo->prepare("UPDATE reports SET is_closed = 1 WHERE id = ?");
$stmt->execute([$report_id]);

header("Location: dashboard.php");
exit;
