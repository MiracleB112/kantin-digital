<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../config/koneksi.php';

$idPenjual = isset($_GET['idPenjual']) ? (int)$_GET['idPenjual'] : 0;

$query = "SELECT id, nama_menu AS name, deskripsi AS `desc`, harga AS price, status, foto AS img 
          FROM menus WHERE id_penjual = $idPenjual ORDER BY id DESC";

$result = mysqli_query($koneksi, $query);

if ($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>