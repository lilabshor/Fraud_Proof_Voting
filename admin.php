<?php
require_once "config.php";

$is_admin = !empty($_SESSION['is_admin']);
$errors = [];
$succes = $_SESSION['success'] ?? null;
$error_hapus = $_SESSION['error'] ?? null;
unset($_SESSION['success'] , $_SESSION['error_hapus']);

if (isset($_POST['submit']) && $_POST['submit'] == 'login') {
    if (!verify_csrf_token($_POST['csrf'] ?? ``)) {
        $errors[] = "Csrf token tidak valid";
    }else {
        $u = trim($_POST['username'] ?? '');
        $p = trim($_POST['password'] ?? '');
        if ($u === ADMIN_USERNAME && $p === ADMIN_PASSWORD) {
            $_SESSION['is_admin'] = true;
            header('location: admin.php');
            exit();
        } else {
            $errors[] = "Username atau password salah";
        }
    }
}

