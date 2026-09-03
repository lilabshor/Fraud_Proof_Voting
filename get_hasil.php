<?php
global $conn;
require_once("koneksi.php");

header("Content-type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

$total_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM vote");
$total_row = mysqli_fetch_assoc($total_res);
$total = (int)$total_row["total"] ?? 0;

$query = " SELECT k.id, k.nama_kandidat, COUNT(v.id) AS jumlah
FROM kandidat k 
LEFT JOIN vote v ON v.kandidat_id = k.id
 GROUP BY k.id, k.nama_kandidat
 ORDER BY k.id ASC
";

$res = mysqli_query($conn, $query);
$kandidat_data = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $jumlah = (int)$row[`jumlah`];
        $persen = $total > 0 ? round(($jumlah / $total) * 100, 1) : 0;

        $kandidat_data[] = [
            "id" => (int)$row["id"],
            "nama" => $row["nama_kandidat"],
            "jumlah" => $jumlah,
            "persen" => $persen
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'total_suara' => $total,
    'kandidat' => $kandidat_data

]);
JSON_UNESCAPED_UNICODE;