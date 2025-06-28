<?php
header('Content-Type: application/json');
require 'db.php';

// Sprawdzenie wymaganych pól
if (!isset($_POST['code'], $_POST['description'], $_POST['lat'], $_POST['lng'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Pobranie danych
$code = $_POST['code'];
$description = $_POST['description'];
$lat = floatval($_POST['lat']);
$lng = floatval($_POST['lng']);

// Znajdź użytkownika na podstawie kodu
$stmt = $pdo->prepare("SELECT id, code_hash FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$authenticated = false;
$user_id = null;
foreach ($users as $user) {
    if (password_verify($code, $user['code_hash'])) {
        $authenticated = true;
        $user_id = $user['id'];
        break;
    }
}

if (!$authenticated) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid code']);
    exit;
}

// Obsługa uploadu zdjęcia
$photo_path = null;
if (!empty($_FILES['photo']['tmp_name'])) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $filename = uniqid() . "_" . basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $photo_path = 'uploads/' . $filename; // ścieżka względna, którą React może odczytać
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Photo upload failed']);
        exit;
    }
}

// Wstaw zgłoszenie do bazy
$stmt = $pdo->prepare("INSERT INTO flood_reports (user_id, description, coordinates, photo_path) VALUES (:user_id, :description, ST_GeomFromText(:point), :photo_path)");
$stmt->execute([
    ':user_id' => $user_id,
    ':description' => $description,
    ':point' => "POINT($lng $lat)",
    ':photo_path' => $photo_path,
]);

echo json_encode(['success' => true]);
?>
