<?php
$koneksi = mysqli_connect("127.0.0.1","root","","kantin-digital",3306);

if(!$koneksi){
    die("Koneksi gagal: " . mysqli_connect_error());
}

// =======================================================================
// TAMBAHAN: Sinkronisasi Waktu ke Asia/Jakarta (WIB)
// =======================================================================
// 1. Mengatur zona waktu default untuk fungsi date() di PHP
date_default_timezone_set('Asia/Jakarta');

// 2. Mengatur zona waktu session MySQL agar sinkron dengan PHP
mysqli_query($koneksi, "SET time_zone = '+07:00'");
?>