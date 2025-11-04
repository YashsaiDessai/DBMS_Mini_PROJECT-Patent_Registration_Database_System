<?php

$host = "localhost";
$user = "root";
$pass = '';
$dbname = "patent_registry";

$mysqli = new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_errorno){
    http_response_code(500);
    echo json_encode(["error" => "DB connection failed: " . $mysqli->connect_error]);
    exit;
}

$mysqli->set_charset("utf8mb4");
?>
