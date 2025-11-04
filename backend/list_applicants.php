<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$res = $mysqli->query("SELECT id, name , email , organization, created_at FROM applicants ORDER BY created_at DESC");
if (!$res) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $mysqli->error]);
    exit;
}

$rows =[];
while ($r = $res->fetch_assoc()) $rows[] =$r;
echo json_encode($rows);

$mysqli->close();