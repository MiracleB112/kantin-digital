<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include '../config/koneksi.php';

define('KOMISI_PERSEN', 5); // Komisi kasir/admin 5% dari setiap transaksi selesai

// ===== 1. Pesanan & Omzet Hari Ini =====
$q1 = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah, COALESCE(SUM(total_harga),0) AS total 
                               FROM orders 
                               WHERE status = 'selesai' AND DATE(tanggal_pesan) = CURDATE()");
$rowHari = mysqli_fetch_assoc($q1);

// ===== 2. Pesanan & Omzet Minggu Ini =====
$q2 = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah, COALESCE(SUM(total_harga),0) AS total 
                               FROM orders 
                               WHERE status = 'selesai' AND YEARWEEK(tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)");
$rowMinggu = mysqli_fetch_assoc($q2);

// ===== 3. Pesanan & Omzet Bulan Ini =====
$q3 = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah, COALESCE(SUM(total_harga),0) AS total 
                               FROM orders 
                               WHERE status = 'selesai' 
                               AND MONTH(tanggal_pesan) = MONTH(CURDATE()) 
                               AND YEAR(tanggal_pesan) = YEAR(CURDATE())");
$rowBulan = mysqli_fetch_assoc($q3);

// ===== 4. Pendapatan Kasir (komisi 5%) - Hari / Minggu / Bulan =====
$komisiHariIni   = round(((int)$rowHari['total'])   * KOMISI_PERSEN / 100);
$komisiMingguIni = round(((int)$rowMinggu['total']) * KOMISI_PERSEN / 100);
$komisiBulanIni  = round(((int)$rowBulan['total'])  * KOMISI_PERSEN / 100);

// ===== 5. Monitoring per Tenant (semua user role penjual) =====
$qTenant = mysqli_query($koneksi, "SELECT id, nama FROM users WHERE role = 'penjual' ORDER BY id ASC");
$monitoringTenant = [];

while ($tenant = mysqli_fetch_assoc($qTenant)) {
    $idPenjual = (int)$tenant['id'];

    $qStat = mysqli_query($koneksi, "SELECT COUNT(*) AS total_pesanan, COALESCE(SUM(total_harga),0) AS total_omzet 
                                      FROM orders 
                                      WHERE id_penjual = $idPenjual AND status = 'selesai'");
    $stat = mysqli_fetch_assoc($qStat);

    $totalOmzet = (int)$stat['total_omzet'];
    $komisiTenant = round($totalOmzet * KOMISI_PERSEN / 100);

    $monitoringTenant[] = [
        "id_penjual"     => $idPenjual,
        "nama_toko"      => $tenant['nama'],
        "total_pesanan"  => (int)$stat['total_pesanan'],
        "total_omzet"    => $totalOmzet,
        "komisi_kasir"   => $komisiTenant,
    ];
}

echo json_encode([
    "status" => "success",
    "hari_ini" => [
        "jumlah_pesanan" => (int)$rowHari['jumlah'],
        "total_omzet"    => (int)$rowHari['total'],
        "komisi_kasir"   => (int)$komisiHariIni,
    ],
    "minggu_ini" => [
        "jumlah_pesanan" => (int)$rowMinggu['jumlah'],
        "total_omzet"    => (int)$rowMinggu['total'],
        "komisi_kasir"   => (int)$komisiMingguIni,
    ],
    "bulan_ini" => [
        "jumlah_pesanan" => (int)$rowBulan['jumlah'],
        "total_omzet"    => (int)$rowBulan['total'],
        "komisi_kasir"   => (int)$komisiBulanIni,
    ],
    "komisi_persen" => KOMISI_PERSEN,
    "monitoring_tenant" => $monitoringTenant,
]);
?>