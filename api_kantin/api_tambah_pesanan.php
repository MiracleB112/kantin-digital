<?php
ob_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

include '../config/koneksi.php';

$json_data = file_get_contents('php://input');
$input = json_decode($json_data, true);

$total_harga = isset($input['total_bayar']) ? (int)$input['total_bayar'] : 0;
$items = isset($input['items']) ? $input['items'] : [];

$id_mahasiswa = isset($input['id_mahasiswa']) ? (int)$input['id_mahasiswa'] : 0;
if ($id_mahasiswa == 0) {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "ID Mahasiswa tidak ditemukan!"]);
    exit;
}

if ($total_harga > 0 && !empty($items)) {

    mysqli_begin_transaction($koneksi);

    try {
        // CEK SALDO TERKINI USER (sebelum potong, untuk validasi & cegah saldo minus)
        $query_saldo = mysqli_query($koneksi, "SELECT saldo FROM users WHERE id = $id_mahasiswa");
        $user_data = mysqli_fetch_assoc($query_saldo);

        if (!$user_data) {
            throw new Exception("Data user tidak ditemukan.");
        }

        $saldo_sekarang = (int)$user_data['saldo'];

        if ($saldo_sekarang < $total_harga) {
            throw new Exception("Saldo Anda tidak cukup untuk pesanan ini.");
        }

        // 1. AMBIL id_penjual UNTUK SETIAP ITEM (bukan cuma item pertama)
        //    lalu kelompokkan item per penjual.
        $items_per_penjual = []; // [id_penjual => [item, item, ...]]

        foreach ($items as $item) {
            $id_menu = (int)$item['id_menu'];

            $query_menu = mysqli_query($koneksi, "SELECT id_penjual FROM menus WHERE id = $id_menu");
            $menu_data = mysqli_fetch_assoc($query_menu);

            if (!$menu_data) {
                throw new Exception("Menu dengan id $id_menu tidak ditemukan.");
            }

            $id_penjual = (int)$menu_data['id_penjual'];

            if (!isset($items_per_penjual[$id_penjual])) {
                $items_per_penjual[$id_penjual] = [];
            }
            $items_per_penjual[$id_penjual][] = $item;
        }

        // 2. UNTUK SETIAP PENJUAL, BUAT SATU ORDER TERPISAH BESERTA DETAILNYA
        $order_ids_dibuat = [];

        foreach ($items_per_penjual as $id_penjual => $items_penjual_ini) {

            // Hitung total harga khusus untuk penjual ini saja
            $subtotal_penjual = 0;
            foreach ($items_penjual_ini as $item) {
                $jumlah = (int)$item['qty'];
                $harga_satuan = (int)$item['harga'];
                $subtotal_penjual += $jumlah * $harga_satuan;
            }

            $sql_order = "INSERT INTO orders (id_mahasiswa, id_penjual, total_harga, status, sudah_rating)
                          VALUES ($id_mahasiswa, $id_penjual, $subtotal_penjual, 'pending', 'belum')";

            if (!mysqli_query($koneksi, $sql_order)) {
                throw new Exception("Gagal menyimpan data induk pesanan: " . mysqli_error($koneksi));
            }

            $id_order_baru = mysqli_insert_id($koneksi);
            $order_ids_dibuat[] = $id_order_baru;

            foreach ($items_penjual_ini as $item) {
                $id_menu = (int)$item['id_menu'];
                $jumlah = (int)$item['qty'];
                $harga_satuan = (int)$item['harga'];

                $sql_detail = "INSERT INTO order_details (id_order, id_menu, jumlah, harga_satuan)
                               VALUES ($id_order_baru, $id_menu, $jumlah, $harga_satuan)";

                if (!mysqli_query($koneksi, $sql_detail)) {
                    throw new Exception("Gagal menyimpan rincian makanan: " . mysqli_error($koneksi));
                }
            }
        }

        // 3. KURANGI SALDO USER DI DATABASE (dipotong sekali untuk total keseluruhan)
        $sql_update_saldo = "UPDATE users SET saldo = saldo - $total_harga WHERE id = $id_mahasiswa";
        if (!mysqli_query($koneksi, $sql_update_saldo)) {
            throw new Exception("Gagal memperbarui saldo: " . mysqli_error($koneksi));
        }

        mysqli_commit($koneksi);

        ob_end_clean();
        echo json_encode([
            "status" => "success",
            "message" => "Pesanan berhasil disimpan di database!",
            "order_ids" => $order_ids_dibuat,
            "saldo_sisa" => $saldo_sekarang - $total_harga
        ]);

    } catch (Exception $e) {
        mysqli_rollback($koneksi);

        ob_end_clean();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Keranjang belanja Anda kosong atau format data tidak valid."]);
}
?>