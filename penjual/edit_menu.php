<?php
session_start();
include '../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
    SELECT *
    FROM menus
    WHERE id='$id'
    ")
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Menu</title>
    <link rel="stylesheet" href="../assets/css/penjual.css">
</head>
<body>
<div class="edit-container">

    <div class="edit-header">

        <h2>✏️ Edit Menu</h2>

        <p>
            Perbarui informasi menu yang tersedia di toko Anda.
        </p>

    </div>

    <div class="edit-card">

        <div class="preview-side">

            <img
            src="../assets/images/<?= $data['foto']; ?>"
            id="previewFoto">

            <label class="upload-btn">
                📷 Ganti Foto
                <input
                type="file"
                name="foto"
                id="fotoInput"
                hidden>
            </label>

        </div>

        <div class="form-side">

            <form
            action="proses_edit_menu.php"
            method="POST"
            enctype="multipart/form-data">

                <input
                type="hidden"
                name="id"
                value="<?= $data['id']; ?>">

                <label>Nama Menu</label>

                <input
                type="text"
                name="nama_menu"
                value="<?= $data['nama_menu']; ?>"
                required>

                <label>Harga Menu</label>

                <input
                type="number"
                name="harga"
                value="<?= $data['harga']; ?>"
                required>

                <label>Status</label>

                <select name="status">

                    <option
                    value="tersedia"
                    <?= $data['status']=='tersedia'?'selected':''; ?>>
                    Tersedia
                    </option>

                    <option
                    value="habis"
                    <?= $data['status']=='habis'?'selected':''; ?>>
                    Habis
                    </option>

                </select>

                <label>Deskripsi</label>

                <textarea
                name="deskripsi"
                rows="5"><?= $data['deskripsi']; ?></textarea>

                <div class="btn-group-edit">

                    <a
                    href="menu.php"
                    class="btn-cancel">
                    Batal
                    </a>

                    <button
                    type="submit"
                    class="btn-save">
                    💾 Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
</body>
</html>