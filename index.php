<?php
//Memulai sesi dan koneksi ke database
session_start();
$host = 'localhost';
$user = 'root';
$password = '';
$dbnama = 'kelontong';

//Koneksi ke MySql
$conn = new mysqli($host, $user, $password, $dbnama);
if($conn->connect_error){
    die("Koneksi gagal: " . $conn->connect_error);
}

//Menangani penambahan item ke keranjang
if(isset($_POST['add_to_cart'])){
    $product_id = $_POST['product_id'];
    $query = "INSERT INTO keranjang (produk_id, jumlah) VALUES ($product_id, 1)
              ON DUPLICATE KEY UPDATE jumlah = jumlah + 1"; 
    $conn->query($query);
}

//Menangani penghapusan item dari keranjang
if(isset($_POST['remove_from_cart'])){
    $cart_id = $_POST['cart_id'];
    $query = "DELETE FROM keranjang WHERE id = $cart_id";
    $conn->query($query);
}

//Menangani pembaruan jumlah item di keranjang
if(isset($_POST['update_cart'])){
    foreach($_POST['quantities'] as $cart_id => $quantity){
        $query = "UPDATE keranjang SET jumlah = $quantity WHERE id = $cart_id";
        $conn->query($query);
    }
}

//Mengambil data produk dari database
$produk = $conn->query("SELECT * FROM produk");

//Mengambil data dari item di keranjang
$cart_items = $conn->query("SELECT keranjang.id as cart_id, produk.nama, produk.harga,keranjang.jumlah
                             FROM keranjang
                             JOIN produk ON keranjang.produk_id = produk.id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelontong Tembarak</title>
    <style>
        /*Styling dasar */
        body{
            font-family: Arial, sans-serif;
            background-color: #e8f1ff;
            display:flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .left {
            width: 60%;
            padding: 20px;
            background-color: #d5e5ff;
            /*border-right: 1px solid #d5e5ff;*/
        }

        .right {
            width: 40%;
            padding: 20px;
            background-color: #d5e5ff;
        }

        /* Header */
        .header {
            background-color: #2f4bff/*#34a853 */;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #ffffff;
            margin: 0;
            font-weight: bold;
        }

        .search-bar {
            display: block;
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        /* Styling untuk produk */
        .products {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Dua kolom produk */
            gap: 20px;
        }

        .product-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .product-item img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .product-name {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 14px;
            color: #0c16ff;
            margin-bottom: 10px;
        }

        .add-to-cart-btn {
            padding: 8px 12px;
            background-color: #34a853;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .add-to-cart-btn:hover {
            background-color: #2c8f47;
        }

        /* Order Summary */
        .order-summary h2 {
            font-size: 20px;
            color: #0c16ff;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #0c16ff;
            color: white;
        }

        td input[type="number"] {
            width: 50px;
            padding: 5px;
            text-align: center;
        }

        .remove-button {
            padding: 5px 10px;
            background-color: #ea4335;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }

        .remove-button:hover {
            background-color: #d83429;
        }

        .update-cart {
            width: 100%;
            padding: 10px;
            background-color: #34a853;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .update-cart:hover {
            background-color: #2c8f47;
        }

        .empty-cart {
            font-size: 16px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left">
        <!-- Header -->
        <div class="header">
            <h1>Kelompok Sejahtera</h1>
        </div>

        <!-- Search Bar -->
        <input type="text" class="search-bar" placeholder="Search...">

        <!-- Daftar Produk -->
        <div class="products">
            <?php while ($product = $produk->fetch_assoc()): ?>
                <div class="product-item">
                    <img src="uploads/<?= $product['gambar']; ?>" alt="<?= $product['nama']; ?>">
                    <div class="product-name"><?= $product['nama']; ?></div>
                    <div class="product-price">Rp<?= number_format($product['harga'], 0, ',', '.'); ?></div>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="right">
        <h2>Pesanan Baru</h2>
        <div class="order-summary">
            <?php if ($cart_items->num_rows > 0): ?>
                <form method="post">
                    <table>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Sub Total</th>
                            <th>Action</th>
                        </tr>
                        <?php $total = 0; ?>
                        <?php while ($item = $cart_items->fetch_assoc()): ?>
                            <?php $subtotal = $item['harga'] * $item['jumlah']; ?>
                            <?php $total += $subtotal; ?>
                            <tr>
                                <td><?= $item['nama']; ?></td>
                                <td>
                                    <input type="number" name="quantities[<?= $item['cart_id']; ?>]" value="<?= $item['jumlah']; ?>" min="1">
                                </td>
                                <td>Rp<?= number_format($subtotal, 0, ',', '.'); ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                                        <button type="submit" name="remove_from_cart" class="remove-button">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <tr>
                            <td colspan="2"><strong>Total</strong></td>
                            <td colspan="2"><strong>Rp<?= number_format($total, 0, ',', '.'); ?></strong></td>
                        </tr>
                    </table>
                    <button type="submit" name="update_cart" class="update-cart">Update Cart</button>
                </form>
            <?php else: ?>
                <p class="empty-cart">No items in the cart.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>