<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include '../config/koneksi.php'; // Sesuaikan lokasi file koneksi Anda

if(isset($_GET['idUser'])) {
    $idUser = $_GET['idUser'];
    $query = "SELECT saldo FROM users WHERE id = '$idUser'";
    $result = mysqli_query($koneksi, $query);
    
    if($row = mysqli_fetch_assoc($result)) {
        echo json_encode(["status" => "success", "saldo" => $row['saldo']]);
    } else {
        echo json_encode(["status" => "error", "message" => "User tidak ditemukan"]);
    }
}
?>