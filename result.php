<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil Voting - Realtime</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Hasil Perolehan Suara</h1>
    <p class="sub">Diperbarui otomatis setiap 5 detik</p>
    <div class="stat-box">
        Total suara masuk: <span id="total-suara" style="color:#3182ce; font-weight:700;">0</span>
    </div>
    <div id="hasil-container">
        <p style="text-align:center; color:#a0aec0;">Memuat data...</p>
    </div>
    <div class="footer-nav">
        <a href="index.php">← Kembali ke voting</a>
        <a href="admin.php">Panel admin</a>
    </div>
</div>
<script>
function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function muatHasil() {
    fetch('get_hasil.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('total-suara').textContent = data.total_suara;
            const box = document.getElementById('hasil-container');

            if (!data.kandidat || data.kandidat.length === 0) {
                box.innerHTML = '<p style="text-align:center; color:#a0aec0;">Belum ada data kandidat.</p>';
                return;
            }

            box.innerHTML = data.kandidat.map(item => {
                const pct = Number(item.persen) || 0;
                return `
                    <div class="item">
                        <div class="header">
                            <span>${escapeHtml(item.nama)}</span>
                            <span style="color:#718096;">${item.jumlah} suara (${pct}%)</span>
                        </div>
                        <div class="bar-bg">
                            <div class="bar-fill" style="width:${pct}%;">${pct}%</div>
                        </div>
                    </div>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('hasil-container').innerHTML =
                '<p style="text-align:center; color:#e53e3e;">Gagal memuat data.</p>';
        });
}

muatHasil();
setInterval(muatHasil, 5000);
</script>
</body>
</html>
