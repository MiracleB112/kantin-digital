<?php
session_start();
include '../config/koneksi.php';

$id = $_POST['id'];

$nama_menu = $_POST['nama_menu'];
$harga = $_POST['harga'];
$deskripsi = $_POST['deskripsi'];
$status = $_POST['status'];

if($_FILES['foto']['name'] != ''){

    $foto = time().$_FILES['foto']['name'];

    move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        "../assets/images/".$foto
    );

    mysqli_query($koneksi,"
    UPDATE menus
    SET
    nama_menu='$nama_menu',
    harga='$harga',
    deskripsi='$deskripsi',
    status='$status',
    foto='$foto'
    WHERE id='$id'
    ");

}else{

    mysqli_query($koneksi,"
    UPDATE menus
    SET
    nama_menu='$nama_menu',
    harga='$harga',
    deskripsi='$deskripsi',
    status='$status'
    WHERE id='$id'
    ");
}

header("Location: menu.php");