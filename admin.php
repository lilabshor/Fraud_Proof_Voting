<?php
global $conn;
require_once "config.php";

$is_admin = !empty($_SESSION['is_admin']);
$errors = [];
$succes = $_SESSION['success'] ?? null;
$error_hapus = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error_hapus']);

if (isset($_POST['submit']) && $_POST['submit'] == 'login') {
    if (!verify_csrf_token($_POST['csrf'] ?? ``)) {
        $errors[] = "Csrf token tidak valid";
    } else {
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

if (isset($_POST['submit']) && $_POST['submit'] == 'logout') {
    unset($_SESSION['is_admin']);
    header('location: admin.php');
    exit();
}

if ($is_admin) :
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Admin</title>
        <style>

        </style>
    </head>
    <body>
    <div class="card">
        <h3 style="text-align: center; margin-bottom: 15px;">Login Admin vote</h3>
        <?php foreach ($errors as $e) : ?> <p style="color: red; font-size: 13px;"><?= e($e) ?></p><?php endforeach ?>
        <form action="admin.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="submit" value="login">
            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>password</label><input type="password" name="password" required></div>
            <button type="submit" class="btn">Masuk</button>
        </form>
        <p style="text-align: center; margin-top: 10px;"><a href="index.php"
                                                            style="font-size: 12px; color: #38a169">Kembali</a></p>
    </div>
    </body>
    </html>
    <?php exit; endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['submit'] ?? ``) === 'tambah') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? ``)) {
        $errors[] = "Csrf token tidak valid";
    } else {
        $nama = trim($_POST[`nama_kandidat`] ?? '');
        $deskripsi = trim($_POST[`deskripsi_kandidat`] ?? '');
        if ($nama === '') {
            $errors[] = "Nama kandidat tidak boleh kosong";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO kandidat (nama_kandidat, deskripsi_kandidat, deskripsi) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $nama, $deskripsi);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['success'] = "Kandidat berhasil ditambahkan";
            header('location: admin.php');
            exit();
        }

    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['submit'] ?? ``) === 'hapus') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? ``)) {
        $errors[] = "Csrf token tidak valid";
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $cek = mysqli_prepare($conn, "SELECT id FROM vote WHERE kandidat_id = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, "i", $id);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            mysqli_stmt_close($cek);
            $_SESSION['error_hapus'] = "kandidat ini sudah memiliki vote. reset vote dulu jika ingin menghapus kandidat";
            header('location: admin.php');
            exit();
        }
        mysqli_stmt_close($cek);
        $stmt = mysqli_prepare($conn, "DELETE FROM kandidat WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Kandidat berhasil dihapus";
        header('location: admin.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['submit'] ?? ``) === 'reset_vote') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? ``)) {
        $errors[] = "Csrf token tidak valid";
    } else {
        mysqli_query($conn, "TRUNCATE TABLE vote");
        $_SESSION['success'] = "Kandidat berhasil direset vote";
        header('location: admin.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html >
    <head>
        <title> Panel Admin voting</title>
        <style>

        </style>
    </head>

    <body>
        <div class="container">
            <div class="header" >
                <h2>Panel Kontrol Admin</h2>
                <form action="admin.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="submit" value="logout">
                    <button type="submit" class="btn btn-logout">Logout</button>
                </form>
            </div>
        </div>
        <?php if ($succes) : ?><p style="color: green;"><?= e($succes) ?></p> <?php endif; ?>
        <?php if ($errors) : ?><p style="color: red;"><?= e($errors) ?></p> <?php endif; ?>

        <h3>TAMBAH kandidat Baru</h3>
        <form action="admin.php" method="POST" style="margin-bottom: 25px;">
            <input type="hidden" name="csrf_token" value="<?= e(generate_csrf_token()) ?>">
            <input type="hidden" name="submit" value="tambah">
            <input type="text" name="nama_kandidat" placeholder="nama paslon" required style="width: 100%; margin: 4px 0 8px"; >
            <textarea name="deskripsi" placeholder="deskripsi visi misi" rows ="2" style="width: 100%; padding: 6px;"></textarea>
            <button type="submit" class="btn" style="margin-top: 6px;">Simpan Kandidat</button>
        </form>
    </body>
</html>
