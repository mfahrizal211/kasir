<?php
// Koneksi database
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbnama = 'kelontong';
$conn = new mysqli($host, $user, $password, $dbnama);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID barang dari URL
$id = $_GET['id'];

// Menangani pembaruan data barang
if (isset($_POST['update_barang'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $gambar_lama = $_POST['gambar_lama'];
    $gambar_baru = $gambar_lama;

    // Proses upload gambar jika ada gambar baru yang diunggah
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        // Direktori upload
        $target_dir = "uploads/";
        
        // Hapus gambar lama jika ada gambar baru diunggah
        if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
            unlink($target_dir . $gambar_lama);
        }

        // Nama file gambar baru
        $gambar_baru = basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $gambar_baru;

        // Pastikan direktori upload ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Pindahkan file gambar yang diunggah
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    }

    // Update data barang di database
    $query = "UPDATE produk SET nama='$nama', harga=$harga, gambar='$gambar_baru' WHERE id=$id";
    $conn->query($query);
    header("Location: admin_barang.php"); // Redirect ke halaman daftar barang
}

// Mengambil data barang yang akan diedit
$barang = $conn->query("SELECT * FROM produk WHERE id = $id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang</title>
</head>
<body>

<h3>Edit Data Barang</h3>
<form method="post" enctype="multipart/form-data">
    <label>Nama Barang:</label><br>
    <input type="text" name="nama" value="<?= $barang['nama']; ?>" required><br><br>
    
    <label>Harga Barang:</label><br>
    <input type="number" name="harga" value="<?= $barang['harga']; ?>" required><br><br>
    
    <label>Gambar Barang:</label><br>
    <?php if ($barang['gambar']): ?>
        <img src="uploads/<?= $barang['gambar']; ?>" alt="<?= $barang['nama']; ?>" width="100"><br><br>
    <?php else: ?>
        Tidak ada gambar<br><br>
    <?php endif; ?>
    <input type="file" name="gambar" accept="image/*"><br><br>
    
    <!-- Menyimpan nama gambar lama sebagai referensi jika gambar baru tidak diunggah -->
    <input type="hidden" name="gambar_lama" value="<?= $barang['gambar']; ?>">

    <button type="submit" name="update_barang">Update</button>
</form>
<a href="admin_barang.php">Kembali ke Daftar Barang</a>

</body>
</html>

<?php $conn->close(); ?>
