<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include '../config/koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
$idMenu = isset($data['id_menu']) ? (int)$data['id_menu'] : 0;

if ($idMenu == 0) {
    echo json_encode(["status" => "error", "message" => "ID Menu tidak valid"]);
    exit;
}

$cek = mysqli_query($koneksi, "SELECT status FROM menus WHERE id = $idMenu");
$row = mysqli_fetch_assoc($cek);

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Menu tidak ditemukan"]);
    exit;
}

$statusSekarang = strtolower($row['status']);
$statusBaru = ($statusSekarang === 'tersedia') ? 'habis' : 'tersedia';
$query = "UPDATE menus SET status = '$statusBaru' WHERE id = $idMenu";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(["status" => "success", "status_baru" => $statusBaru]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>