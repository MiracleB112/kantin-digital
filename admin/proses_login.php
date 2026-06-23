<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['login_admin'])) {
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Query mencari user dengan email dan password tersebut yang rolenya 'admin'
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND password='$password' AND role='admin'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        $_SESSION['id_user'] = $data['id'];
        $_SESSION['nama']    = $data['nama'];
        $_SESSION['role']    = $data['role'];
        $_SESSION['status']  = "login";

        // Berhasil, langsung lempar ke index.php di dalam folder admin
        header("Location: index.php");
        exit();
    } else {
        header("Location: login.php?pesan=gagal");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>