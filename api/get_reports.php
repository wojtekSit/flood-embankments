<?php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("
    SELECT 
        fr.id, 
        u.name AS reporter_name, 
        fr.description, 
        ST_X(fr.coordinates) AS lng, 
        ST_Y(fr.coordinates) AS lat, 
        fr.photo_path, 
        fr.report_time
    FROM flood_reports fr
    JOIN users u ON fr.user_id = u.id
    ORDER BY fr.report_time DESC
");

$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($reports);
?>
