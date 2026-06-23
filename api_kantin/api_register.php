<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
include '../config/koneksi.php';

// Pastikan menangkap data dari $_POST
$nama     = isset($_POST['nama']) ? $_POST['nama'] : '';
$email    = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role     = isset($_POST['role']) ? $_POST['role'] : '';

if (!empty($nama) && !empty($email) && !empty($password) && !empty($role)) {
    // Gunakan prepared statement untuk keamanan
    $stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $email, $password, $role);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pendaftaran berhasil"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal: " . $stmt->error]);
    }
} else {
    // Jika pesan ini muncul, berarti data yang dikirim Flutter kosong/null
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap: " . json_encode($_POST)]);
}
?>