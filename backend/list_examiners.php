<?php
// backend/list_examiners.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
try {
  $res = $mysqli->query("SELECT id,name,email,organization,created_at FROM examiners ORDER BY name ASC");
  if (!$res) { error_log("list_examiners query: ".$mysqli->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $mysqli->close(); exit; }
  $rows=[]; while($r=$res->fetch_assoc()) $rows[]=$r;
  echo json_encode($rows); $mysqli->close();
} catch (Throwable $t) {
  error_log("list_examiners exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
