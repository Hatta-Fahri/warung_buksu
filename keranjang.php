<?php
session_start();
// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
$keranjang = $_SESSION['keranjang'];
$total_harga = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Anda - Warung Kak Su</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .menu-hover:hover { transform: scale(1.02); transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gradient-to-b from-orange-50 to-white min-h-screen">
    <!-- Include the same header as before -->
    <?php include 'layouts/public/header.php'; ?>

    <div class="container mx-auto p-4">
        <h2 class="text-3xl font-bold mb-6 mt-4 text-gray-800">
            <i class="fas fa-shopping-basket text-orange-500 mr-2"></i>
            Keranjang Pesanan Anda
        </h2>
        
        <?php if (empty($keranjang)): ?>
            <div class="bg-white border-2 border-orange-100 rounded-lg p-8 text-center shadow-lg">
                <p class="text-gray-600 mb-4">Keranjang Anda masih kosong.</p>
                <a href="index.php" class="inline-flex items-center px-6 py-3 bg-orange-500 text-white font-medium rounded-full hover:bg-orange-600 transition-colors">
                    <i class="fas fa-utensils mr-2"></i>
                    Mulai Memesan
                </a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-orange-100">
                <table class="min-w-full">
                    <thead class="bg-orange-50">
                        <tr>
                            <th class="py-4 px-6 text-left text-gray-700">Menu</th>
                            <th class="py-4 px-6 text-left text-gray-700">Harga</th>
                            <th class="py-4 px-6 text-center text-gray-700">Jumlah</th>
                            <th class="py-4 px-6 text-right text-gray-700">Subtotal</th>
                            <th class="py-4 px-6 text-center text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keranjang as $id => $item): ?>
                        <?php 
                            $subtotal = $item['harga'] * $item['jumlah'];
                            $total_harga += $subtotal;
                        ?>
                        <tr class="border-b border-orange-50 hover:bg-orange-50/30 transition-colors">
                            <td class="py-4 px-6 font-medium text-gray-800"><?php echo htmlspecialchars($item['nama']); ?></td>
                            <td class="py-4 px-6 text-gray-600">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td class="py-4 px-6">
                                <form action="cart_action.php" method="POST" class="flex justify-center">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="menu_id" value="<?php echo $id; ?>">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="decrementQuantity(this)" class="w-8 h-8 rounded-full bg-orange-100 hover:bg-orange-200 text-orange-500">-</button>
                                        <input type="number" name="jumlah" value="<?php echo $item['jumlah']; ?>" min="0" 
                                               class="w-16 px-2 py-1 border border-orange-200 rounded-lg shadow-sm text-center focus:ring-2 focus:ring-orange-300 focus:outline-none" 
                                               onchange="this.form.submit()">
                                        <button type="button" onclick="incrementQuantity(this)" class="w-8 h-8 rounded-full bg-orange-100 hover:bg-orange-200 text-orange-500">+</button>
                                    </div>
                                </form>
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-gray-800">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                            <td class="py-4 px-6 text-center">
                                <form action="cart_action.php" method="POST">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="menu_id" value="<?php echo $id; ?>">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-red-100 hover:bg-red-200 text-red-500 transition-colors">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-orange-50">
                        <tr>
                            <td colspan="3" class="py-4 px-6 text-right font-bold text-lg text-gray-800">Total</td>
                            <td class="py-4 px-6 text-right font-bold text-lg text-orange-600">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-between mt-8">
                <a href="index.php" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-full hover:bg-gray-200 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Tambah Pesanan
                </a>
                <a href="checkout.php" class="inline-flex items-center px-6 py-3 bg-green-500 text-white font-medium rounded-full hover:bg-green-600 transition-colors">
                    Lanjut ke Pembayaran
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function incrementQuantity(btn) {
            const input = btn.previousElementSibling;
            input.value = parseInt(input.value) + 1;
            input.form.submit();
        }

        function decrementQuantity(btn) {
            const input = btn.nextElementSibling;
            if (parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
                input.form.submit();
            }
        }
    </script>
</body>
</html>