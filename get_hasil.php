<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$res   = mysqli_query($conn, "SELECT COUNT(*) AS total FROM vote");
$total = (int) mysqli_fetch_assoc($res)['total'];

$q = "SELECT k.id, k.nama_kandidat, COUNT(v.id) AS jumlah
      FROM kandidat k
      LEFT JOIN vote v ON v.kandidat_id = k.id
      GROUP BY k.id, k.nama_kandidat
      ORDER BY k.id ASC";

$kandidat = [];
$r = mysqli_query($conn, $q);
while ($row = mysqli_fetch_assoc($r)) {
    $jml    = (int)$row['jumlah'];
    $persen = $total > 0 ? round(($jml / $total) * 100, 1) : 0;
    $kandidat[] = [
        'id'     => (int)$row['id'],
        'nama'   => $row['nama_kandidat'],
        'jumlah' => $jml,
        'persen' => $persen,
    ];
}

echo json_encode([
    'status'      => 'ok',
    'total_suara' => $total,
    'kandidat'    => $kandidat,
], JSON_UNESCAPED_UNICODE);
