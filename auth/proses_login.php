<?php
// Memulai session PHP
session_start();

// Menghubungkan ke file koneksi database
include '../config/koneksi.php';

if (isset($_POST['login'])) {
    // Mengamankan input dari sql injection
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Query untuk mencocokkan email, password, dan role sekaligus
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND password='$password' AND role='$role'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Menyimpan data user ke dalam session global
        $_SESSION['id_user'] = $data['id'];
        $_SESSION['nama']    = $data['nama'];
        $_SESSION['role']    = $data['role'];
        $_SESSION['status']  = "login";

        // Mengalihkan halaman berdasarkan role-nya (menggunakan ../ karena keluar dari folder auth)
        if ($data['role'] == 'mahasiswa') {
            header("Location: ../mahasiswa/index.php");
        } elseif ($data['role'] == 'penjual') {
            header("Location: ../penjual/index.php");
        } elseif ($data['role'] == 'admin') {
            header("Location: ../admin/index.php");
        }
        exit();
    } else {
        // Jika data tidak ditemukan atau tidak cocok, balikkan ke halaman login dengan status gagal
        header("Location: ../index.php?pesan=gagal");
        exit();
    }
} else {
    // Jika diakses tanpa menekan tombol login, tendang balik ke index
    header("Location: ../index.php");
    exit();
}
?>