<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include '../config/koneksi.php';

// 1. Total mahasiswa terdaftar
$q1 = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'mahasiswa'");
$totalMahasiswa = (int)mysqli_fetch_assoc($q1)['total'];

// 2. Total tenant aktif (semua user dengan role penjual)
$q2 = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'penjual'");
$totalTenant = (int)mysqli_fetch_assoc($q2)['total'];

// 3. Total perputaran uang (total transaksi yang sudah selesai)
$q3 = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) AS total FROM orders WHERE status = 'selesai'");
$totalPerputaran = (int)mysqli_fetch_assoc($q3)['total'];

// 4. Data mahasiswa (id, nama, email, saldo)
$q4 = mysqli_query($koneksi, "SELECT id, nama, email, saldo FROM users WHERE role = 'mahasiswa' ORDER BY id ASC");
$dataMahasiswa = [];
while ($row = mysqli_fetch_assoc($q4)) {
    $dataMahasiswa[] = [
        "id"     => "#USR-" . $row['id'],
        "id_raw" => (int)$row['id'],
        "nama"   => $row['nama'],
        "email"  => $row['email'],
        "saldo"  => (int)$row['saldo'],
    ];
}

// 5. Data tenant/penjual (id, nama toko, email)
$q5 = mysqli_query($koneksi, "SELECT id, nama, email FROM users WHERE role = 'penjual' ORDER BY id ASC");
$dataTenant = [];
while ($row = mysqli_fetch_assoc($q5)) {
    $dataTenant[] = [
        "id"        => "#USR-" . $row['id'],
        "id_raw"    => (int)$row['id'],
        "nama_toko" => $row['nama'],
        "email"     => $row['email'],
    ];
}

echo json_encode([
    "status" => "success",
    "total_mahasiswa"    => $totalMahasiswa,
    "total_tenant"        => $totalTenant,
    "total_perputaran"    => $totalPerputaran,
    "data_mahasiswa"      => $dataMahasiswa,
    "data_tenant"         => $dataTenant,
]);
?>