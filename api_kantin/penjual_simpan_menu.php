<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

include '../config/koneksi.php';

// Data dikirim sebagai multipart/form-data (bukan JSON), karena ada file foto
$idMenu     = isset($_POST['id_menu']) ? (int)$_POST['id_menu'] : 0; // 0 = tambah baru
$idPenjual  = isset($_POST['id_penjual']) ? (int)$_POST['id_penjual'] : 0;
$nama       = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
$harga      = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
$deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi'] ?? '-');
$status      = strtolower(mysqli_real_escape_string($koneksi, $_POST['status'] ?? 'tersedia'));

if ($idPenjual == 0 || $nama == '' || $harga == 0) {
    echo json_encode(["status" => "error", "message" => "Data menu tidak lengkap"]);
    exit;
}

// Upload foto jika ada file baru dikirim
$namaFotoBaru = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $folderUpload = '../assets/images/';
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $namaFotoBaru = "menu_" . time() . "_" . rand(100, 999) . "." . $ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], $folderUpload . $namaFotoBaru);
}

if ($idMenu == 0) {
    // TAMBAH MENU BARU
    $foto = $namaFotoBaru ?? '';
    $query = "INSERT INTO menus (id_penjual, nama_menu, deskripsi, harga, status, foto) 
              VALUES ($idPenjual, '$nama', '$deskripsi', $harga, '$status', '$foto')";
} else {
    // EDIT MENU YANG SUDAH ADA
    $fotoUpdate = $namaFotoBaru ? ", foto = '$namaFotoBaru'" : "";
    $query = "UPDATE menus 
              SET nama_menu = '$nama', deskripsi = '$deskripsi', harga = $harga, status = '$status' $fotoUpdate 
              WHERE id = $idMenu";
}

if (mysqli_query($koneksi, $query)) {
    echo json_encode(["status" => "success", "message" => "Menu berhasil disimpan"]);
} else {
    echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
?>