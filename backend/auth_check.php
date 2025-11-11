<?php
// backend/auth_check.php
header('Content-Type: application/json; charset=utf-8');
session_start();
if (isset($_SESSION['user_id'])) {
  echo json_encode(['logged_in'=>true,'user_id'=>$_SESSION['user_id'],'name'=>$_SESSION['user_name'],'role'=>$_SESSION['role']]);
} else {
  echo json_encode(['logged_in'=>false]);
}
