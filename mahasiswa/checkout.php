<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'mahasiswa' || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}
$id_mahasiswa = $_SESSION['id_user'];

$total_harga = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_harga += ($item['harga'] * $item['qty']);
}

$first_item = array_values($_SESSION['cart'])[0];
$id_penjual = $first_item['id_penjual'];

$query_user = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id = '$id_mahasiswa'");
$user = mysqli_fetch_assoc($query_user);

if ($user['saldo'] < $total_harga) {
    header("Location: index.php?error=saldo_kurang");
    exit();
}

$sisa_saldo = $user['saldo'] - $total_harga;
mysqli_query($koneksi, "UPDATE users SET saldo = '$sisa_saldo' WHERE id = '$id_mahasiswa'");

$insert_order = mysqli_query($koneksi, "
INSERT INTO orders (id_mahasiswa, id_penjual, total_harga, status)
VALUES ('$id_mahasiswa', '$id_penjual', '$total_harga', 'pending')
");

$id_order_baru = mysqli_insert_id($koneksi);

foreach ($_SESSION['cart'] as $id_menu => $item) {
    mysqli_query($koneksi, "
    INSERT INTO order_details (id_order, id_menu, jumlah, harga_satuan)
    VALUES ('$id_order_baru', '$id_menu', '{$item['qty']}', '{$item['harga']}')
    ");
}

unset($_SESSION['cart']);

header("Location: riwayat.php?pesan=sukses_order");
exit();
?>