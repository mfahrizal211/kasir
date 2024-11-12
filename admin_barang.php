<?php
// Memulai sesi dan koneksi ke database
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbnama = 'kelontong';

// Koneksi ke MySQL
$conn = new mysqli($host, $user, $password, $dbnama);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Menangani penambahan data barang
if (isset($_POST['add_barang'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    
    // Proses upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $gambar = basename($_FILES['gambar']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . $gambar;
        
        // Memindahkan file yang diunggah ke direktori uploads
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada
        }
        
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    }

    // Menyimpan data barang ke database
    $query = "INSERT INTO produk (nama, harga, gambar) VALUES ('$nama', $harga, '$gambar')";
    $conn->query($query);
    header("Location: admin_barang.php"); // Refresh halaman
}

// Menangani penghapusan data barang
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Hapus gambar fisik dari direktori
    $result = $conn->query("SELECT gambar FROM produk WHERE id = $id");
    $row = $result->fetch_assoc();
    if ($row && $row['gambar'] && file_exists("uploads/" . $row['gambar'])) {
        unlink("uploads/" . $row['gambar']);
    }

    // Hapus data barang dari database
    $query = "DELETE FROM produk WHERE id = $id";
    $conn->query($query);
    header("Location: admin_barang.php");
}

// Menangani pembaruan data barang
if (isset($_POST['edit_barang'])) {
    $id = $_POST['edit_id'];
    $nama = $_POST['edit_nama'];
    $harga = $_POST['edit_harga'];
    $gambar_lama = $_POST['edit_gambar_lama'];
    $gambar_baru = $gambar_lama;

    // Proses upload gambar jika ada gambar baru yang diunggah
    if (isset($_FILES['edit_gambar']) && $_FILES['edit_gambar']['error'] == 0) {
        $gambar_baru = basename($_FILES['edit_gambar']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . $gambar_baru;

        // Hapus gambar lama jika ada gambar baru diunggah
        if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
            unlink($target_dir . $gambar_lama);
        }

        // Pindahkan file gambar yang diunggah
        move_uploaded_file($_FILES['edit_gambar']['tmp_name'], $target_file);
    }

    // Update data barang di database
    $query = "UPDATE produk SET nama='$nama', harga=$harga, gambar='$gambar_baru' WHERE id=$id";
    $conn->query($query);
    header("Location: admin_barang.php"); // Refresh halaman
}

// Mengambil data produk dari database
$produk = $conn->query("SELECT * FROM produk");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang - Admin</title>
    <style>
        /* Styling dasar */
        body { font-family: Arial, sans-serif; background-color:#e8f1ff; }
        .container { width: 80%; margin: auto; }
        .header { background-color: #2f4bff; padding: 10px; text-align: center; color: white; }
        .btn { padding: 5px 10px; cursor: pointer; text-decoration: none; background-color:#ea4335; color:white; border:none; font-size: 1rem;}
        .btn-add { background-color: #34a853; color: white; }
        .btn-edit { background-color: #fbbc05; color: white; }
        .btn-delete { background-color: #ea4335; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #0c16f2; color: white; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; }
        .modal-content { background-color: white; padding: 20px; border-radius: 5px; width: 400px; }
        img { width: 50px; height: auto; }
        .btn-save {
            background-color: #34a853;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>DATA BARANG</h2>
    </div>

    <button class="btn btn-add" onclick="document.getElementById('modalAdd').style.display='flex'">+ Tambah Barang</button>

    <!-- Modal Tambah Barang -->
    <div id="modalAdd" class="modal">
        <div class="modal-content">
            <h3>Tambah Data Barang</h3>
            <form method="post" enctype="multipart/form-data">
                <label>Nama Barang:</label><br>
                <input type="text" name="nama" required><br><br>
                <label>Harga Barang:</label><br>
                <input type="number" name="harga" required><br><br>
                <label>Gambar Barang:</label><br>
                <input type="file" name="gambar" accept="image/*"><br><br>
                <button type="submit" name="add_barang" class="btn btn-add">Tambah</button>
                <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" class="btn">Batal</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Barang -->
    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <h3>Edit Data Barang</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" id="edit_id">
                <label>Nama Barang:</label><br>
                <input type="text" name="edit_nama" id="edit_nama" required><br><br>
                <label>Harga Barang:</label><br>
                <input type="number" name="edit_harga" id="edit_harga" required><br><br>
                <label>Gambar Barang:</label><br>
                <img id="edit_gambar_preview" width="100" style="display:block; margin-bottom: 10px;">
                <input type="file" name="edit_gambar" accept="image/*"><br><br>
                <input type="hidden" name="edit_gambar_lama" id="edit_gambar_lama">
                <button type="submit" name="edit_barang" class="btn btn-save">Simpan</button>
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" class="btn">Batal</button>
            </form>
        </div>
    </div>

    <!-- Tabel Data Barang -->
    <table>
        <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Harga</th>
            <th>Gambar</th>
            <th>Opsi</th>
        </tr>
        <?php $no = 1; while ($row = $produk->fetch_assoc()): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['nama']; ?></td>
                <td>Rp<?= number_format($row['harga'], 0, ',', '.'); ?></td>
                <td>
                    <?php if ($row['gambar']): ?>
                        <img src="uploads/<?= $row['gambar']; ?>" alt="<?= $row['nama']; ?>">
                    <?php else: ?>
                        Tidak ada gambar
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="openEditModal('<?= $row['id']; ?>', '<?= $row['nama']; ?>', '<?= $row['harga']; ?>', '<?= $row['gambar']; ?>')" class="btn btn-edit">Edit</button>
                    <a href="admin_barang.php?delete_id=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus barang ini?');" class="btn btn-delete">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
    // Untuk menutup modal ketika klik di luar area modal
    window.onclick = function(event) {
        var modalAdd = document.getElementById('modalAdd');
        var modalEdit = document.getElementById('modalEdit');
        if (event.target == modalAdd) {
            modalAdd.style.display = "none";
        }
        if (event.target == modalEdit) {
            modalEdit.style.display = "none";
        }
    }

    // Fungsi untuk membuka modal edit dengan data barang
    function openEditModal(id, nama, harga, gambar) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_harga').value = harga;
        document.getElementById('edit_gambar_lama').value = gambar;
        document.getElementById('edit_gambar_preview').src = gambar ? 'uploads/' + gambar : '';
        document.getElementById('modalEdit').style.display = 'flex';
    }
</script>

</body>
</html>

<?php $conn->close(); ?>
