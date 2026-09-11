<?php
require_once 'config.php';

$ip  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$sid = session_id();

$s = mysqli_prepare($conn, "SELECT id FROM vote WHERE session_id = ? OR ip_address = ? LIMIT 1");
mysqli_stmt_bind_param($s, 'ss', $sid, $ip);
mysqli_stmt_execute($s);
mysqli_stmt_store_result($s);
$sudah_vote = mysqli_stmt_num_rows($s) > 0;
mysqli_stmt_close($s);

$error   = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

$kandidat_list = [];
$r = mysqli_query($conn, "SELECT * FROM kandidat ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($r)) {
    $kandidat_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fraud Proof Voting</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Web-based Voting</h1>
    <p class="sub">Gunakan hak suara kamu dengan jujur dan terbuka</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($sudah_vote): ?>
        <div class="alert alert-info">
            <strong>Partisipasi Terverifikasi</strong> — kamu sudah memberikan suara di sesi ini.
        </div>
        <a href="result.php" class="btn btn-block" style="background:#38a169;">Lihat Hasil Voting</a>
    <?php else: ?>
        <?php if (empty($kandidat_list)): ?>
            <p style="text-align:center; color:#a0aec0; margin-top:20px;">Belum ada kandidat tersedia.</p>
        <?php else: ?>
            <?php foreach ($kandidat_list as $k): ?>
            <div class="card">
                <h3><?= e($k['nama_kandidat']) ?></h3>
                <p><?= nl2br(e($k['deskripsi'])) ?></p>
                <form action="proses_vote.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                    <input type="hidden" name="kandidat_id" value="<?= (int)$k['id'] ?>">
                    <button type="submit" class="btn" onclick="return confirm('Pilih kandidat ini? Pilihan bersifat final.')">
                        Pilih Kandidat Ini
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="footer-nav">
        <a href="result.php">Pantau hasil live</a>
        <a href="admin.php">Panel admin</a>
    </div>
</div>
</body>
</html>
