<?php
session_start();
include '../config/koneksi.php';

$id_menu = $_GET['id_menu'];

$menu = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
    SELECT nama_menu
    FROM menus
    WHERE id='$id_menu'
    ")
);

$query = mysqli_query($koneksi,"
SELECT
    ratings.*,
    users.nama
FROM ratings
JOIN users
ON ratings.id_mahasiswa = users.id
WHERE ratings.id_menu='$id_menu'
ORDER BY ratings.id DESC
");

$total = mysqli_num_rows($query);

$rata = mysqli_fetch_assoc(
    mysqli_query($koneksi,"
    SELECT ROUND(AVG(rating),1) as rata
    FROM ratings
    WHERE id_menu='$id_menu'
    ")
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ulasan Menu</title>
    <link rel="stylesheet" href="../assets/css/mahasiswa.css">
</head>
<body>

<div class="review-page">

   <h2 class="modal-title">
    Ulasan Produk:
    <?= htmlspecialchars($menu['nama_menu']); ?>
</h2>

    <?php if(mysqli_num_rows($query) > 0): ?>

        <?php while($row = mysqli_fetch_assoc($query)): ?>

       <div class="review-card-modern">

    <div class="avatar-circle">
        <?= strtoupper(substr($row['nama'],0,1)); ?>
    </div>

    <div class="review-content">

        <div class="review-user">
            <?= htmlspecialchars($row['nama']); ?>
        </div>

        <div class="review-stars">
            <?= str_repeat("⭐",$row['rating']); ?>
        </div>

        <div class="review-comment">
            <?= nl2br(htmlspecialchars($row['ulasan'])); ?>
        </div>

    </div>

</div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="review-empty">
            Belum ada ulasan untuk menu ini.
        </div>

    <?php endif; ?>

    <!-- <a href="index.php" class="btn-back-review">
        ← Kembali ke Menu
    </a> -->

</div>

</body>
</html>