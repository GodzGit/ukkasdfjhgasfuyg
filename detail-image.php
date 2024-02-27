<?php
session_start();
error_reporting(0);
include 'db.php';

// Fungsi untuk mendapatkan jumlah like pada suatu gambar
function getLikeCount($conn, $imageId) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS totalLikes FROM likefoto WHERE image_id = '$imageId'");
    $row = mysqli_fetch_assoc($result);
    return $row['totalLikes'];
}

// Fungsi untuk memeriksa apakah pengguna sudah menyukai gambar tertentu
function userLiked($conn, $imageId, $userId) {
    $result = mysqli_query($conn, "SELECT * FROM likefoto WHERE image_id = '$imageId' AND admin_id = '$userId'");
    return mysqli_num_rows($result) > 0;
}

// Periksa apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    $loggedIn = false; // Set status login ke false jika admin belum login
} else {
    $loggedIn = true; // Set status login ke true jika admin sudah login
}

$userId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null; // Ambil ID admin dari sesi jika tersedia

$produk = mysqli_query($conn, "SELECT * FROM tb_image WHERE image_id = '".$_GET['id']."' ");
$p = mysqli_fetch_object($produk);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $loggedIn) {
    // Memeriksa apakah tombol like ditekan
    if (isset($_POST['like'])) {
        $imageId = $_GET['id'];
        
        // Jika admin belum menyukai gambar, tambahkan like
        if (!userLiked($conn, $imageId, $userId)) {
            mysqli_query($conn, "INSERT INTO likefoto (image_id, admin_id, tanggallike) VALUES ('$imageId', '$userId', NOW())");
        } else {
            // Jika admin sudah menyukai gambar, hapus like
            mysqli_query($conn, "DELETE FROM likefoto WHERE image_id = '$imageId' AND admin_id = '$userId'");
        }
    } elseif (isset($_POST['comment'])) {
        // Handle submission komentar
        $imageId = $_GET['id'];
        $isiKomentar = mysqli_real_escape_string($conn, $_POST['comment']); // Sanitize input
        mysqli_query($conn, "INSERT INTO komentarfoto (image_id, admin_id, isikomentar, tanggalkomentar) VALUES ('$imageId', '$userId', '$isiKomentar', NOW())");
        
        // Redirect kembali ke halaman detail foto setelah berhasil mengirim komentar
        header("Location: detail-image-dashboard.php?id=$_GET[id]");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WEB Galeri Foto</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="container">
        <h1><a href="dashboard.php">WEB GALERI FOTO</a></h1>
        <ul>
            <li><a href="galeri.php">Galeri</a></li>
            <li><a href="data-image.php">Data foto</a></li>
            <li><a href="keluar.php">Logout?</a></li>
        </ul>
    </div>
</header>

<div class="section">
    <div class="container">
        <h3>Detail Foto</h3>
        <div class="box">
            <div class="col-2">
                <img src="foto/<?php echo $p->image ?>" width="100%" />
            </div>
            <div class="col-2">
                <h3><?php echo $p->image_name ?><br />Album : <?php echo $p->category_name  ?></h3>
                <h4>Nama User : <?php echo $p->admin_name ?><br />
                Upload Pada Tanggal : <?php echo $p->date_created  ?></h4>
                <p>Deskripsi :<br />
                    <?php echo $p->image_description ?>
                </p>
            </div>
            <div class="col-2">
                <!-- Tombol Like -->
                <?php if ($loggedIn): ?>
                <form method="post">
                    <button type="submit" name="like" class="button-like">
                        <?php
                        // Tampilkan jumlah like dan periksa apakah admin sudah menyukai gambar
                        $likeCount = getLikeCount($conn, $_GET['id']);
                        $likedByUser = userLiked($conn, $_GET['id'], $userId);

                        // Jika admin sudah menyukai gambar, tampilkan tombol unlike
                        if ($likedByUser) {
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">';
                            echo '<path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a10 10 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733q.086.18.138.363c.077.27.113.567.113.856s-.036.586-.113.856c-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.2 3.2 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.8 4.8 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/>';
                            echo '</svg>';
                        } else {
                            // Jika admin belum menyukai gambar, tampilkan tombol like
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-up" viewBox="0 0 16 16">';
                            echo '<path d="M8.864.046C7.908-.193 7.02.53 6.956 1.466c-.072 1.051-.23 2.016-.428 2.59-.125.36-.479 1.013-1.04 1.639-.557.623-1.282 1.178-2.131 1.41C2.685 7.288 2 7.87 2 8.72v4.001c0 .845.682 1.464 1.448 1.545 1.07.114 1.564.415 2.068.723l.048.03c.272.165.578.348.97.484.397.136.861.217 1.466.217h3.5c.937 0 1.599-.477 1.934-1.064a1.86 1.86 0 0 0 .254-.912c0-.152-.023-.312-.077-.464.201-.263.38-.578.488-.901.11-.33.172-.762.004-1.149c.069-.13.12-.269.159-.403.077-.27.113-.568.113-.857 0-.288-.036-.585-.113-.856a2 2 0 0 0-.138-.362 1.9 1.9 0 0 0 .234-1.734c-.206-.592-.682-1.1-1.2-1.272-.847-.282-1.803-.276-2.516-.211a10 10 0 0 0-.443.05 9.4 9.4 0 0 0-.062-4.509A1.38 1.38 0 0 0 9.125.111zM11.5 14.721H8c-.51 0-.863-.069-1.14-.164-.281-.097-.506-.228-.776-.393l-.04-.024c-.555-.339-1.198-.731-2.49-.868-.333-.036-.554-.29-.554-.55V8.72c0-.254.226-.543.62-.65 1.095-.3 1.977-.996 2.614-1.708.635-.71 1.064-1.475 1.238-1.978.243-.7.407-1.768.482-2.85.025-.362.36-.594.667-.518l.262.066c.16.04.258.143.288.255a8.34 8.34 0 0 1-.145 4.725.5.5 0 0 0 .595.644l.003-.001.014-.003.058-.014a9 9 0 0 1 1.036-.157c.663-.06 1.457-.054 2.11.164.175.058.45.3.57.65.107.308.087.67-.266 1.022l-.353.353.353.354c.043.043.105.141.154.315.048.167.075.37.075.581 0 .212-.027.414-.075.582-.05.174-.111.272-.154.315l-.353.353.353.354c.047.047.109.177.005.488a2.2 2.2 0 0 1-.505.805l-.353.353.353.354c.006.005.041.05.041.17a.9.9 0 0 1-.121.416c-.165.288-.503.56-1.066.56z"/>';
                            echo '</svg>';
                        }

                        echo " ($likeCount)";
                        ?>
                    </button>
                </form>
                <?php else: ?>
                <p>Anda harus <a href="login.php">login</a> untuk memberikan like atau komentar.</p>
                <?php endif; ?>

                <!-- Form Komentar -->
                <?php if ($loggedIn): ?>
                <form method="post" class="form-comment">
                    <textarea name="comment" placeholder="Tambahkan komentar" required></textarea>
                    <button type="submit">Kirim Komentar</button>
                </form>
                <?php endif; ?>
            </div>

            <div class="col-2">
                <h3>Komentar</h3>
                <?php
                // Query untuk mengambil komentar dari database
                $komentarQuery = mysqli_query($conn, "SELECT * FROM komentarfoto WHERE image_id = '".$_GET['id']."' ORDER BY tanggalkomentar DESC");

                // Periksa apakah ada komentar yang ditemukan
                if (mysqli_num_rows($komentarQuery) > 0) {
                    // Loop untuk menampilkan setiap komentar
                    while ($komentar = mysqli_fetch_assoc($komentarQuery)) {
                        echo "<p><strong>" . $komentar['admin_id'] . "</strong>: " . $komentar['isikomentar'] . "</p>";
                    }
                } else {
                    echo "<p>Belum ada komentar.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <small>Copyright &copy; 2024 - Web Galeri Foto.</small>
    </div>
</footer>

</body>
</html>
