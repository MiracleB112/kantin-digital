<?php
include '../config/koneksi.php';

if (isset($_POST['register'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']); // Bisa pakai password_hash jika diwajibkan oleh dosen
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);

    // 1. Cek dulu ke database apakah email sudah pernah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($cek_email) > 0) {
        // Jika email sudah ada, kembalikan ke halaman register dengan pesan peringatan
        header("Location: ../register.php?pesan=email_ada");
        exit();
    } else {
        // 2. Jika email bersih, lakukan query insert ke tabel users
        $query_insert = mysqli_query($koneksi, "INSERT INTO users (nama, email, password, role, saldo) VALUES ('$nama', '$email', '$password', '$role', 0)");
        
        if ($query_insert) {
            // Registrasi sukses, lempar ke halaman login utama
            header("Location: ../index.php?pesan=registrasi_sukses");
            exit();
        } else {
            echo "Eror Sistem: Gagal menyimpan data pengguna baru.";
        }
    }
} else {
    header("Location: ../register.php");
    exit();
}
?>