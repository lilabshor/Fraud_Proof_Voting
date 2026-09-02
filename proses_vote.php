<?php
require_once "config.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION[`error`] = `Metode request tidak valid!`;
    header('location: index.php');
    exit();
}

