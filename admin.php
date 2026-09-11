<?php
require_once 'config.php';

$is_admin = !empty($_SESSION['is_admin']);
$errors   = [];
$success  = $_SESSION['success'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

if (($_POST['submit'] ?? '') === 'login') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token tidak valid.';
    } else {
        $u = trim($_POST['username'] ?? '');
        $p = trim($_POST['password'] ?? '');
        if ($u === ADMIN_USERNAME && $p === ADMIN_PASSWORD) {
            $_SESSION['is_admin'] = true;
            header('Location: admin.php'); exit;
        } else {
            $errors[] = 'Username atau password salah.';
        }
    }
}

if (($_POST['submit'] ?? '') === 'logout') {
    session_destroy();
    header('Location: admin.php'); exit;
}

if (!$is_admin):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>body { display:flex; align-items:center; justify-content:center; }</style>
</head>
<body>
<div class="card" style="width:100%; max-width:380px; margin-top:80px;">
    <h3 style="text-align:center; margin-bottom:16px;">Login Admin</h3>
    <?php foreach ($errors as $err): ?>
        <p style="color:#e53e3e; font-size:13px; margin-bottom:8px;"><?= e($err) ?></p>
    <?php endforeach; ?>
    <form action="admin.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="submit" value="login">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn" style="width:100%;">Masuk</button>
    </form>
    <p style="text-align:center; margin-top:12px; font-size:12px;">
        <a href="index.php" style="color:#38a169;">← Kembali ke halaman voting</a>
    </p>
</div>
</body>
</html>
<?php exit; endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['submit'] ?? '';

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token tidak valid.';
    } elseif ($aksi === 'tambah') {
        $nama      = trim($_POST['nama_kandidat'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        if ($nama === '') {
            $errors[] = 'Nama kandidat wajib diisi.';
        } else {
            $s = mysqli_prepare($conn, "INSERT INTO kandidat (nama_kandidat, deskripsi) VALUES (?, ?)");
            mysqli_stmt_bind_param($s, 'ss', $nama, $deskripsi);
            mysqli_stmt_execute($s);
            mysqli_stmt_close($s);
            $_SESSION['success'] = 'Kandidat berhasil ditambahkan.';
            header('Location: admin.php'); exit;
        }
    } elseif ($aksi === 'hapus') {
        $id  = (int)($_POST['id'] ?? 0);
        $cek = mysqli_prepare($conn, "SELECT id FROM vote WHERE kandidat_id = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, 'i', $id);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            mysqli_stmt_close($cek);
            $errors[] = 'Kandidat ini sudah punya suara. Reset vote dulu sebelum menghapus.';
        } else {
            mysqli_stmt_close($cek);
            $s = mysqli_prepare($conn, "DELETE FROM kandidat WHERE id = ?");
            mysqli_stmt_bind_param($s, 'i', $id);
            mysqli_stmt_execute($s);
            mysqli_stmt_close($s);
            $_SESSION['success'] = 'Kandidat berhasil dihapus.';
            header('Location: admin.php'); exit;
        }
    } elseif ($aksi === 'reset_vote') {
        mysqli_query($conn, "TRUNCATE TABLE vote");
        $_SESSION['success'] = 'Semua data suara berhasil direset.';
        header('Location: admin.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin Voting</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Panel Kontrol Admin</h2>
        <form action="admin.php" method="POST" style="margin:0;">
            <input type="hidden" name="submit" value="logout">
            <button type="submit" class="btn btn-logout">Logout</button>
        </form>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <h3>Tambah Kandidat</h3>
    <form action="admin.php" method="POST" style="margin-bottom:24px;">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="submit" value="tambah">
        <input type="text" name="nama_kandidat" placeholder="Nama paslon" required>
        <textarea name="deskripsi" placeholder="Deskripsi / visi misi" rows="2" style="margin-top:6px;"></textarea>
        <button type="submit" class="btn" style="margin-top:8px;">Simpan Kandidat</button>
    </form>

    <h3>Daftar Kandidat</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM kandidat ORDER BY id ASC");
        while ($r = mysqli_fetch_assoc($res)):
        ?>
        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><strong><?= e($r['nama_kandidat']) ?></strong></td>
            <td><?= e($r['deskripsi']) ?></td>
            <td>
                <form action="admin.php" method="POST" onsubmit="return confirm('Hapus kandidat ini?');" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
                    <input type="hidden" name="submit" value="hapus">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger" style="padding:3px 10px; font-size:11px;">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h3 style="margin-top:24px;">Reset Semua Suara</h3>
    <form action="admin.php" method="POST" onsubmit="return confirm('Yakin ingin mereset semua suara? Data tidak bisa dikembalikan.');">
        <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
        <input type="hidden" name="submit" value="reset_vote">
        <button type="submit" class="btn btn-danger">Reset Suara (Truncate Vote)</button>
    </form>
</div>
</body>
</html>
