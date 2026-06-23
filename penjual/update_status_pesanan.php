<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['role'] !== 'penjual') {
    http_response_code(403);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penjual = $_SESSION['id_user'];
    $id_order = intval($_POST['id_order']);
    $action = $_POST['action'];
    
    $status_baru = '';
    if ($action == 'proses') $status_baru = 'diproses';
    elseif ($action == 'siap') $status_baru = 'siap diambil';
    elseif ($action == 'selesai') $status_baru = 'selesai';
    
    if (!empty($status_baru)) {
        $update = mysqli_query($koneksi, "UPDATE orders SET status = '$status_baru' WHERE id = '$id_order' AND id_penjual = '$id_penjual'");
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