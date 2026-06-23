<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit();
}

$id_penjual = $_SESSION['id_user'];

// Proses Input Menu Baru + Upload Gambar
if (isset($_POST['tambah_menu'])) {
    $nama_menu = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga = intval($_POST['harga']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Konfigurasi Upload Gambar
    $nama_file = $_FILES['foto']['name'];
    $ukuran_file = $_FILES['foto']['size'];
    $error_file = $_FILES['foto']['error'];
    $tmp_name = $_FILES['foto']['tmp_name'];

    if ($error_file === 0) {
        $ekstensi_valid = ['jpg', 'jpeg', 'png'];
        $ekstensi_file = explode('.', $nama_file);
        $ekstensi_file = strtolower(end($ekstensi_file));

        if (in_array($ekstensi_file, $ekstensi_valid)) {
            if ($ukuran_file < 2000000) {
                $nama_file_baru = uniqid() . '.' . $ekstensi_file;
                $folder_tujuan = '../assets/images/' . $nama_file_baru;
                move_uploaded_file($tmp_name, $folder_tujuan);
            } else {
                header("Location: menu.php?pesan=ukuran_terlalu_besar");
                exit();
            }
        } else {
            header("Location: menu.php?pesan=ekstensi_salah");
            exit();
        }
    } else {
        $nama_file_baru = 'default-food.jpg';
    }

    mysqli_query($koneksi, "INSERT INTO menus (id_penjual, nama_menu, deskripsi, harga, foto, status) VALUES ('$id_penjual', '$nama_menu', '$deskripsi', '$harga', '$nama_file_baru', '$status')");
    header("Location: menu.php?pesan=berhasil_tambah");
    exit();
}

// Proses Hapus Menu
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);

    $get_foto = mysqli_query($koneksi, "SELECT foto FROM menus WHERE id = '$id_hapus' AND id_penjual = '$id_penjual'");
    $data_foto = mysqli_fetch_assoc($get_foto);
    if ($data_foto['foto'] && $data_foto['foto'] != 'default-food.jpg') {
        if (file_exists('../assets/images/' . $data_foto['foto'])) {
            unlink('../assets/images/' . $data_foto['foto']);
        }
    }

    mysqli_query($koneksi, "DELETE FROM menus WHERE id = '$id_hapus' AND id_penjual = '$id_penjual'");
    header("Location: menu.php?pesan=berhasil_hapus");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Tenant</title>
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
            <a href="index.php" class="btn-nav inactive">📋 Pesanan Masuk</a>
            <a href="menu.php" class="btn-nav active">🍔 Kelola Menu</a>
            <a href="laporan.php" class="btn-nav inactive">📈 Laporan Penjualan</a>
            <a href="riwayat.php" class="btn-nav inactive">📜 Riwayat Pesanan</a>

        </div>

        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] == 'ekstensi_salah'): ?>
                <div
                    style="background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px; font-weight:bold;">
                    ⚠️ Format file harus JPG, JPEG, atau PNG!</div>
            <?php elseif ($_GET['pesan'] == 'ukuran_terlalu_besar'): ?>
                <div
                    style="background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:15px; font-weight:bold;">
                    ⚠️ Ukuran foto terlalu besar! Maksimal 2MB.</div>
            <?php elseif ($_GET['pesan'] == 'berhasil_tambah'): ?>
                <div
                    style="background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px; font-weight:bold;">
                    ✅ Menu baru berhasil disimpan beserta foto!</div>
            <?php elseif ($_GET['pesan'] == 'berhasil_hapus'): ?>
                <div
                    style="background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:15px; font-weight:bold;">
                    ✅ Menu berhasil dihapus dari katalog.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="panel-card" style="margin-bottom: 30px;">
            <h3 class="panel-title-bold" style="margin-top:0; margin-bottom:20px;">Tambah Menu Makanan Baru</h3>
            <form action="menu.php" method="POST" enctype="multipart/form-data">

                <div class="form-row-layout">
                    <div class="form-group-cell">
                        <label>Nama Makanan / Minuman</label>
                        <input type="text" name="nama_menu" placeholder="Contoh: Nasi Goreng Gila" required>
                    </div>
                    <div class="form-group-cell">
                        <label>Harga Jual (Rupiah)</label>
                        <input type="number" name="harga" placeholder="Contoh: 15000" required>
                    </div>
                    <div class="form-group-cell">
                        <label>Status Ketersediaan</label>
                        <select name="status">
                            <option value="tersedia">Tersedia (Ready)</option>
                            <option value="habis">Habis (Kosong)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-layout" style="margin-top: 15px;">
                    <div class="form-group-cell-twothird">
                        <label>Deskripsi Menu</label>
                        <input type="text" name="deskripsi" placeholder="Sebutkan topping atau level kepedasan..."
                            required>
                    </div>
                    <div class="form-group-cell-onethird">
                        <label>Foto Produk</label>
                        <input type="file" name="foto" accept="image/*" class="input-file-clean">
                    </div>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" name="tambah_menu" class="btn-submit-menu">➕ Simpan Menu Ke Toko</button>
                </div>
            </form>
        </div>

        <div class="katalog-container-full">
            <h3 class="panel-title-bold" style="margin-top: 0; margin-bottom: 20px;">Daftar Katalog Menu Restoran</h3>

            <div class="menu-grid-view">
                <?php
                $query_m = mysqli_query($koneksi, "SELECT * FROM menus WHERE id_penjual = '$id_penjual' ORDER BY id DESC");
                if (mysqli_num_rows($query_m) > 0):
                    while ($menu = mysqli_fetch_assoc($query_m)):
                        $status = isset($menu['status']) ? $menu['status'] : 'tersedia';
                        $is_ready = ($status == 'tersedia');
                        ?>
                        <div class="menu-item-card <?= !$is_ready ? 'card-habis' : ''; ?>" id="card-<?= $menu['id']; ?>">
                            <div class="menu-item-image">
                                <img src="../assets/images/<?= $menu['foto'] ? $menu['foto'] : 'default-food.jpg'; ?>"
                                    alt="Foto Menu">
                            </div>
                            <div class="menu-item-body">
                                <h4 class="menu-item-title"><?= htmlspecialchars($menu['nama_menu']); ?></h4>
                                <p class="menu-item-desc"><?= htmlspecialchars($menu['deskripsi']); ?></p>
                                <p class="menu-item-price">Rp <?= number_format($menu['harga'], 0, ',', '.'); ?></p>

                                <div class="menu-item-actions">
                                    <button type="button"
                                        class="btn-toggle-ready <?= $is_ready ? 'style-active' : 'style-inactive'; ?>"
                                        onclick="toggleMenuStatus(<?= $menu['id']; ?>, '<?= $status; ?>')">
                                        <?= $is_ready ? '🟢 Tersedia' : '🔴 Habis'; ?>
                                    </button>

                                    <div class="menu-actions">

                                        <a href="edit_menu.php?id=<?= $menu['id']; ?>" class="btn-edit">
                                            ✏️
                                        </a>

                                        <a href="hapus_menu.php?id=<?= $menu['id']; ?>" class="btn-delete"
                                            onclick="return confirm('Hapus menu ini?')">
                                            🗑️
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    endwhile;
                else:
                    ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #6c757d; padding: 40px 0;">
                        👋 Belum ada menu terdaftar di toko ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleMenuStatus(menuId, currentStatus) {
            const button = event.currentTarget;
            const card = document.getElementById('card-' + menuId);
            const newStatus = (currentStatus === 'tersedia') ? 'habis' : 'tersedia';

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_status_menu.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                if (this.status === 200) {
                    if (newStatus === 'tersedia') {
                        button.innerText = "🟢 Tersedia";
                        button.className = "btn-toggle-ready style-active";
                        card.classList.remove('card-habis');
                        button.setAttribute('onclick', `toggleMenuStatus(${menuId}, 'tersedia')`);
                    } else {
                        button.innerText = "🔴 Habis";
                        button.className = "btn-toggle-ready style-inactive";
                        card.classList.add('card-habis');
                        button.setAttribute('onclick', `toggleMenuStatus(${menuId}, 'habis')`);
                    }
                } else {
                    alert("Gagal memperbarui status ke server.");
                }
            };

            xhr.send("id_menu=" + menuId + "&status=" + newStatus);
        }

// edit menu
document.getElementById('fotoInput')
.addEventListener('change', function(e){

    const file = e.target.files[0];

    if(file){

        document.getElementById('previewFoto')
        .src = URL.createObjectURL(file);

    }

});
    </script>

</body>

</html>