<?php
$host = '127.0.0.1';
$dbname = 'flood_db'; // wpisz dokładnie nazwę bazy, którą stworzyłeś w phpMyAdmin
$user = 'root';       // w XAMPP to domyślnie 'root'
$pass = '';           // w XAMPP domyślnie bez hasła

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>