<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../config/koneksi.php';

$idPenjual = isset($_GET['idPenjual']) ? (int)$_GET['idPenjual'] : 0;

if ($idPenjual == 0) {
    echo json_encode(["status" => "error", "message" => "ID Penjual tidak ditemukan!"]);
    exit;
}

$query = "SELECT 
            o.id,
            o.total_harga AS total,
            o.status,
            o.tanggal_pesan,
            o.jam_ambil,
            u.nama AS pemesan,
            GROUP_CONCAT(CONCAT(m.nama_menu, ' (x', od.jumlah, ')') SEPARATOR '\n') AS detail
          FROM orders o
          JOIN users u ON u.id = o.id_mahasiswa
          JOIN order_details od ON od.id_order = o.id
          JOIN menus m ON m.id = od.id_menu
          WHERE o.id_penjual = $idPenjual
          GROUP BY o.id
          ORDER BY o.id DESC";

$result = mysqli_query($koneksi, $query);

if ($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = "#ORD-" . $row['id'];
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>