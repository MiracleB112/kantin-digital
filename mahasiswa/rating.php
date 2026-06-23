<?php
session_start();
include '../config/koneksi.php';

$id_order = $_GET['id_order'];

$query = mysqli_query($koneksi, "
SELECT od.*, m.nama_menu
FROM order_details od
JOIN menus m ON od.id_menu = m.id
WHERE od.id_order = '$id_order'
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Beri Rating</title>
    <link rel="stylesheet" href="../assets/css/mahasiswa.css">
</head>

<body>
    <div class="rating-container">

        <h1 class="rating-title">
            ⭐ Beri Rating Pesanan
        </h1>

        <form action="proses_rating.php" method="POST">

            <input type="hidden" name="id_order" value="<?= $id_order; ?>">

            <?php while ($menu = mysqli_fetch_assoc($query)): ?>

                <div class="rating-card">

                    <h3 class="menu-name">
                        🍽️ <?= htmlspecialchars($menu['nama_menu']); ?>
                    </h3>

                    <input type="hidden" name="id_menu[]" value="<?= $menu['id_menu']; ?>">

                    <label class="rating-label">
                        Berikan Penilaian
                    </label>

                    <div class="star-rating">

                        <input type="radio" id="star5<?= $menu['id_menu']; ?>" name="rating[<?= $menu['id_menu']; ?>]"
                            value="5">
                        <label for="star5<?= $menu['id_menu']; ?>">★</label>

                        <input type="radio" id="star4<?= $menu['id_menu']; ?>" name="rating[<?= $menu['id_menu']; ?>]"
                            value="4">
                        <label for="star4<?= $menu['id_menu']; ?>">★</label>

                        <input type="radio" id="star3<?= $menu['id_menu']; ?>" name="rating[<?= $menu['id_menu']; ?>]"
                            value="3">
                        <label for="star3<?= $menu['id_menu']; ?>">★</label>

                        <input type="radio" id="star2<?= $menu['id_menu']; ?>" name="rating[<?= $menu['id_menu']; ?>]"
                            value="2">
                        <label for="star2<?= $menu['id_menu']; ?>">★</label>

                        <input type="radio" id="star1<?= $menu['id_menu']; ?>" name="rating[<?= $menu['id_menu']; ?>]"
                            value="1">
                        <label for="star1<?= $menu['id_menu']; ?>">★</label>

                    </div>
                   <textarea
name="ulasan[]"
rows="4"
placeholder="Ceritakan pengalamanmu menikmati menu ini...">
</textarea>

                </div>

            <?php endwhile; ?>

            <button type="submit" class="btn-ratingg">
                💾 Simpan Rating
            </button>

        </form>

    </div>


</body>

</html>