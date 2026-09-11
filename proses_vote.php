<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Metode tidak valid.';
    header('Location: index.php'); exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Sesi expired, silakan coba lagi.';
    header('Location: index.php'); exit;
}

$raw_id = $_POST['kandidat_id'] ?? '';
if ($raw_id === '' || !ctype_digit((string)$raw_id)) {
    $_SESSION['error'] = 'ID kandidat tidak valid.';
    header('Location: index.php'); exit;
}

$kandidat_id = (int)$raw_id;
$ip          = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$sid         = session_id();

$cek = mysqli_prepare($conn, "SELECT id FROM kandidat WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($cek, 'i', $kandidat_id);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);
if (mysqli_stmt_num_rows($cek) === 0) {
    mysqli_stmt_close($cek);
    $_SESSION['error'] = 'Kandidat tidak ditemukan.';
    header('Location: index.php'); exit;
}
mysqli_stmt_close($cek);

$cek2 = mysqli_prepare($conn, "SELECT id FROM vote WHERE session_id = ? OR ip_address = ? LIMIT 1");
mysqli_stmt_bind_param($cek2, 'ss', $sid, $ip);
mysqli_stmt_execute($cek2);
mysqli_stmt_store_result($cek2);
if (mysqli_stmt_num_rows($cek2) > 0) {
    mysqli_stmt_close($cek2);
    $_SESSION['error'] = 'Kamu sudah pernah melakukan vote.';
    header('Location: index.php'); exit;
}
mysqli_stmt_close($cek2);

$ins = mysqli_prepare($conn, "INSERT INTO vote (kandidat_id, ip_address, session_id) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($ins, 'iss', $kandidat_id, $ip, $sid);

if (mysqli_stmt_execute($ins)) {
    $_SESSION['success'] = 'Suara berhasil dikirim, terima kasih!';
} else {
    $_SESSION['error'] = 'Gagal menyimpan suara, coba lagi.';
}
mysqli_stmt_close($ins);
header('Location: index.php'); exit;
