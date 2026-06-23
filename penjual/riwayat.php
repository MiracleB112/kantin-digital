<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit();
}

$id_penjual = $_SESSION['id_user'];

$filter = $_GET['filter'] ?? 'bulan';

$whereFilter = "";

if($filter == "hari"){

    $whereFilter = "
    AND DATE(tanggal_pesan)=CURDATE()
    ";

}elseif($filter == "minggu"){

    $whereFilter = "
    AND tanggal_pesan >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ";

}else{

    $whereFilter = "
    AND tanggal_pesan >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ";
}


$sql = "
SELECT orders.*, users.nama as nama_mahasiswa
FROM orders
JOIN users ON orders.id_mahasiswa = users.id
WHERE orders.id_penjual = '$id_penjual'
AND orders.status = 'selesai'
$whereFilter
ORDER BY orders.tanggal_pesan DESC
";
$query = mysqli_query($koneksi, $sql);

$hari = [
    'Sunday'=>'Minggu',
    'Monday'=>'Senin',
    'Tuesday'=>'Selasa',
    'Wednesday'=>'Rabu',
    'Thursday'=>'Kamis',
    'Friday'=>'Jumat',
    'Saturday'=>'Sabtu'
];

$bulan = [
    1=>'Januari','Februari','Maret','April','Mei','Juni',
    'Juli','Agustus','September','Oktober','November','Desember'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="../assets/css/penjual.css">
</head>
<body>

<div class="header">
    <div class="user-info">
        <h3>Toko: <?= htmlspecialchars($_SESSION['nama']); ?></h3>
        <span>Riwayat Pesanan Selesai</span>
    </div>

    <a href="index.php" class="btn-logout" style="text-decoration:none;">
        ⬅ Kembali
    </a>
</div>

<div class="container">

<h2 class="panel-title-bold">
📜 Riwayat Pesanan
</h2>

<div class="filter-container">

    <a href="?filter=hari"
       class="filter-btn <?= $filter=='hari'?'active':'' ?>">
       📅 Hari
    </a>

    <a href="?filter=minggu"
       class="filter-btn <?= $filter=='minggu'?'active':'' ?>">
       📈 Minggu
    </a>

    <a href="?filter=bulan"
       class="filter-btn <?= $filter=='bulan'?'active':'' ?>">
       🏆 Bulan
    </a>

</div>

<div class="orders-card-grid">

<?php
if(mysqli_num_rows($query) > 0):

while($row = mysqli_fetch_assoc($query)):

$tanggal = strtotime($row['tanggal_pesan']);
?>

<div class="order-main-card border-selesai">

    <div class="order-card-header">

        <span class="order-id-badge">
            #ORD-<?= $row['id']; ?>
        </span>

        <span class="badge-status state-selesai">
            Selesai
        </span>

    </div>

    <div class="order-card-body">

        <h4 class="customer-name">
            👤 <?= htmlspecialchars($row['nama_mahasiswa']); ?>
        </h4>

        <div style="margin-top:10px;">

            <?php

            $detail = mysqli_query($koneksi,"
            SELECT od.*, menus.nama_menu
            FROM order_details od
            JOIN menus ON od.id_menu = menus.id
            WHERE od.id_order='".$row['id']."'
            ");

            while($d = mysqli_fetch_assoc($detail)):
            ?>

                <div class="item-product-row">
                    <span>
                        🍽️ <?= $d['nama_menu']; ?>
                    </span>

                    <span>
                        x<?= $d['jumlah']; ?>
                    </span>
                </div>

            <?php endwhile; ?>

        </div>

        <hr style="margin:15px 0;">

        <div style="font-size:14px;color:#666;">
            📅 <?= $hari[date('l',$tanggal)]; ?>,
            <?= date('d',$tanggal); ?>
            <?= $bulan[date('n',$tanggal)]; ?>
            <?= date('Y',$tanggal); ?>
        </div>

        <div style="font-size:14px;color:#666;margin-top:5px;">
            🕒 <?= date('H:i',$tanggal); ?> WIB
        </div>

    </div>

    <div class="order-card-footer">

        <div class="total-price-block">

            <span class="total-label">
                Total Bayar
            </span>

            <span class="total-amount">
                Rp <?= number_format($row['total_harga'],0,',','.'); ?>
            </span>

        </div>

        <span class="text-completed">
            ✔️ Transaksi Selesai
        </span>

    </div>

</div>

<?php
endwhile;

else:
?>

<div style="
grid-column:1/-1;
background:#fff;
padding:40px;
text-align:center;
border-radius:15px;
">

📭 Belum ada riwayat pesanan.

</div>

<?php endif; ?>

</div>

</div>

</body>
</html>