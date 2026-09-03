<?php
require("config.php");
?>
<!DOCTYPE html>
<html>

<head >

    <title>hasil perolehan suara - realtime</title>
    <style>
        * {box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif}
    </style>
</head>
<body>
    <div class="container" >
        <h1>Hasil perolehan Surara</h1>
        <p class="sub"> pembaruan otomatis setiap 5 detik </p>
        <div class="stat-box">Total suara masuk : <span id="total-suara" style="color: #3182ce">0</span></div>
        <div id="hasil-container"><p style="text-align: center ; color: #a0aec0;">Memuat perolehan suara...</p> </div>
        <div class="footer-nav" >
            <a href="index.php"> Kembali ke halaman voting</a>
            <a href="admin.php"> Panel admin</a>
        </div>
    </div>

    <script>
        function escapeHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function muatHasil(){
            fetch("get_hasil.php")
                .then(res => res.json())
                .then(payload => {
                    document.getElementById("total-suara").textContent = payload.total_suara;
                    const  container = document.getElementById("hasil-container");
                    if (!payload.kandidat || payload.kandidat.length === 0) {
                        container.innerHTML = '<p style="text-align: center ; color: #a0aec0;"> belum ada data kandidat. </p>';
                        return ;
                    }
                    let html = "";
                    payload.kandidat.forEach(item => {
                        const  pct = Number(item.persen) || 0;
                        html +=
                            <div class = "item" >
                                <div class = "header" >
                                    <span>${escapeHtml(item.name)}</span>
                                    <span style="color:#718096;">${item.jumlah} suara (${pct}%)</span>
                                </div>
                                <div class "bar-bg">
                                    <div class="bar-fill" style="width:${pct}%;"> ${pct}%</div>

                                </div>
                            </div>
                    });
                    container.innerHTML = html;
                })
                .cetch (() => {
                    document.getElementById("hasil-container").innerHTML = '<p style="text-align:center ; color: #e53e3e"> Gagal memuat ulang data </p>'
                })
        }
        muatHasil();
        setInterval(muatHasil, 5000);

    </script>

</body>

</html>

