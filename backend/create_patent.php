<?php
// backend/create_patent.php
// Returns only JSON. Logs errors server-side.

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

  $title = isset($_POST['title']) ? trim($_POST['title']) : '';
  $appnum = isset($_POST['application_number']) ? trim($_POST['application_number']) : '';
  $filing_date = isset($_POST['filing_date']) ? trim($_POST['filing_date']) : null;

  if (!$title || !$appnum) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'title and application_number required']);
    ob_end_flush();
    exit;
  }

  $stmt = $mysqli->prepare("INSERT INTO patents (title, application_number, filing_date) VALUES (?, ?, ?)");
  if (!$stmt) {
    error_log("create_patent.php prepare failed: " . $mysqli->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    ob_end_flush();
    exit;
  }

  $stmt->bind_param('sss', $title, $appnum, $filing_date);
  if (!$stmt->execute()) {
    error_log("create_patent.php execute failed: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    $stmt->close();
    ob_end_flush();
    exit;
  }

  $id = $stmt->insert_id;
  $stmt->close();
  $mysqli->close();

  echo json_encode(['success' => true, 'id' => $id]);

} catch (Throwable $t) {
  error_log("create_patent.php exception: " . $t->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Server error']);
}

ob_end_flush();
