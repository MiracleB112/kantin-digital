<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Kantin Digital</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-card">
    <div class="logo-icon">📝</div>
    <div class="title">Daftar Akun</div>
    <div class="subtitle">Bergabung dengan Kantin Digital</div>

    <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'email_ada'): ?>
        <div class="alert">
             <strong>Gagal Daftar!</strong> Email tersebut sudah digunakan.
        </div>
    <?php endif; ?>

    <form action="auth/proses_register.php" method="POST">
        
        <div class="form-group">
            <label>Nama Lengkap / Nama Toko</label>
            <input type="text" name="nama" placeholder="Nama Anda atau Nama Stand" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan email aktif" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Buat password baru" required>
        </div>

        <div class="form-group">
            <label>Mendaftar Sebagai</label>
            <select name="role" required>
                <option value="mahasiswa">Mahasiswa (Pembeli)</option>
                <option value="penjual">Penjual (Tenant Kantin)</option>
            </select>
        </div>

        <button type="submit" name="register" class="btn-submit">Daftar Sekarang</button>
    </form>

    <div class="register-link">
        Sudah punya akun? <a href="index.php">Log In di sini</a>
    </div>
</div>

</body>
</html>