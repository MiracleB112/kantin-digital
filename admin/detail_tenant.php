<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$id_penjual = $_GET['id'];

$qOmzet = mysqli_query($koneksi, "
SELECT
COUNT(id) as total_order,
SUM(total_harga) as omzet
FROM orders
WHERE id_penjual='$id_penjual'
AND status='selesai'
");

$dataOmzet = mysqli_fetch_assoc($qOmzet);

$totalOmzet = $dataOmzet['omzet'] ?? 0;
$totalOrder = $dataOmzet['total_order'] ?? 0;

$komisiKasir = $totalOmzet * 0.05;
$pendapatanBersihTenant = $totalOmzet - $komisiKasir;

$filter = isset($_GET['filter'])
    ? $_GET['filter']
    : 'bulan';

$whereFilter = "";

if ($filter == "hari") {

    $whereFilter .= "
    AND DATE(tanggal_pesan)=CURDATE()
    ";

} elseif ($filter == "minggu") {

    $whereFilter .= "
    AND tanggal_pesan >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ";

} else {

    $whereFilter .= "
    AND tanggal_pesan >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ";

}



$hari_ini = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT SUM(total_harga) total
FROM orders
WHERE id_penjual='$id_penjual'
AND DATE(tanggal_pesan)=CURDATE()
"));

$minggu_ini = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT SUM(total_harga) total
FROM orders
WHERE id_penjual='$id_penjual'
AND YEARWEEK(tanggal_pesan,1)=YEARWEEK(CURDATE(),1)
"));

$bulan_ini = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT SUM(total_harga) total
FROM orders
WHERE id_penjual='$id_penjual'
AND MONTH(tanggal_pesan)=MONTH(CURDATE())
AND YEAR(tanggal_pesan)=YEAR(CURDATE())
"));



$tenant = mysqli_fetch_assoc(
    mysqli_query($koneksi, "
SELECT *
FROM users
WHERE id='$id_penjual'
")
);

$queryOrder = mysqli_query($koneksi, "
SELECT
orders.*,
users.nama AS nama_mahasiswa
FROM orders
JOIN users
ON orders.id_mahasiswa = users.id
WHERE orders.id_penjual='$id_penjual'
$whereFilter
ORDER BY orders.tanggal_pesan DESC
");

$hari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

$bulan = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Tenant</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <div class="header">
        <div class="user-info">
            <h3>Administrator</h3>
            <span>Detail Tenant</span>
        </div>

        <a href="laporan_tenant.php" class="btn-logout">
            ⬅ Kembali
        </a>
    </div>

    <div class="container">

        <div class="panel-card">

            <h2>
                🏪 <?= htmlspecialchars($tenant['nama']); ?>
            </h2>

            <p>
                Email :
                <?= htmlspecialchars($tenant['email']); ?>
            </p>

            <p>
                Sistem Komisi Kasir :
            <b>5%</b>
            </p>

        </div>

        <div class="panel-card">
            <div class="stats-row">


                <div class="stat-card">
                    <span>💰 Pendapatan Hari Ini</span>
                    <h2>
                        Rp <?= number_format($hari_ini['total'] ?? 0, 0, ',', '.'); ?>
                    </h2>
                </div>

                <div class="stat-card">
                    <span>📈 Pendapatan Minggu Ini</span>
                    <h2>
                        Rp <?= number_format($minggu_ini['total'] ?? 0, 0, ',', '.'); ?>
                    </h2>
                </div>

                <div class="stat-card">
                    <span>🏆 Pendapatan Bulan Ini</span>
                    <h2>
                        Rp <?= number_format($bulan_ini['total'] ?? 0, 0, ',', '.'); ?>
                    </h2>
                </div>

                <div class="stat-card">
                    <span>💰 Total Omzet</span>
                    <h2>
                        Rp <?= number_format($totalOmzet, 0, ',', '.'); ?>
                    </h2>
                </div>

                <div class="stat-card">
                    <span>💼 Komisi Kasir</span>

                    <h2>
                        Rp <?= number_format($komisiKasir, 0, ',', '.'); ?>
                    </h2>
                </div>

                <div class="stat-card">
                    <span>🏦 Pendapatan Bersih Tenant</span>
                    <h2>
                        Rp <?= number_format($pendapatanBersihTenant, 0, ',', '.'); ?>
                    </h2>
                </div>

                <div class="stat-card">
                    <span>📦 Total Pesanan</span>
                    <h2>
                        <?= $totalOrder; ?>
                    </h2>
                </div>

                <div class="panel-card">

<h3>📊 Ringkasan Keuangan Tenant</h3>

<p>
Omzet Kotor :
<b>
Rp <?= number_format($totalOmzet,0,',','.'); ?>
</b>
</p>

<p>
Komisi Kasir (5%) :
<b>
Rp <?= number_format($komisiKasir,0,',','.'); ?>
</b>
</p>

<p>
Pendapatan Bersih Tenant :
<b style="color:green;">
Rp <?= number_format($totalOmzet - $komisiKasir,0,',','.'); ?>
</b>
</p>

</div>

            </div> <!-- tutup stats-row -->

            <div class="filter-tabs"></div>

            <div class="filter-tabs">

                <a href="?id=<?= $id_penjual ?>&filter=hari" class="<?= $filter == 'hari' ? 'active-filter' : '' ?>">
                    📅 Hari
                </a>

                <a href="?id=<?= $id_penjual ?>&filter=minggu"
                    class="<?= $filter == 'minggu' ? 'active-filter' : '' ?>">
                    📈 Minggu
                </a>

                <a href="?id=<?= $id_penjual ?>&filter=bulan" class="<?= $filter == 'bulan' ? 'active-filter' : '' ?>">
                    🏆 Bulan
                </a>

            </div>

            <h2>
                📋 Seluruh Transaksi Tenant
            </h2>
        </div>


        <?php while ($row = mysqli_fetch_assoc($queryOrder)): ?>

            <?php

            $tanggal = strtotime($row['tanggal_pesan']);

            $tglOrder = date('Y-m-d', $tanggal);
            $weekOrder = date('W', $tanggal);
            $monthOrder = date('m', $tanggal);


            $detailMenu = mysqli_query($koneksi, "
SELECT
order_details.*,
menus.nama_menu
FROM order_details
JOIN menus
ON order_details.id_menu = menus.id
WHERE order_details.id_order='" . $row['id'] . "'
");

            ?>

            <?php
            $tanggal = strtotime($row['tanggal_pesan']);
            ?>

            <div class="transaction-card">

                <div class="transaction-top">

                    <div>

                        <h3 class="customer-name">
                            👤 <?= htmlspecialchars($row['nama_mahasiswa']); ?>
                        </h3>

                        <div class="date-info">
                            📅 <?= $hari[date('l', $tanggal)]; ?>,
                            <?= date('d', $tanggal); ?>
                            <?= $bulan[(int) date('n', $tanggal)]; ?>
                            <?= date('Y', $tanggal); ?>
                        </div>

                        <div class="time-info">
                            🕒 <?= date('H:i', $tanggal); ?> WIB
                        </div>

                    </div>

                    <span class="status-pill">
                        <?= ucfirst($row['status']); ?>
                    </span>

                </div>

                <div class="menu-box">

                    <?php while ($menu = mysqli_fetch_assoc($detailMenu)): ?>

                        <div class="menu-row">

                            <span>
                                🍽️ <?= htmlspecialchars($menu['nama_menu']); ?>
                            </span>

                            <strong>
                                x<?= $menu['jumlah']; ?>
                            </strong>

                        </div>

                    <?php endwhile; ?>

                </div>

                <div class="transaction-bottom">

                    <span>
                        Total Pembayaran
                    </span>

                    <h2>
                        Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                    </h2>

                </div>

            </div>

        <?php endwhile; ?>






    </div>

</body>

</html>