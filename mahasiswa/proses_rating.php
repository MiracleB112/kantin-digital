<?php
session_start();
include '../config/koneksi.php';

$id_mahasiswa = $_SESSION['id_user'];
$id_order = $_POST['id_order'];

$id_menu = $_POST['id_menu'];
$rating = $_POST['rating'];
$ulasan = $_POST['ulasan'];

for($i=0; $i<count($id_menu); $i++){

    $idMenuSekarang = $id_menu[$i];

    $nilaiRating = isset($rating[$idMenuSekarang])
        ? $rating[$idMenuSekarang]
        : 0;

    mysqli_query($koneksi,"
    INSERT INTO ratings
    (
        id_order,
        id_menu,
        id_mahasiswa,
        rating,
        ulasan
    )
    VALUES
    (
        '$id_order',
        '$idMenuSekarang',
        '$id_mahasiswa',
        '$nilaiRating',
        '{$ulasan[$i]}'
    )
    ");
}

header("Location: riwayat.php");