<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kantin Digital</title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

    <div class="login-card">
        <img src="assets/images/logo.png" class="logo-custom" alt="Logo Kantin Digital">
        <div class="title">Kantin Digital</div>
        <div class="subtitle">Sistem Pre-Order Multi-Tenant</div>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == 'gagal'): ?>
            <div class="alert">
                ⚠️ <strong>Login Gagal!</strong> Email, Password, atau Role tidak sesuai.
            </div>
        <?php endif; ?>

        <div class="role-tabs">
            <button type="button" class="tab-btn active" onclick="setRole('mahasiswa', this)">Mahasiswa</button>
            <button type="button" class="tab-btn" onclick="setRole('penjual', this)">Penjual</button>
        </div>

        <form action="auth/proses_login.php" method="POST">
            <input type="hidden" name="role" id="selected-role" value="mahasiswa">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email-placeholder" placeholder="Email mahasiswa" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" name="login" class="btn-submit">Masuk</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </div>
    </div>

    <script>
        function setRole(roleName, element) {
            document.querySelectorAll('.role-tabs .tab-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            element.classList.add('active');
            document.getElementById('selected-role').value = roleName;
            
            var emailInput = document.getElementById('email-placeholder');
            if(roleName === 'mahasiswa') {
                emailInput.placeholder = "Email mahasiswa";
            } else if(roleName === 'penjual') {
                emailInput.placeholder = "Email penjual/tenant";
            }
        }
    </script>

</body>
</html>