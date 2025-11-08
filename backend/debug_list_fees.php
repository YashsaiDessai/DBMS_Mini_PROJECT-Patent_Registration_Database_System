<?php
// debug_list_fees.php - temporary, shows exact DB error for list_fees.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$patent_id = isset($_GET['patent_id']) ? trim($_GET['patent_id']) : null;

if ($patent_id) {
    $stmt = $mysqli->prepare("SELECT id, patent_id, fee_type, amount, created_at FROM fees WHERE patent_id = ? ORDER BY created_at DESC");
    if (!$stmt) {
        echo json_encode(['ok'=>false, 'error' => 'prepare failed', 'mysqli_error'=>$mysqli->error]);
        exit;
    }
    $stmt->bind_param('s', $patent_id);
    if (!$stmt->execute()) {
        echo json_encode(['ok'=>false, 'error' => 'execute failed', 'stmt_error'=>$stmt->error]);
        exit;
    }
    $res = $stmt->get_result();
} else {
    $res = $mysqli->query("SELECT id, patent_id, fee_type, amount, created_at FROM fees ORDER BY created_at DESC");
    if (!$res) {
        echo json_encode(['ok'=>false, 'error' => 'query failed', 'mysqli_error'=>$mysqli->error]);
        $mysqli->close();
        exit;
    }
}

$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;
echo json_encode(['ok'=>true, 'rows'=>$rows]);
$mysqli->close();
