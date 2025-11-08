<?php
// backend/record_fee.php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'POST required']);
        ob_end_flush();
        exit;
    }

    // expected form fields: patent_id, fee_type, amount
    $patent_id = trim($_POST['patent_id'] ?? '');
    $fee_type  = trim($_POST['fee_type'] ?? '');
    $amount_raw = trim($_POST['amount'] ?? '');

    if ($patent_id === '' || $fee_type === '' || $amount_raw === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'patent_id, fee_type and amount are required']);
        ob_end_flush();
        exit;
    }

    // Normalize amount to decimal; reject non-numeric
    // allow comma or dot, convert comma to dot
    $amount_norm = str_replace(',', '.', $amount_raw);
    if (!is_numeric($amount_norm)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'amount must be numeric']);
        ob_end_flush();
        exit;
    }
    $amount = (float) $amount_norm;

    $stmt = $mysqli->prepare("INSERT INTO fees (patent_id, fee_type, amount) VALUES (?, ?, ?)");
    if (!$stmt) {
        error_log("record_fee.php prepare failed: " . $mysqli->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
        ob_end_flush();
        exit;
    }

    // 'd' in bind_param is for double (float)
    $stmt->bind_param('ssd', $patent_id, $fee_type, $amount);

    if (!$stmt->execute()) {
        error_log("record_fee.php execute failed: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error']);
        $stmt->close();
        ob_end_flush();
        exit;
    }

    $newId = $stmt->insert_id;
    $stmt->close();
    $mysqli->close();

    echo json_encode(['success' => true, 'id' => $newId]);

} catch (Throwable $t) {
    error_log("record_fee.php exception: " . $t->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

ob_end_flush();
