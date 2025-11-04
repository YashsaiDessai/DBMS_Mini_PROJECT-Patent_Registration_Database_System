<?php
require_once "db.php";

if ($mysqli->connect_errno) {
  echo "❌ Connection failed: " . $mysqli->connect_error;
} else {
  echo "✅ Connected successfully to database: " . $mysqli->host_info;
}
?>
