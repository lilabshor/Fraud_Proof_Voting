<?php
require_once "config.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION[`error`] = `Metode request tidak valid!`;
    header('location: index.php');
    exit();
}

if(!verify_csrf_token($_POST[`csrf_token`]?? '')){
    $_SESSION[`error`] = `Sesi Telah Expired!`;
    header("location: index.php");
    exit();
}

$raw_kandidat_id = $_POST['kandidat_id'] ?? '';
if ($raw_kandidat_id === '' || !ctype_digit((string) $raw_kandidat_id)) {
    $_SESSION[`error`] = `kandidat ID not valid!`;
    header("location: index.php");
    exit();
}

$kandidat_id = (int) $raw_kandidat_id;
$ip = $_SERVER['REMOTE_ADDR'] ?? `127.0.0.1`;
$session_id = session_id();
