<?php
// backend/register.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'error'=>'POST required']); exit;
  }
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($name === '' || $email === '' || $password === '') {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'name,email,password required']); exit;
  }

  // basic email validation
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'invalid email']); exit;
  }

  // check exists
  $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->close();
    http_response_code(409);
    echo json_encode(['success'=>false,'error'=>'email exists']);
    exit;
  }
  $stmt->close();

  $hash = password_hash($password, PASSWORD_DEFAULT);
  $ins = $mysqli->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)");
  $ins->bind_param('sss', $name, $email, $hash);
  if (!$ins->execute()) {
    error_log("register exec: ".$ins->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $ins->close(); exit;
  }
  $id = $ins->insert_id;
  $ins->close();
  echo json_encode(['success'=>true,'id'=>$id]);
} catch (Throwable $t) {
  error_log("register exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
