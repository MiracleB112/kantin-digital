<?php
$koneksi = mysqli_connect(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    getenv('MYSQLPORT')
);

if (!$koneksi) {
    // Jangan pakai die() dengan plain text — ini merusak response JSON
    // yang diharapkan oleh semua file api_*.php yang include file ini.
    header("Content-Type: application/json");
    http_response_code(200); // tetap 200 agar Dio/Flutter tidak menganggapnya badResponse
    echo json_encode([
        "status"  => "error",
        "message" => "Koneksi database gagal: " . mysqli_connect_error()
    ]);
    exit;
}

date_default_timezone_set('Asia/Jakarta');
mysqli_query($koneksi, "SET time_zone = '+07:00'");
?>
