<?php
// backend/add_examiner.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'error'=>'POST required']); exit;
  }
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $org  = trim($_POST['organization'] ?? '');
  if ($name === '') { http_response_code(400); echo json_encode(['success'=>false,'error'=>'name required']); exit;}
  $stmt = $mysqli->prepare("INSERT INTO examiners (name,email,organization) VALUES (?, ?, ?)");
  if (!$stmt) { error_log("add_examiner prepare: ".$mysqli->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); exit;}
  $stmt->bind_param('sss',$name,$email,$org);
  if (!$stmt->execute()) { error_log("add_examiner exec: ".$stmt->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $stmt->close(); exit;}
  $id = $stmt->insert_id; $stmt->close(); $mysqli->close();
  echo json_encode(['success'=>true,'id'=>$id]);
} catch (Throwable $t) {
  error_log("add_examiner exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
