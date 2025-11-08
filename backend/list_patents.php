<?php
// backend/list_patents.php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$res = $mysqli->query("SELECT id, title, application_number, filing_date, created_at FROM patents ORDER BY created_at DESC");
if (!$res) {
    error_log("list_patents.php query failed: " . $mysqli->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    $mysqli->close();
    exit;
}

$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;
echo json_encode($rows);
$mysqli->close();
