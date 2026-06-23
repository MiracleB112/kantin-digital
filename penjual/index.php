<?php
session_start();
include '../config/koneksi.php';

// Proteksi: Pastikan sudah login dan merupakan penjual
if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit();
}

$id_penjual = $_SESSION['id_user'];
setlocale(LC_TIME, 'id_ID', 'Indonesian');
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
        <a href="../auth/logout.php" class="btn-logout" style="text-decoration: none;">➡️ Keluar</a>
    </div>

    <div class="container">
        <div class="sub-nav">
            <a href="index.php" class="btn-nav active">📋 Pesanan Masuk</a>
            <a href="menu.php" class="btn-nav inactive">🍔 Kelola Menu</a>
            <a href="laporan.php" class="btn-nav inactive">📈 Laporan Penjualan</a>
            <a href="riwayat.php" class="btn-nav inactive">📜 Riwayat Pesanan</a>
        </div>

        <h3 class="panel-title-bold" style="margin-bottom: 20px;">Daftar Pre-Order Mahasiswa</h3>

        <div class="orders-card-grid">
            <?php
            $sql = "SELECT orders.*, users.nama as nama_mahasiswa 
                FROM orders 
                JOIN users ON orders.id_mahasiswa = users.id 
                WHERE orders.id_penjual = '$id_penjual'
                AND orders.status != 'selesai'
                ORDER BY orders.id DESC";
            $query = mysqli_query($koneksi, $sql);

            if (mysqli_num_rows($query) > 0):
                while ($row = mysqli_fetch_assoc($query)):
                    $id_order = $row['id'];
                    $st = $row['status']; // pending, diproses, siap diambil, selesai
            
                    // Penentuan class border pembungkus berdasarkan status saat ini
                    $border_class = 'border-pending';
                    if ($st == 'diproses')
                        $border_class = 'border-diproses';
                    elseif ($st == 'siap diambil')
                        $border_class = 'border-siap';
                    elseif ($st == 'selesai')
                        $border_class = 'border-selesai';
                    ?>
                    <div class="order-main-card <?= $border_class; ?>" id="order-card-<?= $id_order; ?>">
                        <div class="order-card-header">
                            <div>
                                <span class="order-id-badge">
                                    #ORD-<?= $id_order; ?>
                                </span>
                                <span class="order-time">
                                    <?php

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

                                    $tanggal = strtotime($row['tanggal_pesan']);

                                    echo "📅 " . $hari[date('l', $tanggal)] . ", " .
                                        date('d', $tanggal) . " " .
                                        $bulan[date('n', $tanggal)] . " " .
                                        date('Y', $tanggal);

                                    echo " • 🕒 " . date('H:i', $tanggal) . " WIB";

                                    ?>
                                </span>
                            </div>
                            <span class="badge-status state-<?= str_replace(' ', '-', $st); ?>"
                                id="badge-text-<?= $id_order; ?>">
                                <?= ucwords($st); ?>
                            </span>
                        </div>

                        <div class="order-card-body">
                            <h4 class="customer-name">👤 <?= htmlspecialchars($row['nama_mahasiswa']); ?></h4>

                            <div class="order-items-list">
                                <?php
                                $sql_det = "SELECT order_details.*, menus.nama_menu FROM order_details JOIN menus ON order_details.id_menu = menus.id WHERE id_order = '$id_order'";
                                $query_det = mysqli_query($koneksi, $sql_det);
                                while ($det = mysqli_fetch_assoc($query_det)):
                                    ?>
                                    <div class="item-product-row">
                                        <span class="item-name">🍽️ <?= htmlspecialchars($det['nama_menu']); ?></span>
                                        <span class="item-qty">x<?= $det['jumlah']; ?></span>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="order-card-footer">
                            <div class="total-price-block">
                                <span class="total-label">Total Bayar</span>
                                <span class="total-amount">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></span>
                            </div>

                            <div class="action-button-zone" id="action-zone-<?= $id_order; ?>">
                                <?php if ($st == 'pending'): ?>
                                    <button type="button" class="btn-action-process"
                                        onclick="changeOrderStatus(<?= $id_order; ?>, 'proses')">🧑‍🍳 Proses Masak</button>
                                <?php elseif ($st == 'diproses'): ?>
                                    <button type="button" class="btn-action-ready"
                                        onclick="changeOrderStatus(<?= $id_order; ?>, 'siap')">📦 Siap Diambil</button>
                                <?php elseif ($st == 'siap diambil'): ?>
                                    <button type="button" class="btn-action-done"
                                        onclick="changeOrderStatus(<?= $id_order; ?>, 'selesai')">✅ Selesai / Diambil</button>
                                <?php else: ?>
                                    <span class="text-completed">✔️ Transaksi Selesai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                endwhile;
            else:
                ?>
                <div
                    style="grid-column: 1/-1; text-align: center; color: #6c757d; padding: 40px 0; background: #fff; border-radius: 12px; border: 1px solid #dee2e6;">
                    📭 Belum ada pesanan masuk dari mahasiswa.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function changeOrderStatus(orderId, actionType) {
            const card = document.getElementById('order-card-' + orderId);
            const badgeText = document.getElementById('badge-text-' + orderId);
            const actionZone = document.getElementById('action-zone-' + orderId);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_status_pesanan.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                if (this.status === 200) {
                    if (actionType === 'proses') {
                        card.className = "order-main-card border-diproses";
                        badgeText.className = "badge-status state-diproses";
                        badgeText.innerText = "Diproses";
                        actionZone.innerHTML = `<button type="button" class="btn-action-ready" onclick="changeOrderStatus(${orderId}, 'siap')">📦 Siap Diambil</button>`;
                    } else if (actionType === 'siap') {
                        card.className = "order-main-card border-siap";
                        badgeText.className = "badge-status state-siap-diambil";
                        badgeText.innerText = "Siap Diambil";
                        actionZone.innerHTML = `<button type="button" class="btn-action-done" onclick="changeOrderStatus(${orderId}, 'selesai')">✅ Selesai / Diambil</button>`;
                    } else if (actionType === 'selesai') {
                        card.className = "order-main-card border-selesai";
                        badgeText.className = "badge-status state-selesai";
                        badgeText.innerText = "Selesai";
                        actionZone.innerHTML = `<span class="text-completed">✔️ Transaksi Selesai</span>`;
                    }
                } else {
                    alert("Gagal memperbarui status pesanan.");
                }
            };

            xhr.send("id_order=" + orderId + "&action=" + actionType);
        }
    </script>

</body>

</html>