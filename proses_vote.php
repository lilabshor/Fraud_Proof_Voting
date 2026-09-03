<?php
global $conn;
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

$stmt_kandidat = mysqli_prepare($conn, "SELECT id FROM kandidat WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_kandidat, "i", $kandidat_id);
mysqli_stmt_execute($stmt_kandidat);
mysqli_stmt_store_result($stmt_kandidat);

if(mysqli_stmt_num_rows($stmt_kandidat) === 0){
mysqli_stmt_close($stmt_kandidat);
$_SESSION['error'] = `Kandidat tidak ditemukan!`;
header("location: index.php");
exit();
}

mysqli_stmt_close($stmt_kandidat);

$stmt_check = mysqli_prepare($conn, "SELECT id FROM kandidat WHERE session_id = ? OR ip_address = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_check, "ss", $session_id, $ip);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if(mysqli_stmt_num_rows($stmt_check) > 0) {
    mysqli_stmt_close($stmt_check);
    $_SESSION[`error`] = "Anda sudah pernah melakukan vote!";
    header("location index.php");
    exit();
}

$stmt_insert = mysqli_prepare($conn, "INSERT INTO vote (kandidat_id, ip_address) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt_insert, "is", $kandidat_id, $ip, $session_id);

if (mysqli_stmt_execute($stmt_insert)) {
    mysqli_stmt_close($stmt_insert);
    $_SESSION[`success`] = "Suara anda telah berhasil memilih kandidat terimakasih!";
    header("location index.php");
    exit();
} else {
    mysqli_stmt_close($stmt_insert);
    $_SESSION ['error'] = "terjadi kesalahan saat menyimpan vote!";
    header("location index.php");
    exit();
}


