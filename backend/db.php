<?php
// backend/db.php — production-safe
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "patent_registry";

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    error_log("DB connect failed: " . $mysqli->connect_error);
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'DB connection error']);
    exit;
}
$mysqli->set_charset("utf8mb4");
?>
