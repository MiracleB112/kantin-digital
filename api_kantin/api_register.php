<?php
// Matikan tampilan error PHP ke output (biar tidak merusak JSON),
// tapi tetap dicatat ke log server untuk debugging.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Tangani preflight request CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Bungkus semuanya supaya error fatal apa pun tetap menghasilkan JSON, bukan HTML/500.
set_exception_handler(function ($e) {
    http_response_code(200); // tetap 200 agar Dio tidak menganggapnya badResponse
    echo json_encode([
        "status"  => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(200);
        echo json_encode([
            "status"  => "error",
            "message" => "Server error: " . $error['message']
        ]);
    }
});

include '../config/koneksi.php';

// Pastikan koneksi database berhasil
if (!isset($koneksi) || $koneksi->connect_error) {
    echo json_encode([
        "status"  => "error",
        "message" => "Koneksi database gagal: " . ($koneksi->connect_error ?? 'unknown')
    ]);
    exit;
}

// Pastikan menangkap data dari $_POST
$nama     = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role     = isset($_POST['role']) ? trim($_POST['role']) : '';

if (empty($nama) || empty($email) || empty($password) || empty($role)) {
    echo json_encode([
        "status"  => "error",
        "message" => "Data tidak lengkap. Pastikan semua field terisi."
    ]);
    exit;
}

// Validasi format email sederhana
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status"  => "error",
        "message" => "Format email tidak valid."
    ]);
    exit;
}

// Cek apakah email sudah terdaftar lebih dulu, supaya pesan errornya jelas
$cek = $koneksi->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$cek->bind_param("s", $email);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    echo json_encode([
        "status"  => "error",
        "message" => "Email sudah terdaftar. Silakan gunakan email lain atau login."
    ]);
    $cek->close();
    $koneksi->close();
    exit;
}
$cek->close();

// Hash password sebelum disimpan (JANGAN simpan plain text)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama, $email, $passwordHash, $role);

if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Pendaftaran berhasil"
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => "Gagal mendaftar: " . $stmt->error
    ]);
}

$stmt->close();
$koneksi->close();
