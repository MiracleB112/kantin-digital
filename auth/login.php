<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password']; // Disarankan pakai password_verify jika di-hash

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Simpan data user ke session
        $_SESSION['id_user'] = $data['id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['role'] = $data['role'];

        // Redirect sesuai role masing-masing
        if ($data['role'] == 'mahasiswa') {
            header("Location: ../mahasiswa/index.php");
        } elseif ($data['role'] == 'penjual') {
            header("Location: ../penjual/index.php");
        } elseif ($data['role'] == 'admin') {
            header("Location: ../admin/index.php");
        }
    } else {
        header("Location: ../index.php?pesan=gagal");
    }
}
?>