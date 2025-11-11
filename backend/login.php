<?php
// backend/login.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
session_start();

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'error'=>'POST required']); exit;
  }
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($email === '' || $password === '') {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'email and password required']); exit;
  }

  $stmt = $mysqli->prepare("SELECT id,name,password_hash,role FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'invalid credentials']);
    exit;
  }

  // Success: set session
  session_regenerate_id(true);
  $_SESSION['user_id'] = (int)$user['id'];
  $_SESSION['user_name'] = $user['name'];
  $_SESSION['role'] = $user['role'];

  echo json_encode(['success'=>true,'id'=>$user['id'],'name'=>$user['name'],'role'=>$user['role']]);
} catch (Throwable $t) {
  error_log("login exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
