<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../config/koneksi.php';

$idUser = isset($_GET['idUser']) ? mysqli_real_escape_string($koneksi, $_GET['idUser']) : null;

$query = "SELECT 
            o.id, 
            o.tanggal_pesan AS tanggal, 
            o.total_harga AS total, 
            o.status, 
            o.sudah_rating AS rateable,
            u.nama AS tenant,
            MIN(od.id_menu) AS id_menu,
            GROUP_CONCAT(CONCAT(m.nama_menu, ' (x', od.jumlah, ')') SEPARATOR ', ') AS item
          FROM orders o
          JOIN users u ON u.id = o.id_penjual
          JOIN order_details od ON od.id_order = o.id
          JOIN menus m ON m.id = od.id_menu
          WHERE o.id_mahasiswa = '$idUser'
          GROUP BY o.id
          ORDER BY o.id DESC";

$result = mysqli_query($koneksi, $query);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['no_order'] = "#ORD-" . $row['id'];
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>