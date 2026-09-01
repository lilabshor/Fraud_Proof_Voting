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

