<?php
session_start();
include '../config/koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 1. Hitung Total Mahasiswa
$q_mhs = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'");
$d_mhs = mysqli_fetch_assoc($q_mhs);

// 2. Hitung Total Penjual/Tenant
$q_tnt = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'penjual'");
$d_tnt = mysqli_fetch_assoc($q_tnt);

// 3. Hitung Total Seluruh Transaksi Selesai di Kantin
$q_trx = mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM orders WHERE status = 'selesai'");
$d_trx = mysqli_fetch_assoc($q_trx);
$total_omzet_kantin = $d_trx['total'] ? $d_trx['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kantin Digital</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="header">
    <div class="user-info">
        <h3>Administrator</h3>
        <span>Sistem Pusat Kantin</span>
    </div>
    <a href="../auth/logout.php" class="btn-logout">➡️ Keluar</a>
</div>

<div class="container">
    <div class="sub-nav">
        <a href="index.php" class="btn-nav active">📊 Ringkasan Sistem</a>
        <a href="topup.php" class="btn-nav inactive">💳 Top Up Saldo</a>
        <a href="laporan_tenant.php" class="btn-nav inactive">📈 Monitoring Tenant</a>
    </div>

    <div class="stats-grid">
        <div class="card-stat">
            <span>Total Mahasiswa Terdaftar</span>
            <h2><?= $d_mhs['total']; ?> Orang</h2>
        </div>
        <div class="card-stat">
            <span>Total Tenant Aktif</span>
            <h2><?= $d_tnt['total']; ?> Stand</h2>
        </div>
        <div class="card-stat">
            <span>Total Perputaran Uang</span>
            <h2>Rp <?= number_format($total_omzet_kantin, 0, ',', '.'); ?></h2>
        </div>
    </div>

    <!-- DATA MAHASISWA -->
<div class="panel-card">

    <h3 class="panel-title">
        🎓 Data Mahasiswa
    </h3>

    <table class="main-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Saldo</th>
            </tr>
        </thead>

        <tbody>

        <?php
        $mahasiswa = mysqli_query($koneksi,"
        SELECT *
        FROM users
        WHERE role='mahasiswa'
        ORDER BY nama ASC
        ");

        while($mhs = mysqli_fetch_assoc($mahasiswa)):
        ?>

        <tr>
            <td>#USR-<?= $mhs['id']; ?></td>
            <td><?= htmlspecialchars($mhs['nama']); ?></td>
            <td><?= htmlspecialchars($mhs['email']); ?></td>
            <td>
                Rp <?= number_format($mhs['saldo'],0,',','.'); ?>
            </td>
        </tr>

        <?php endwhile; ?>

        </tbody>
    </table>

</div>


<!-- DATA TENANT -->
<div class="panel-card">

    <h3 class="panel-title">
        🏪 Data Tenant / Penjual
    </h3>

    <table class="main-table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Toko</th>
                <th>Email</th>
            </tr>
        </thead>

        <tbody>

        <?php
        $tenant = mysqli_query($koneksi,"
        SELECT *
        FROM users
        WHERE role='penjual'
        ORDER BY nama ASC
        ");

        while($t = mysqli_fetch_assoc($tenant)):
        ?>

        <tr>
            <td>#USR-<?= $t['id']; ?></td>
            <td><?= htmlspecialchars($t['nama']); ?></td>
            <td><?= htmlspecialchars($t['email']); ?></td>
        </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>
</div>

</body>
</html>