<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include '../config/koneksi.php';

define('KOMISI_PERSEN', 5); // Komisi kasir/admin 5% dari setiap transaksi selesai

$idPenjual = isset($_GET['idPenjual']) ? (int)$_GET['idPenjual'] : 0;
$filter    = isset($_GET['filter']) ? strtolower($_GET['filter']) : 'bulan'; // hari | minggu | bulan

if ($idPenjual == 0) {
    echo json_encode(["status" => "error", "message" => "ID Penjual tidak ditemukan!"]);
    exit;
}

if (!in_array($filter, ['hari', 'minggu', 'bulan'])) {
    $filter = 'bulan';
}

// Ambil data dasar tenant
$qTenant = mysqli_query($koneksi, "SELECT id, nama, email FROM users WHERE id = $idPenjual AND role = 'penjual'");
$tenant = mysqli_fetch_assoc($qTenant);

if (!$tenant) {
    echo json_encode(["status" => "error", "message" => "Tenant tidak ditemukan!"]);
    exit;
}

// ===== Klausa WHERE periode, sesuai filter yang dipilih =====
switch ($filter) {
    case 'hari':
        $klausaPeriode = "DATE(tanggal_pesan) = CURDATE()";
        break;
    case 'minggu':
        $klausaPeriode = "YEARWEEK(tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    default: // bulan
        $klausaPeriode = "MONTH(tanggal_pesan) = MONTH(CURDATE()) AND YEAR(tanggal_pesan) = YEAR(CURDATE())";
        break;
}

// ===== 1. Statistik mengikuti filter (Total Omzet, Komisi Kasir, Pendapatan Bersih, Total Pesanan) =====
$qStat = mysqli_query($koneksi, "SELECT COUNT(*) AS total_pesanan, COALESCE(SUM(total_harga),0) AS total_omzet 
                                  FROM orders 
                                  WHERE id_penjual = $idPenjual AND status = 'selesai' AND $klausaPeriode");
$stat = mysqli_fetch_assoc($qStat);

$totalOmzetFilter   = (int)$stat['total_omzet'];
$komisiKasirFilter  = (int)round($totalOmzetFilter * KOMISI_PERSEN / 100);
$pendapatanBersih   = $totalOmzetFilter - $komisiKasirFilter;
$totalPesananFilter = (int)$stat['total_pesanan'];

// ===== 2. Pendapatan Hari Ini / Minggu Ini / Bulan Ini (selalu statis, tidak ikut filter) =====
$qHari = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) AS total FROM orders 
                                  WHERE id_penjual = $idPenjual AND status = 'selesai' 
                                  AND DATE(tanggal_pesan) = CURDATE()");
$pendapatanHari = (int)mysqli_fetch_assoc($qHari)['total'];

$qMinggu = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) AS total FROM orders 
                                    WHERE id_penjual = $idPenjual AND status = 'selesai' 
                                    AND YEARWEEK(tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)");
$pendapatanMinggu = (int)mysqli_fetch_assoc($qMinggu)['total'];

$qBulan = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) AS total FROM orders 
                                   WHERE id_penjual = $idPenjual AND status = 'selesai' 
                                   AND MONTH(tanggal_pesan) = MONTH(CURDATE()) AND YEAR(tanggal_pesan) = YEAR(CURDATE())");
$pendapatanBulan = (int)mysqli_fetch_assoc($qBulan)['total'];

// ===== 3. Seluruh Transaksi Tenant (ikut filter periode) =====
$qTransaksi = mysqli_query($koneksi, "SELECT 
            o.id,
            o.total_harga AS total,
            o.status,
            o.tanggal_pesan,
            o.jam_ambil,
            u.nama AS pemesan,
            GROUP_CONCAT(CONCAT(m.nama_menu, '||', od.jumlah) SEPARATOR ';;') AS detail_item
          FROM orders o
          JOIN users u ON u.id = o.id_mahasiswa
          JOIN order_details od ON od.id_order = o.id
          JOIN menus m ON m.id = od.id_menu
          WHERE o.id_penjual = $idPenjual AND $klausaPeriode
          GROUP BY o.id
          ORDER BY o.id DESC");

$transaksi = [];
while ($row = mysqli_fetch_assoc($qTransaksi)) {
    $items = [];
    foreach (explode(';;', $row['detail_item']) as $itemStr) {
        $parts = explode('||', $itemStr);
        if (count($parts) == 2) {
            $items[] = ["nama_menu" => $parts[0], "qty" => (int)$parts[1]];
        }
    }

    $transaksi[] = [
        "id_order"     => (int)$row['id'],
        "no_order"     => "#ORD-" . $row['id'],
        "pemesan"      => $row['pemesan'],
        "tanggal"      => $row['tanggal_pesan'],
        "jam_ambil"    => $row['jam_ambil'],
        "status"       => $row['status'],
        "total"        => (int)$row['total'],
        "items"        => $items,
    ];
}

echo json_encode([
    "status" => "success",
    "tenant" => [
        "id"            => (int)$tenant['id'],
        "nama_toko"     => $tenant['nama'],
        "email"         => $tenant['email'],
        "komisi_persen" => KOMISI_PERSEN,
    ],
    "pendapatan_statis" => [
        "hari_ini"   => $pendapatanHari,
        "minggu_ini" => $pendapatanMinggu,
        "bulan_ini"  => $pendapatanBulan,
    ],
    "filter_aktif" => $filter,
    "ringkasan_filter" => [
        "total_omzet"         => $totalOmzetFilter,
        "komisi_kasir"        => $komisiKasirFilter,
        "pendapatan_bersih"   => $pendapatanBersih,
        "total_pesanan"       => $totalPesananFilter,
    ],
    "transaksi" => $transaksi,
]);
?>