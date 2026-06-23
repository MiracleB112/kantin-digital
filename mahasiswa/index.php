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

// 3. Logika Filter Tenant
$filter_tenant = isset($_GET['tenant']) ? $_GET['tenant'] : 'semua';






// BEST SELLER penambahan 
$query_best = mysqli_query($koneksi, "
SELECT
    menus.id,
    AVG(ratings.rating) AS rata_rating,
    COUNT(ratings.id) AS total_rating
FROM ratings
JOIN menus ON ratings.id_menu = menus.id
GROUP BY menus.id
HAVING total_rating >= 5
AND rata_rating >= 4
ORDER BY rata_rating DESC
LIMIT 3
");

$bestSellerIds = [];

while ($best = mysqli_fetch_assoc($query_best)) {
    $bestSellerIds[] = $best['id'];
}

$query_best_seller = mysqli_query($koneksi, "
    SELECT
        menus.id,
        menus.nama_menu,
        menus.foto,
        menus.harga,
        AVG(ratings.rating) AS rata_rating,
        COUNT(ratings.id) AS total_rating
    FROM ratings
    JOIN menus ON ratings.id_menu = menus.id
    GROUP BY menus.id
    HAVING total_rating >= 5
    AND rata_rating >= 4
    ORDER BY rata_rating DESC
    LIMIT 5
");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Kantin Digital</title>
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

    <div class="container">
        <div class="main-content">

            <div class="info-cards">
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
                <a href="index.php" class="btn-nav active">🍴 Pesan Makanan</a>
                <a href="riwayat.php" class="btn-nav inactive">⏳ Riwayat Pesanan</a>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'saldo_kurang'): ?>
                <div
                    style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: bold;">
                    ⚠️ Saldo E-Wallet Anda tidak mencukupi untuk melakukan pemesanan ini! Silakan hubungi admin kantin untuk
                    top up.
                </div>
            <?php endif; ?>

            <div class="filter-section">
                <div class="filter-title">Pilih Penjual:</div>
                <div class="filter-tabs">
                    <a href="index.php?tenant=semua"
                        class="filter-btn <?= $filter_tenant == 'semua' ? 'active' : ''; ?>">Semua Penjual</a>

                    <?php
                    // Mengambil list daftar tenant/penjual yang ada di database
                    $query_tenant = mysqli_query($koneksi, "SELECT id, nama FROM users WHERE role = 'penjual'");
                    while ($tenant = mysqli_fetch_assoc($query_tenant)):
                        ?>
                        <a href="index.php?tenant=<?= $tenant['id']; ?>"
                            class="filter-btn <?= $filter_tenant == $tenant['id'] ? 'active' : ''; ?>">
                            <?= htmlspecialchars($tenant['nama']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="menu-grid">
                <?php
                // Query menu makanan beserta nama toko penjualnya
                if ($filter_tenant == 'semua') {

                    $sql_menu = "
    SELECT
        menus.*,
        users.nama AS nama_toko,

        (
            SELECT ROUND(AVG(rating),1)
            FROM ratings
            WHERE ratings.id_menu = menus.id
        ) AS rata_rating,

        (
            SELECT COUNT(*)
            FROM ratings
            WHERE ratings.id_menu = menus.id
        ) AS total_rating,

        (
            SELECT COALESCE(SUM(jumlah),0)
            FROM order_details
            WHERE order_details.id_menu = menus.id
        ) AS total_terjual

    FROM menus
    JOIN users
    ON menus.id_penjual = users.id

    WHERE menus.status='tersedia'
    ";

                } else {

                    $sql_menu = "
    SELECT
        menus.*,
        users.nama AS nama_toko,

        (
            SELECT ROUND(AVG(rating),1)
            FROM ratings
            WHERE ratings.id_menu = menus.id
        ) AS rata_rating,

        (
            SELECT COUNT(*)
            FROM ratings
            WHERE ratings.id_menu = menus.id
        ) AS total_rating,

        (
            SELECT COALESCE(SUM(jumlah),0)
            FROM order_details
            WHERE order_details.id_menu = menus.id
        ) AS total_terjual

    FROM menus
    JOIN users
    ON menus.id_penjual = users.id

    WHERE menus.status='tersedia'
    AND menus.id_penjual='$filter_tenant'
    ";
                }

                $query_menu = mysqli_query($koneksi, $sql_menu);
                if (mysqli_num_rows($query_menu) > 0):
                    while ($menu = mysqli_fetch_assoc($query_menu)):
                        ?>
                        <div class="menu-card">
                            <!-- menu card menambahkan bestseller di colum pesanan  -->
                            <?php if (in_array($menu['id'], $bestSellerIds)): ?>
                                <span class="badge-best">
                                    🔥 Best Seller
                                </span>
                            <?php endif; ?>
                            <img src="../assets/images/<?= $menu['foto'] ? $menu['foto'] : 'default-food.jpg'; ?>"
                                class="menu-img" alt="Foto Makanan">
                            <div class="menu-body">
                                <div class="tenant-name"><?= htmlspecialchars($menu['nama_toko']); ?></div>
                                <h4 class="menu-title">
                                    <?= htmlspecialchars($menu['nama_menu']); ?>
                                </h4>

                                <div class="menu-rating">
                                    ⭐ <?= $menu['rata_rating']; ?>

                                    <span>
                                        <?= $menu['total_rating']; ?> ulasan
                                    </span>
                                </div>

                                <button type="button" class="btn-ulasan" onclick="bukaUlasan(<?= $menu['id']; ?>)">
                                    ⭐ Lihat Ulasan
                                </button>

                                <div class="menu-sales">
                                    👥 <?= $menu['total_terjual']; ?> pembelian
                                </div>
                                <p class="menu-desc">
                                    <?= htmlspecialchars($menu['deskripsi']); ?>
                                </p>
                                <div class="menu-footer">
                                    <span class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></span>
                                    <a href="aksi_keranjang.php?aksi=tambah&id=<?= $menu['id']; ?>" class="btn-add"
                                        style="text-decoration: none;">+ Tambah</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else:
                    echo "<p style='color:#6c757d; grid-column: 1/-1;'>Belum ada menu makanan yang tersedia dari penjual ini.</p>";
                endif;
                ?>
            </div>

        </div>

        <div class="sidebar-cart">
            <div class="cart-card">
                <?php
                $total_items = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                ?>
                <h3 class="cart-title">🛒 Keranjang (<?= $total_items; ?>)</h3>

                <?php if ($total_items > 0): ?>
                    <div class="cart-items-list">
                        <?php
                        $grand_total = 0;
                        foreach ($_SESSION['cart'] as $id_menu_cart => $item_cart):
                            $get_menu_info = mysqli_query($koneksi, "SELECT nama_menu FROM menus WHERE id = '$id_menu_cart'");
                            $menu_info = mysqli_fetch_assoc($get_menu_info);
                            $subtotal = $item_cart['harga'] * $item_cart['qty'];
                            $grand_total += $subtotal;
                            ?>
                            <div class="cart-item">
                                <div class="cart-item-info">
                                    <h5><?= htmlspecialchars($menu_info['nama_menu']); ?></h5>
                                    <span><?= $item_cart['qty']; ?> x Rp
                                        <?= number_format($item_cart['harga'], 0, ',', '.'); ?></span>
                                </div>
                                <div>
                                    <a href="aksi_keranjang.php?aksi=hapus&id=<?= $id_menu_cart; ?>" class="btn-delete-item"
                                        style="text-decoration: none;">❌</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-total">
                        <span>Total:</span>
                        <span style="color: #f24e1e;">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                    </div>

                    <a href="checkout.php" class="btn-checkout"
                        onclick="return confirm('Apakah Anda yakin ingin memesan makanan ini?')">Pesan Sekarang</a>
                    <p style="text-align: center; margin-top: 10px;"><a href="aksi_keranjang.php?aksi=kosongkan"
                            style="color: #6c757d; font-size: 12px; text-decoration: none;">Kosongkan Keranjang</a></p>
                <?php else: ?>
                    <div class="cart-empty">
                        Keranjang masih kosong
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- ulasan  -->
    <div id="modalUlasan" class="modal-ulasan">

        <div class="modal-content-ulasan">

            <span class="close-modal" onclick="tutupUlasan()">
                ✖
            </span>

            <iframe id="frameUlasan" src="" width="100%" height="100%" frameborder="0">
            </iframe>

        </div>

    </div>
    <script>

        function bukaUlasan(idMenu) {

            document.getElementById("modalUlasan")
                .style.display = "flex";

            document.getElementById("frameUlasan")
                .src = "ulasan.php?id_menu=" + idMenu;

        }

        function tutupUlasan() {

            document.getElementById("modalUlasan")
                .style.display = "none";

            document.getElementById("frameUlasan")
                .src = "";

        }

    </script>
</body>


</html>