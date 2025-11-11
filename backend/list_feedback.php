<?php
// backend/list_feedback.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  http_response_code(403); echo json_encode(['success'=>false,'error'=>'forbidden']); exit;
}

try {
  $res = $mysqli->query("SELECT f.id, f.user_id, f.name, f.email, f.message, f.created_at FROM feedback f ORDER BY f.created_at DESC");
  if (!$res) { error_log("list_feedback query: ".$mysqli->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $mysqli->close(); exit; }
  $rows=[]; while($r=$res->fetch_assoc()) $rows[]=$r;
  echo json_encode($rows); $mysqli->close();
} catch (Throwable $t) {
  error_log("list_feedback exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
