<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../config/koneksi.php';

// Membaca kiriman data, baik format JSON maupun Form biasa dari Android
$json_data = file_get_contents('php://input');
$input = json_decode($json_data, true);

$email    = isset($input['email']) ? $input['email'] : (isset($_POST['email']) ? $_POST['email'] : null);
$password = isset($input['password']) ? $input['password'] : (isset($_POST['password']) ? $_POST['password'] : null);

// CATATAN: role TIDAK lagi diminta dari Flutter.
// Role ditentukan otomatis oleh sistem berdasarkan data di tabel users,
// bukan dipilih sendiri oleh user (prinsip access control yang benar).
if ($email && $password) {
    $email    = mysqli_real_escape_string($koneksi, $email);
    $password = mysqli_real_escape_string($koneksi, $password);

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND password='$password'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        echo json_encode([
            "status" => "success",
            "message" => "Login berhasil",
            "user" => [
                "id" => $data['id'],
                "nama" => $data['nama'],
                "role" => $data['role'] // <-- inilah yang dipakai Flutter untuk menentukan dashboard tujuan
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email atau Password salah!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>