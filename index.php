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

$error = $_SESSION[`error`] ?? null;
$success = $_SESSION[`success`] ?? null;
unset($_SESSION[`error`], $_SESSION[`success`]);

$kandidat_list = [];
$query_kandidat = mysqli_query($conn, "SELECT * FROM kandidat ORDER BY id ASC");
if ($query_kandidat) {
    while ($row = mysqli_fetch_assoc ($query_kandidat)) {
        $kandidat_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fraud Proof Voting</title>
    <style>
        * {box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif}

    </style>
</head>

<body>
<div class="container" >
    <h1>Web-based voting</h1>
    <P class="sub">Gawe en hak suoro sing jujur lan terbuka</P>

    <?php if ($error) : ?> <div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success) : ?> <div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <?php if ($after_vote) : ?>
    <div class=" alert alert-info">
        <strong>Partisipasi Terverifikasi</strong> sampean wes ngekek i suoro pas sesi iki. suon pak/buk/mbah/nak!
    </div>
    <a href="result.php" class="btn btn-block" style="background: #38a169;">delok hasil asli voting</a>
    <?php else: ?>

    <?php foreach ($kandidat_list as $k) : ?>
    <div class="card">
        <h3><?= e($k[`nama_kandidat`]) ?></h3>
        <p><?= n12br (e($k['deskripsi']))?></p>
        <form action="proses_vote.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="kandidat_id" value="<?= (int) $k['id'] ?>">
            <button type="submit" class="btn" onclick="return confirm('pilih kandidat ini? pilihan bersifat final.');" >
                pilih kandidat ini
            </button>
        </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="footer-nav">
        <a href="result.php" > Pantau hasil live</a>
        <a href="admin.php" > panel admin</a>
    </div>
</div>
</body>
</html>
