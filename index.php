<?php

global $conn;
require_once "config.php";

$ip = $_SERVER['REMOTE_ADDR'] ?? `127.0.0.1`;
$session_id = session_id();

$stmt = mysqli_prepare($conn, "SELECT id FROM vote WHERE session_id = ? OR ip_address = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $session_id, $ip);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$after_vote = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

