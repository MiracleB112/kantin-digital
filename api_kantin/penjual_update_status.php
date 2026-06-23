<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Path disesuaikan: file ini ada di .../Kantin-Digital/api_kantin/
// koneksi.php ada di .../Kantin-Digital/config/koneksi.php
require_once '../config/koneksi.php';

$body = json_decode(file_get_contents("php://input"), true);
$idOrder = isset($body['id_order']) ? intval($body['id_order']) : 0;
$statusBaru = isset($body['status']) ? $body['status'] : null;

if ($idOrder <= 0) {
    echo json_encode(["status" => "error", "message" => "id_order tidak valid"]);
    exit;
}

// Kalau Flutter tidak mengirim status tujuan, tentukan otomatis dari status saat ini di database
if ($statusBaru === null) {
    $cek = mysqli_query($koneksi, "SELECT status FROM orders WHERE id = " . $idOrder);
    $row = mysqli_fetch_assoc($cek);

    if (!$row) {
        echo json_encode(["status" => "error", "message" => "Order tidak ditemukan"]);
        exit;
    }

    $statusSekarang = $row['status'];
    if ($statusSekarang === 'pending') {
        $statusBaru = 'diproses';
    } elseif ($statusSekarang === 'diproses') {
        $statusBaru = 'siap diambil';
    } elseif ($statusSekarang === 'siap diambil') {
        $statusBaru = 'selesai';
    } else {
        echo json_encode(["status" => "error", "message" => "Pesanan sudah selesai"]);
        exit;
    }
}

$allowed = ['pending', 'diproses', 'siap diambil', 'selesai'];
if (!in_array($statusBaru, $allowed)) {
    echo json_encode(["status" => "error", "message" => "Status tidak valid: $statusBaru"]);
    exit;
}

$stmt = mysqli_prepare($koneksi, "UPDATE orders SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $statusBaru, $idOrder);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($koneksi);

echo json_encode([
    "status"  => $ok ? "success" : "error",
    "message" => $ok ? "Status berhasil diperbarui ke $statusBaru" : "Gagal update status di database",
]);