<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
$queryTenant = mysqli_query($koneksi, "
SELECT
users.id,
users.nama,
COUNT(orders.id) as total_order,
SUM(orders.total_harga) as omzet
FROM users
LEFT JOIN orders
ON users.id = orders.id_penjual
AND orders.status='selesai'
WHERE users.role='penjual'
GROUP BY users.id
ORDER BY omzet DESC
");

// HARI INI
$q_hari = mysqli_query($koneksi,"
SELECT
COUNT(*) as total_pesanan,
SUM(total_harga) as omzet
FROM orders
WHERE status='selesai'
AND DATE(tanggal_pesan)=CURDATE()
");

$hari = mysqli_fetch_assoc($q_hari);


// MINGGU INI
$q_minggu = mysqli_query($koneksi,"
SELECT
COUNT(*) as total_pesanan,
SUM(total_harga) as omzet
FROM orders
WHERE status='selesai'
AND YEARWEEK(tanggal_pesan,1)=YEARWEEK(CURDATE(),1)
");

$minggu = mysqli_fetch_assoc($q_minggu);


// BULAN INI
$q_bulan = mysqli_query($koneksi,"
SELECT
COUNT(*) as total_pesanan,
SUM(total_harga) as omzet
FROM orders
WHERE status='selesai'
AND MONTH(tanggal_pesan)=MONTH(CURDATE())
AND YEAR(tanggal_pesan)=YEAR(CURDATE())
");

$bulan = mysqli_fetch_assoc($q_bulan);
$komisiKasir = 5; // persen

$pendapatanKasirHari =
(($hari['omzet'] ?? 0) * $komisiKasir) / 100;

$pendapatanKasirMinggu =
(($minggu['omzet'] ?? 0) * $komisiKasir) / 100;

$pendapatanKasirBulan =
(($bulan['omzet'] ?? 0) * $komisiKasir) / 100;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Tenant</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="header">

        <div class="user-info">
            <h3>Administrator</h3>
            <span>Sistem Pusat Kantin</span>
        </div>

        <a href="../auth/logout.php" class="btn-logout">
            ➡️ Keluar
        </a>

    </div>

    <div class="sub-nav">

        <a href="index.php" class="btn-nav inactive">
            📊 Ringkasan Sistem
        </a>

        <a href="topup.php" class="btn-nav inactive">
            💳 Top Up Saldo
        </a>

        <a href="laporan_tenant.php" class="btn-nav active">
            📈 Monitoring Tenant
        </a>

    </div>

    <div class="container">
        <div class="stats-grid">

    <div class="card-stat">
        <span>📅 Hari Ini</span>

        <h3>
            <?= $hari['total_pesanan']; ?> Pesanan
        </h3>

        <h2>
            Rp <?= number_format($hari['omzet'] ?: 0,0,',','.'); ?>
        </h2>
    </div>

    <div class="card-stat">
        <span>📆 Minggu Ini</span>

        <h3>
            <?= $minggu['total_pesanan']; ?> Pesanan
        </h3>

        <h2>
            Rp <?= number_format($minggu['omzet'] ?: 0,0,',','.'); ?>
        </h2>
    </div>

    <div class="card-stat">
        <span>🗓️ Bulan Ini</span>

        <h3>
            <?= $bulan['total_pesanan']; ?> Pesanan
        </h3>

        <h2>
            Rp <?= number_format($bulan['omzet'] ?: 0,0,',','.'); ?>
        </h2>
    </div>

   <div class="card-stat">

    <span>💼 Pendapatan Kasir</span>

    <h3>
        Komisi <?= $komisiKasir; ?>%
    </h3>

    <h2>
        Rp <?= number_format($pendapatanKasirBulan,0,',','.'); ?>
    </h2>

</div>

</div>

<div class="panel-card">

    <h2>💼 Ringkasan Pendapatan Kasir</h2>

    <div class="kasir-grid">

        <div class="kasir-item">
            <span>Hari Ini</span>
            <h3>
                Rp <?= number_format($pendapatanKasirHari,0,',','.'); ?>
            </h3>
        </div>

        <div class="kasir-item">
            <span>Minggu Ini</span>
            <h3>
                Rp <?= number_format($pendapatanKasirMinggu,0,',','.'); ?>
            </h3>
        </div>

        <div class="kasir-item">
            <span>Bulan Ini</span>
            <h3>
                Rp <?= number_format($pendapatanKasirBulan,0,',','.'); ?>
            </h3>
        </div>

    </div>

</div>
        <h2 style="margin-bottom:20px;">
            📈 Monitoring Tenant
        </h2>

        <?php
        while ($tenant = mysqli_fetch_assoc($queryTenant)):
            $komisiTenant = (($tenant['omzet'] ?? 0) * 5) / 100;
            ?>

            <div class="panel-card">

                <h3>
                    🏪 <?= htmlspecialchars($tenant['nama']); ?>
                </h3>

                <p>
                    Total Pesanan :
                    <b><?= $tenant['total_order']; ?></b>
                </p>

                <p>
                    Total Omzet :
                    <b>
                        Rp <?= number_format($tenant['omzet'], 0, ',', '.'); ?>
                    </b>
                </p>

                <p>
    Komisi Kasir :
    <b style="color:#16a34a;">
        Rp <?= number_format($komisiTenant,0,',','.'); ?>
    </b>
</p>

                <a
href="detail_tenant.php?id=<?= $tenant['id']; ?>"
class="btn-detail"
>
Lihat Detail
</a>

            </div>

            

        <?php endwhile; ?>

    </div>


</body>

</html>