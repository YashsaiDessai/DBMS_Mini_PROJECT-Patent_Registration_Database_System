<?php
header('Content-Type : application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$organization = trim($_POST['organization'] ?? '');

if ($name === '') {
    http_response_code(400);
    echo json_encode(['success'=> false ,"error" => "Name is required"]);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO applicants (name, email, organization) VALUES (?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success'=> false ,"error" => $mysqli->error]);
    exit;
}

$stmt->bind_param("sss", $name, $email, $organization);

if ($stmt->execute()) {
    echo json_encode(['success'=> true, 'id' => $stmt->insert_id]);
}else{
    http_response_code(500);
    echo json_encode(['success'=> false ,"error" => $stmt->error]);
}
$stmt->close();
$mysqli->close();