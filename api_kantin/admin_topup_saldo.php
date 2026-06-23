<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include '../config/koneksi.php';

// Data dikirim sebagai JSON dari Flutter
$json_data = file_get_contents('php://input');
$input = json_decode($json_data, true);

$idMahasiswa = isset($input['id_mahasiswa']) ? (int)$input['id_mahasiswa'] : 0;
$nominal     = isset($input['nominal']) ? (int)$input['nominal'] : 0;

if ($idMahasiswa == 0 || $nominal <= 0) {
    echo json_encode(["status" => "error", "message" => "Pilih mahasiswa dan masukkan nominal top up yang valid!"]);
    exit;
}

// Pastikan user target benar-benar mahasiswa
$cek = mysqli_query($koneksi, "SELECT id, nama, saldo FROM users WHERE id = $idMahasiswa AND role = 'mahasiswa'");
$user = mysqli_fetch_assoc($cek);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Mahasiswa tidak ditemukan!"]);
    exit;
}

$query = "UPDATE users SET saldo = saldo + $nominal WHERE id = $idMahasiswa";

if (mysqli_query($koneksi, $query)) {
    $saldoBaru = (int)$user['saldo'] + $nominal;
    echo json_encode([
        "status"     => "success",
        "message"    => "Saldo " . $user['nama'] . " berhasil ditambahkan!",
        "saldo_baru" => $saldoBaru,
    ]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>