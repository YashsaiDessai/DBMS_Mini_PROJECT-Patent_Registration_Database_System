<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "test_db_include starting\n";
require_once __DIR__ . '/db.php';
echo "db included. mysqli object class: " . (isset($mysqli) ? get_class($mysqli) : 'NO mysqli') . "\n";
