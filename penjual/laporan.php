<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit();
}

$id_penjual = $_SESSION['id_user'];

// 1. Hitung total pendapatan kotor (Bruto) dari pesanan yang sukses/selesai
$q_omzet = mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM orders WHERE id_penjual = '$id_penjual' AND status = 'selesai'");
$d_omzet = mysqli_fetch_assoc($q_omzet);
$total_pendapatan_bruto = $d_omzet['total'] ? $d_omzet['total'] : 0;

// Logika Kalkulasi Potongan Kantin 5% & Netto Kasir
$potongan_kantin = $total_pendapatan_bruto * 0.05;
$netto_kasir = $total_pendapatan_bruto - $potongan_kantin;

// 2. Hitung jumlah porsi produk terjual secara keseluruhan
$q_porsi = mysqli_query($koneksi, "SELECT SUM(jumlah) as total_porsi FROM order_details JOIN orders ON order_details.id_order = orders.id WHERE orders.id_penjual = '$id_penjual' AND orders.status = 'selesai'");
$d_porsi = mysqli_fetch_assoc($q_porsi);
$total_terjual = $d_porsi['total_porsi'] ? $d_porsi['total_porsi'] : 0;

// 3. Hitung jumlah total nota pesanan selesai
$q_orders = mysqli_query($koneksi, "SELECT COUNT(id) as total_nota FROM orders WHERE id_penjual = '$id_penjual' AND status = 'selesai'");
$d_orders = mysqli_fetch_assoc($q_orders);
$total_nota = $d_orders['total_nota'] ? $d_orders['total_nota'] : 0;

// 4. Ambil data Menu Best Seller secara dinamis dari Database milik penjual yang login
$query_best = mysqli_query($koneksi, "
    SELECT menus.nama_menu, menus.harga, SUM(order_details.jumlah) as total_laku 
    FROM order_details 
    JOIN menus ON order_details.id_menu = menus.id 
    JOIN orders ON order_details.id_order = orders.id 
    WHERE orders.id_penjual = '$id_penjual' AND orders.status = 'selesai'
    GROUP BY menus.id 
    ORDER BY total_laku DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Tenant</title>
    <link rel="stylesheet" href="../assets/css/penjual.css">
</head>
<body>

<div class="header">
    <div class="user-info">
        <h3>Toko: <?= htmlspecialchars($_SESSION['nama']); ?></h3>
        <span>Penjual / Tenant Kantin</span>
    </div>
    <a href="../auth/logout.php" class="btn-logout" style="text-decoration: none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
        Keluar
    </a>
</div>

<div class="container">
    
    <div class="sub-nav">
        <a href="index.php" class="btn-nav inactive">📋 Pesanan Masuk</a>
        <a href="menu.php" class="btn-nav inactive">🍔 Kelola Menu</a>
        <a href="laporan.php" class="btn-nav active">📈 Laporan Penjualan</a>
        <a href="riwayat.php" class="btn-nav inactive">📜 Riwayat Pesanan</a>
    </div>

    <div class="top-stats-grid">
        <div class="card-stat-mini">
            <div class="stat-info">
                <span class="stat-label">Pesanan Baru</span>
                <h2 class="stat-number color-orange">0</h2>
            </div>
            <div class="stat-icon icon-orange">🔔</div>
        </div>
        
        <div class="card-stat-mini">
            <div class="stat-info">
                <span class="stat-label">Diproses</span>
                <h2 class="stat-number color-blue">0</h2>
            </div>
            <div class="stat-icon icon-blue">🕒</div>
        </div>
        
        <div class="card-stat-main bg-green">
            <div class="stat-info">
                <span class="stat-label-main">Pendapatan Hari Ini</span>
                <h2 class="stat-number-main">Rp <?= number_format($netto_kasir, 0, ',', '.'); ?></h2>
            </div>
            <div class="stat-icon-main">＄</div>
        </div>
        
        <div class="card-stat-main bg-orange">
            <div class="stat-info">
                <span class="stat-label-main">Minggu Ini</span>
                <h2 class="stat-number-main">Rp <?= number_format($netto_kasir, 0, ',', '.'); ?></h2>
            </div>
            <div class="stat-icon-main">📈</div>
        </div>
    </div>

    <div class="main-content-grid">
        
        <div class="panel-card-layout">
            <h3 class="panel-title-bold">Ringkasan Penjualan</h3>
            <div class="summary-list">
                <div class="summary-item">
                    <span class="item-label">Hari Ini</span>
                    <span class="item-value text-green font-bold">Rp <?= number_format($total_pendapatan_bruto, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-item sub-item">
                    <span class="item-label">└ Kantin 5%</span>
                    <span class="item-value text-muted">Rp <?= number_format($potongan_kantin, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-item sub-item">
                    <span class="item-label">└ Netto Kasir</span>
                    <span class="item-value text-blue font-bold">Rp <?= number_format($netto_kasir, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-item spacer">
                    <span class="item-label">Minggu Ini</span>
                    <span class="item-value text-green font-bold">Rp <?= number_format($netto_kasir, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-item spacer">
                    <span class="item-label">Total Pesanan</span>
                    <span class="item-value text-red font-bold"><?= $total_nota; ?></span>
                </div>
                
                <div class="summary-item">
                    <span class="item-label">Pesanan Hari Ini</span>
                    <span class="item-value text-red font-bold"><?= $total_nota; ?></span>
                </div>
            </div>
        </div>

        <div class="panel-card-layout">
            <div class="panel-title-with-icon">
                <span class="icon-trend">📊</span>
                <h3 class="panel-title-bold-inline">Menu Best Seller</h3>
            </div>
            
            <div class="best-seller-list" style="display: flex; flex-direction: column; gap: 12px; margin-top: 15px;">
                <?php 
                $rank = 1;
                if (mysqli_num_rows($query_best) > 0):
                    while($best = mysqli_fetch_assoc($query_best)): 
                ?>
                    <div class="best-seller-item" style="display: flex; align-items: center; justify-content: space-between; padding: 10px; background: #f8f9fa; border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="item-rank-badge" style="background: #e3f2fd; color: #0d47a1; font-weight: bold; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 13px;"><?= $rank++; ?></div>
                            <div class="item-menu-details" style="display: flex; flex-direction: column;">
                                <span class="menu-name" style="font-weight: 600; color: #333; font-size: 14px;"><?= htmlspecialchars($best['nama_menu']); ?></span>
                                <span class="menu-sold" style="font-size: 12px; color: #6c757d;"><?= $best['total_laku']; ?> porsi terjual</span>
                            </div>
                        </div>
                        <span class="menu-price text-green font-bold" style="font-size: 14px; color: #2e7d32;">Rp <?= number_format($best['harga'], 0, ',', '.'); ?></span>
                    </div>
                <?php 
                    endwhile; 
                else:
                ?>
                    <div style="text-align: center; color: #888; font-size: 13px; padding: 20px 0;">
                        Belum ada data penjualan menu.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>