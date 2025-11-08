<?php
// backend/add_review.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'error'=>'POST required']); exit; }
  $patent_id = trim($_POST['patent_id'] ?? '');
  $examiner_id = trim($_POST['examiner_id'] ?? '');
  $decision = trim($_POST['decision'] ?? '');
  $comments = trim($_POST['comments'] ?? '');

  if ($patent_id === '' || $examiner_id === '' || $decision === '') {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'patent_id, examiner_id and decision required']); exit;
  }

  $stmt = $mysqli->prepare("INSERT INTO reviews (patent_id, examiner_id, decision, comments) VALUES (?, ?, ?, ?)");
  if (!$stmt) { error_log("add_review prepare: ".$mysqli->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); exit; }
  $stmt->bind_param('iiss', $patent_id, $examiner_id, $decision, $comments);
  if (!$stmt->execute()) { error_log("add_review exec: ".$stmt->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $stmt->close(); exit; }
  $newId = $stmt->insert_id; $stmt->close(); $mysqli->close();
  echo json_encode(['success'=>true,'id'=>$newId]);
} catch (Throwable $t) {
  error_log("add_review exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
