<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Pusat Kendali</title>
    <link rel="stylesheet" href="../assets/css/login_admin.css">
</head>
<body>

<div class="login-card">
    <img src="../assets/images/logo.png" class="logo-custom" alt="Logo Kantin Digital">
    <div class="title">Admin Login</div>
    <div class="subtitle">Sistem Kendali Pusat Kantin Digital</div>

    <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'gagal'): ?>
        <div class="alert">
            ⚠️ <strong>Akses Ditolak!</strong> Kredensial Admin tidak cocok.
        </div>
    <?php endif; ?>

    <form action="proses_login.php" method="POST">
        
        <div class="form-group">
            <label>Username / Email Admin</label>
            <input type="email" name="email" placeholder="Email khusus admin" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Password sistem" required>
        </div>

        <button type="submit" name="login_admin" class="btn-submit" style="background-color: #212529;">Masuk Sistem</button>
    </form>
</div>

</body>
</html>