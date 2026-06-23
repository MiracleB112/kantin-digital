<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include '../config/koneksi.php';

// Ambil id_menu yang dikirim oleh Flutter
$id_menu = isset($_GET['id_menu']) ? (int)$_GET['id_menu'] : 0;

if (isset($_GET['id_menu'])) {
    $id_menu = (int)$_GET['id_menu'];
    
    // Gunakan query ini untuk mengetes
    $sql = "SELECT u.nama AS nama_user, r.rating, r.ulasan AS komentar 
            FROM ratings r
            JOIN users u ON r.id_mahasiswa = u.id
            WHERE r.id_menu = '$id_menu' 
            ORDER BY r.created_at DESC";
            
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        $list_ulasan = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $list_ulasan[] = [
                "nama_user" => $row['nama_user'],
                "rating" => (int)$row['rating'],
                "komentar" => $row['komentar']
            ];
        }
        echo json_encode(["status" => "success", "data" => $list_ulasan]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "ID Menu tidak valid"]);
}
?>