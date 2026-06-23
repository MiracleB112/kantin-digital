<?php
session_start();
include '../config/koneksi.php';

// Proteksi Halaman: Pastikan user sudah login & merupakan mahasiswa
if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../index.php");
    exit();
}

$id_mahasiswa = $_SESSION['id_user'];

// 1. Ambil data saldo terbaru mahasiswa dari database
$query_user = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id = '$id_mahasiswa'");
$data_user = mysqli_fetch_assoc($query_user);
$saldo = $data_user['saldo'];

// 2. Ambil jumlah pesanan mahasiswa yang berstatus 'siap diambil'
$query_status = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM orders WHERE id_mahasiswa = '$id_mahasiswa' AND status = 'siap diambil'");
$data_status = mysqli_fetch_assoc($query_status);
$pesanan_siap = $data_status['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Kantin Digital</title>
    <link rel="stylesheet" href="../assets/css/mahasiswa.css">
</head>
<body>

<div class="header">
    <div class="user-info">
        <h3>Halo, <?= htmlspecialchars($_SESSION['nama']); ?>!</h3>
        <span>Mahasiswa</span>
    </div>
    <a href="../auth/logout.php" class="btn-logout" style="text-decoration: none;">➡️ Keluar</a>
</div>

<div class="container" style="display: block;"> <div class="info-cards" style="max-width: 100%; margin-bottom: 25px;">
        <div class="card-balance">
            <span>Saldo E-Wallet</span>
            <h2>Rp <?= number_format($saldo, 0, ',', '.'); ?></h2>
        </div>
        <div class="card-status">
            <span>Pesanan Siap Diambil</span>
            <h2><?= $pesanan_siap; ?> Pesanan</h2>
        </div>
    </div>

    <div class="sub-nav">
        <a href="index.php" class="btn-nav inactive">🍴 Pesan Makanan</a>
        <a href="riwayat.php" class="btn-nav active">⏳ Riwayat Pesanan</a>
    </div>

    <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses_order'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: bold;">
            🎉 Pemesanan Berhasil! Saldo E-Wallet Anda telah dipotong. Silakan tunggu penjual memproses makanan Anda.
        </div>
    <?php endif; ?>

    <div class="history-card">
        <h3 style="margin-top: 0; color: #333;">Daftar Transaksi Anda</h3>
        
        <table class="history-table">
            <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Tanggal & Waktu</th>
                    <th>Penjual / Tenant</th>
                    <th>Item Makanan</th>
                    <th>Total Bayar</th>
                    <th>Status Pesanan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query mengambil data orders milik mahasiswa saat ini di-join dengan nama toko penjual
                $sql_orders = "SELECT orders.*, users.nama as nama_toko 
                               FROM orders 
                               JOIN users ON orders.id_penjual = users.id 
                               WHERE orders.id_mahasiswa = '$id_mahasiswa' 
                               ORDER BY orders.id DESC";
                
                $query_orders = mysqli_query($koneksi, $sql_orders);
                
                if (mysqli_num_rows($query_orders) > 0):
                    while($order = mysqli_fetch_assoc($query_orders)):
                        $id_order = $order['id'];
                ?>
                    <tr>
                        <td><strong>#ORD-<?= $id_order; ?></strong></td>
                        <td><?= date('d M Y, H:i', strtotime($order['tanggal_pesan'])); ?> WIB</td>
                        <td><span style="color: #f24e1e; font-weight: bold;"><?= htmlspecialchars($order['nama_toko']); ?></span></td>
                        <td>
                            <ul class="order-items-list">
                                <?php
                                // Query mengambil detail makanan apa saja yang dibeli di dalam ID order ini
                                $sql_details = "SELECT order_details.*, menus.nama_menu 
                                                FROM order_details 
                                                JOIN menus ON order_details.id_menu = menus.id 
                                                WHERE order_details.id_order = '$id_order'";
                                $query_details = mysqli_query($koneksi, $sql_details);
                                while($detail = mysqli_fetch_assoc($query_details)):
                                ?>
                                    <li><?= htmlspecialchars($detail['nama_menu']); ?> <strong>(x<?= $detail['jumlah']; ?>)</strong></li>
                                <?php endwhile; ?>
                            </ul>
                        </td>
                        <td><strong>Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></strong></td>
                        <td>
                           <?php
$status = $order['status'];

$badge_class = 'badge-pending';
if ($status == 'diproses') {
    $badge_class = 'badge-diproses';
} elseif ($status == 'siap diambil') {
    $badge_class = 'badge-siap';
} elseif ($status == 'selesai') {
    $badge_class = 'badge-selesai';
}
?>
<?php
$cek_rating = mysqli_query($koneksi,"
SELECT *
FROM ratings
WHERE id_order='$id_order'
LIMIT 1
");

if($status=='selesai' && mysqli_num_rows($cek_rating)==0):
?>

<a href="rating.php?id_order=<?= $id_order; ?>"
class="btn-rating">
⭐ Beri Rating
</a>

<?php endif; ?>

<span class="badge <?= $badge_class; ?>">
    <?= $status; ?>
</span>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                    echo "<tr><td colspan='6' style='text-align:center; color:#6c757d; padding: 30px;'>Anda belum pernah melakukan pemesanan makanan.</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>