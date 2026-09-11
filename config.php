<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$conn = mysqli_connect("localhost", "", "", "voting");
if (!$conn) die("Koneksi DB gagal: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8");

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token']))
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    return !empty($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
