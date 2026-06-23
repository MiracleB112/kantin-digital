<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../config/koneksi.php';

$idPenjual = isset($_GET['idPenjual']) ? (int)$_GET['idPenjual'] : 0;

if ($idPenjual == 0) {
    echo json_encode(["status" => "error", "message" => "ID Penjual tidak ditemukan!"]);
    exit;
}

// Pendapatan hari ini (hanya status selesai)
$q1 = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) AS total FROM orders 
                               WHERE id_penjual = $idPenjual AND status = 'selesai' 
                               AND DATE(tanggal_pesan) = CURDATE()");
$pendapatanHariIni = mysqli_fetch_assoc($q1)['total'];

// Pendapatan minggu ini
$q2 = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) AS total FROM orders 
                               WHERE id_penjual = $idPenjual AND status = 'selesai' 
                               AND YEARWEEK(tanggal_pesan, 1) = YEARWEEK(CURDATE(), 1)");
$pendapatanMingguIni = mysqli_fetch_assoc($q2)['total'];

// Pesanan baru (status pending/kosong) & sedang diproses (status 'sedang dimasak' ATAU 'siap diambil')
// FIX: nama status disamakan dengan yang dipakai di penjual_update_status.php
$q3 = mysqli_query($koneksi, "SELECT 
                                 SUM(status = 'pending' OR status = '' OR status IS NULL) AS pesanan_baru,
                                 SUM(status = 'sedang dimasak' OR status = 'siap diambil') AS diproses
                               FROM orders WHERE id_penjual = $idPenjual");
$rowStatus = mysqli_fetch_assoc($q3);

// Total pesanan keseluruhan & pesanan hari ini
$q4 = mysqli_query($koneksi, "SELECT 
                                 COUNT(*) AS total_pesanan,
                                 SUM(DATE(tanggal_pesan) = CURDATE()) AS pesanan_hari_ini
                               FROM orders WHERE id_penjual = $idPenjual");
$rowTotal = mysqli_fetch_assoc($q4);

// Best seller (menu paling banyak terjual dari pesanan yang sudah selesai)
// FIX: filter berdasarkan menus.id_penjual (pemilik asli menu), BUKAN orders.id_penjual
// (orders.id_penjual bisa salah kalau keranjang mahasiswa berisi menu dari beberapa tenant sekaligus)
$q5 = mysqli_query($koneksi, "SELECT m.nama_menu AS nama, SUM(od.jumlah) AS qty_terjual, m.harga
                               FROM order_details od
                               JOIN orders o ON o.id = od.id_order
                               JOIN menus m ON m.id = od.id_menu
                               WHERE m.id_penjual = $idPenjual AND o.status = 'selesai'
                               GROUP BY od.id_menu
                               ORDER BY qty_terjual DESC
                               LIMIT 5");
$bestSeller = [];
while ($row = mysqli_fetch_assoc($q5)) {
    $bestSeller[] = $row;
}

echo json_encode([
    "status" => "success",
    "pendapatan_hari_ini" => (int)$pendapatanHariIni,
    "pendapatan_minggu_ini" => (int)$pendapatanMingguIni,
    "pesanan_baru" => (int)($rowStatus['pesanan_baru'] ?? 0),
    "diproses" => (int)($rowStatus['diproses'] ?? 0),
    "total_pesanan" => (int)($rowTotal['total_pesanan'] ?? 0),
    "pesanan_hari_ini" => (int)($rowTotal['pesanan_hari_ini'] ?? 0),
    "best_seller" => $bestSeller
]);
?>