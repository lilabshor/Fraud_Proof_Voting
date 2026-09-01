<?php
    // parameter cookie sing aman yo rek
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params([
       'lifetime' => 0,
       'path' => '/',
       'domain' => '',
       'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on',
       'httponly' => true,
       'samesite' => 'Lax'
    ]);
    session_start();
}
//iki konfig nag database rek
$db_host = "";
$db_user = "";
$db_pass = "";
$db_name = "";
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die ("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
define('ADMIN_USERNAME', '');
define('ADMIN_PASSWORD', '');

//iki generate token csrf e abangku
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

