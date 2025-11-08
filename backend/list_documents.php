<?php
// backend/list_documents.php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$patent_id = isset($_GET['patent_id']) ? trim($_GET['patent_id']) : null;

try {
    if ($patent_id) {
        $stmt = $mysqli->prepare("SELECT id, patent_id, filename, filepath, created_at FROM documents WHERE patent_id = ? ORDER BY created_at DESC");
        $stmt->bind_param('s', $patent_id);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $mysqli->query("SELECT id, patent_id, filename, filepath, created_at FROM documents ORDER BY created_at DESC");
        if (!$res) {
            error_log("list_documents.php query failed: " . $mysqli->error);
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>'Server error']);
            $mysqli->close();
            exit;
        }
    }
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
    if (isset($stmt)) $stmt->close();
    $mysqli->close();

} catch (Throwable $t) {
    error_log("list_documents.php exception: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Server error']);
}
