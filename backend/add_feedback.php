<?php
// backend/add_feedback.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
session_start();

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'error'=>'POST required']); exit;
  }
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? null);
  $message = trim($_POST['message'] ?? '');
  $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

  if ($name === '' || $message === '') {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'name and message required']); exit;
  }

  $stmt = $mysqli->prepare("INSERT INTO feedback (user_id,name,email,message) VALUES (?,?,?,?)");
  $stmt->bind_param('isss', $user_id, $name, $email, $message);
  if (!$stmt->execute()) {
    error_log("add_feedback exec: ".$stmt->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $stmt->close(); exit;
  }
  $id = $stmt->insert_id; $stmt->close(); $mysqli->close();
  echo json_encode(['success'=>true,'id'=>$id]);
} catch (Throwable $t) {
  error_log("add_feedback exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
