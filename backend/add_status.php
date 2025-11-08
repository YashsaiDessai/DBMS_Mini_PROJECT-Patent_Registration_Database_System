<?php
// backend/add_status.php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success'=>false,'error'=>'POST required']);
        ob_end_flush();
        exit;
    }

    $patent_id = trim($_POST['patent_id'] ?? '');
    $status_text = trim($_POST['status_text'] ?? '');
    $status_type = trim($_POST['status_type'] ?? null);

    if ($patent_id === '' || $status_text === '') {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'patent_id and status_text required']);
        ob_end_flush();
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO status (patent_id, status_text, status_type) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("add_status.php prepare failed: " . $mysqli->error);
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Server error']);
        ob_end_flush();
        exit;
    }

    $stmt->bind_param('sss', $patent_id, $status_text, $status_type);
    if (!$stmt->execute()) {
        error_log("add_status.php execute failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Server error']);
        $stmt->close();
        ob_end_flush();
        exit;
    }

    $newId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();

    echo json_encode(['success'=>true,'id'=>$newId]);

} catch (Throwable $t) {
    error_log("add_status.php exception: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Server error']);
}
ob_end_flush();
