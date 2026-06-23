<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../config/koneksi.php'; 

// Query menu + JOIN tenant + hitung rating asli dari ratings + total pembelian asli dari order_details
$sql = "SELECT 
            m.id, 
            u.nama AS tenant, 
            m.nama_menu, 
            m.deskripsi, 
            m.harga, 
            m.foto, 
            m.status,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.id) AS jumlah_ulasan,
            COALESCE((SELECT SUM(od.jumlah) FROM order_details od WHERE od.id_menu = m.id), 0) AS total_pembelian
        FROM menus m 
        JOIN users u ON m.id_penjual = u.id
        LEFT JOIN ratings r ON r.id_menu = m.id
        GROUP BY m.id";

$query = mysqli_query($koneksi, $sql);

if ($query) {
    $list_menu = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $jumlahUlasan = (int)$row['jumlah_ulasan'];
        $avgRating = $jumlahUlasan > 0 ? round((float)$row['avg_rating'], 1) : 0;

        $list_menu[] = [
            "id" => (int)$row['id'],
            "tenant" => $row['tenant'],
            "name" => $row['nama_menu'],
            "desc" => $row['deskripsi'],
            "price" => (int)$row['harga'],
            "img" => $row['foto'] ?? '',
            "status" => $row['status'],
            "rating" => $avgRating,                 // 0 kalau belum ada ulasan sama sekali
            "ulasan" => $jumlahUlasan,                // jumlah ulasan asli
            "pembelian" => (int)$row['total_pembelian'], // total qty terjual asli
            "isBest" => ($jumlahUlasan > 0 && $avgRating >= 4) // Best Seller HANYA jika sudah ada ulasan & rating >= 4
        ];
    }
    
    echo json_encode([
        "status" => "success",
        "data" => $list_menu
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data menu dari database: " . mysqli_error($koneksi)
    ]);
}
?>