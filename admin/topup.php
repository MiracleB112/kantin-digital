<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Proses Eksekusi Top Up Saldo
if (isset($_POST['proses_topup'])) {
    $id_mahasiswa = intval($_POST['id_mahasiswa']);
    $nominal = intval($_POST['nominal']);
    
    if ($id_mahasiswa > 0 && $nominal > 0) {
        // Ambil saldo lama mahasiswa terlebih dahulu
        $get_saldo = mysqli_query($koneksi, "SELECT saldo, nama FROM users WHERE id = '$id_mahasiswa'");
        $mhs = mysqli_fetch_assoc($get_saldo);
        
        $saldo_baru = $mhs['saldo'] + $nominal;
        
        // Update saldo baru ke database
        mysqli_query($koneksi, "UPDATE users SET saldo = '$saldo_baru' WHERE id = '$id_mahasiswa'");
        
        // Alihkan kembali dengan membawa parameter sukses untuk memicu notifikasi
        header("Location: topup.php?pesan=sukses&nama=" . urlencode($mhs['nama']) . "&jumlah=" . $nominal);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Up E-Wallet - Admin</title>
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
        <a href="index.php" class="btn-nav inactive">📊 Ringkasan Sistem</a>
        <a href="topup.php" class="btn-nav active">💳 Top Up Saldo</a>
    </div>

    <div class="panel-card">
        <h3 class="panel-title">Form Pengisian Saldo E-Wallet Mahasiswa</h3>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'sukses'): ?>
            <div class="alert-success">
                ✅ Berhasil menambahkan Rp <?= number_format($_GET['jumlah'], 0, ',', '.'); ?> ke akun <?= htmlspecialchars($_GET['nama']); ?>!
            </div>
        <?php endif; ?>

        <div class="form-box">
            <form action="topup.php" method="POST">
                
                <div class="form-group">
                    <label>Pilih Mahasiswa Target</label>
                    <select name="id_mahasiswa" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        <?php
                        // Menarik data user yang ber-role mahasiswa saja untuk ditampilkan di dropdown
                        $query_mhs = mysqli_query($koneksi, "SELECT id, nama, email FROM users WHERE role = 'mahasiswa' ORDER BY nama ASC");
                        while($m = mysqli_fetch_assoc($query_mhs)):
                        ?>
                            <option value="<?= $m['id']; ?>">
                                <?= htmlspecialchars($m['nama']); ?> (<?= htmlspecialchars($m['email']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nominal Top Up (Rp)</label>
                    <input type="number" name="nominal" min="1000" placeholder="Contoh: 50000" required>
                </div>

                <button type="submit" name="proses_topup" class="btn-submit">⚡ Konfirmasi & Isi Saldo</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>