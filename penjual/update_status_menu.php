<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'penjual') {
    http_response_code(403);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penjual = $_SESSION['id_user'];
    $id_menu = mysqli_real_escape_string($koneksi, $_POST['id_menu']);
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status']);

    if ($status_baru === 'tersedia' || $status_baru === 'habis') {
        // Query disesuaikan dengan nama tabel 'menus' milik Anda
        $update = mysqli_query($koneksi, "UPDATE menus SET status = '$status_baru' WHERE id = '$id_menu' AND id_penjual = '$id_penjual'");
        
        if ($update) {
            http_response_code(200);
            echo "Success";
        } else {
            http_response_code(500);
        }
    } else {
        http_response_code(400);
    }
}
?>