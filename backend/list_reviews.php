<?php
// backend/list_reviews.php
ini_set('display_errors',0); error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$patent_id = isset($_GET['patent_id']) ? trim($_GET['patent_id']) : null;

try {
  if ($patent_id) {
    $stmt = $mysqli->prepare("SELECT r.id, r.patent_id, r.examiner_id, e.name as examiner_name, r.decision, r.comments, r.created_at FROM reviews r LEFT JOIN examiners e ON r.examiner_id = e.id WHERE r.patent_id = ? ORDER BY r.created_at DESC");
    $stmt->bind_param('i',$patent_id);
    $stmt->execute();
    $res = $stmt->get_result();
  } else {
    $res = $mysqli->query("SELECT r.id, r.patent_id, r.examiner_id, e.name as examiner_name, r.decision, r.comments, r.created_at FROM reviews r LEFT JOIN examiners e ON r.examiner_id = e.id ORDER BY r.created_at DESC");
    if (!$res) { error_log("list_reviews query: ".$mysqli->error); http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']); $mysqli->close(); exit; }
  }
  $rows=[]; while($r=$res->fetch_assoc()) $rows[]=$r;
  echo json_encode($rows); if(isset($stmt)) $stmt->close(); $mysqli->close();
} catch (Throwable $t) {
  error_log("list_reviews exception: ".$t->getMessage());
  http_response_code(500); echo json_encode(['success'=>false,'error'=>'Server error']);
}
