<?php
session_start();
include '../config/koneksi.php';

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';
$id_menu = isset($_GET['id']) ? intval($_GET['id']) : 0;
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

if ($aksi == 'tambah' && $id_menu > 0) {
    $query = mysqli_query($koneksi, "SELECT harga, id_penjual FROM menus WHERE id = '$id_menu'");
    if (mysqli_num_rows($query) > 0) {
        $menu = mysqli_fetch_assoc($query);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id_menu])) {
            $_SESSION['cart'][$id_menu]['qty'] += 1;
        } else {
            $_SESSION['cart'][$id_menu] = [
                'id_penjual' => $menu['id_penjual'],
                'harga' => $menu['harga'],
                'qty' => 1
            ];
        }
    }
}

if ($aksi == 'hapus' && $id_menu > 0) {
    unset($_SESSION['cart'][$id_menu]);
}

if ($aksi == 'kosongkan') {
    unset($_SESSION['cart']);
}

header("Location: " . $redirect);
exit();
?>