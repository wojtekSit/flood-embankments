<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_error_handler(function($no,$str,$file,$line){ throw new ErrorException($str,0,$no, $file,$line); });

require_once '../includes/auth_admin.php';
require_once '../config/db.php';
require_once '../vendor/autoload.php';


use Dompdf\Dompdf;

$start_date = $_GET['start'] ?? null;
$end_date = $_GET['end'] ?? null;

if (!$start_date || !$end_date) {
    die("Podaj zakres dat w parametrze ?start=YYYY-MM-DD&end=YYYY-MM-DD");
}

// Pobierz dane z bazy
$stmt = $pdo->prepare("
    SELECT r.*, u.name, u.surname
    FROM app_reports r
    JOIN app_users u ON r.user_id = u.id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$start_date, $end_date]);
$reports = $stmt->fetchAll();

// Generowanie HTML
ob_start();
?>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h1 { margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    th { background: #eee; }
</style>

<h1>Raport zgłoszeń</h1>
<p>Zakres: <strong><?= htmlspecialchars($start_date) ?></strong> – <strong><?= htmlspecialchars($end_date) ?></strong></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Obiekt</th>
            <th>Uszkodzenie</th>
            <th>Stopień</th>
            <th>Opis</th>
            <th>Użytkownik</th>
            <th>Status</th>
            <th>Lat</th>
            <th>Lng</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reports as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['created_at'] ?></td>
                <td><?= htmlspecialchars($r['object_type']) ?></td>
                <td><?= htmlspecialchars($r['issue_type']) ?></td>
                <td><?= $r['damage_level'] ?></td>
                <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
                <td><?= htmlspecialchars($r['name'] . ' ' . $r['surname']) ?></td>
                <td><?= $r['is_closed'] ? 'Zamknięte' : 'Otwarte' ?></td>
                <td><?= $r['gps_lat'] ?></td>
                <td><?= $r['gps_lng'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$html = ob_get_clean();

// Generowanie PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("raport_{$start_date}_{$end_date}.pdf", ["Attachment" => false]);
