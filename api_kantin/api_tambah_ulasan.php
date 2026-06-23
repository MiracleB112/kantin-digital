<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Tangani preflight request (OPTIONS) dari browser
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../config/koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id_pesanan'], $data['id_menu'], $data['id_mahasiswa'], $data['rating'], $data['komentar'])) {
    $id_order = mysqli_real_escape_string($koneksi, $data['id_pesanan']);
    $id_menu = mysqli_real_escape_string($koneksi, $data['id_menu']);
    $id_mahasiswa = mysqli_real_escape_string($koneksi, $data['id_mahasiswa']);
    $rating = mysqli_real_escape_string($koneksi, $data['rating']);
    $ulasan = mysqli_real_escape_string($koneksi, $data['komentar']);

    $query = "INSERT INTO ratings (id_order, id_menu, id_mahasiswa, rating, ulasan) 
              VALUES ('$id_order', '$id_menu', '$id_mahasiswa', '$rating', '$ulasan')";

    if (mysqli_query($koneksi, $query)) {
        $queryUpdate = "UPDATE orders SET sudah_rating = 'sudah' WHERE id = '$id_order'";
        mysqli_query($koneksi, $queryUpdate);

        echo json_encode(["status" => "success", "message" => "Rating berhasil disimpan"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal simpan ke DB: " . mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>