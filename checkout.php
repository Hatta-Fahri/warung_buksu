<?php
session_start();
// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
$keranjang = $_SESSION['keranjang'];

// JIKA KERANJANG KOSONG, tidak bisa checkout, lempar ke index.php
if (empty($keranjang)) {
    header("Location: index.php");
    exit;
}

// Hitung total harga untuk ditampilkan di ringkasan
$total_harga = 0;
foreach ($keranjang as $id => $item) {
    $total_harga += $item['harga'] * $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - dapoer bunasya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .input-focus:focus {
            transform: scale(1.01);
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-orange-50 to-white min-h-screen">
    <?php include 'layouts/public/header.php'; ?>

    <div class="container mx-auto p-4">
        <h2 class="text-3xl font-bold mb-6 mt-4 text-gray-800">
            <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>
            Formulir Pemesanan
        </h2>

        <div class="flex flex-col md:flex-row gap-8">
            <div class="w-full md:w-1/2 bg-white p-8 rounded-xl shadow-lg border border-orange-100">
                <h3 class="text-xl font-semibold mb-6 text-gray-800">
                    <i class="fas fa-user-edit text-orange-500 mr-2"></i>
                    Lengkapi Data Anda
                </h3>

                <form action="proses_checkout.php" method="POST">
                    <div class="mb-6">
                        <label for="nama_pelanggan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-orange-400 mr-2"></i>Nama Anda
                        </label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" required
                            class="w-full px-4 py-3 rounded-lg border border-orange-200 shadow-sm 
                                      focus:ring-2 focus:ring-orange-300 focus:border-orange-300 
                                      focus:outline-none input-focus">
                    </div>

                    <div class="mb-8">
                        <label for="no_meja" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-chair text-orange-400 mr-2"></i>Nomor Meja
                        </label>
                        <input type="text" id="no_meja" name="no_meja" required
                            placeholder="Contoh: 05"
                            class="w-full px-4 py-3 rounded-lg border border-orange-200 shadow-sm 
                                      focus:ring-2 focus:ring-orange-300 focus:border-orange-300 
                                      focus:outline-none input-focus">
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="keranjang.php"
                            class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 
                                  font-medium rounded-full hover:bg-gray-200 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </a>
                        <button type="submit" name="checkout"
                            class="inline-flex items-center px-6 py-3 bg-green-500 text-white 
                                       font-medium rounded-full hover:bg-green-600 transition-colors">
                            <i class="fas fa-check-circle mr-2"></i>
                            Buat Pesanan
                        </button>
                    </div>
                </form>
            </div>

            <div class="w-full md:w-1/2 bg-white p-8 rounded-xl shadow-lg border border-orange-100">
                <h3 class="text-xl font-semibold mb-6 text-gray-800">
                    <i class="fas fa-receipt text-orange-500 mr-2"></i>
                    Ringkasan Pesanan
                </h3>
                <div class="space-y-4">
                    <?php foreach ($keranjang as $item): ?>
                        <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-utensils text-orange-400 mr-3"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($item['nama']); ?>
                                    <span class="text-orange-500 font-medium">(x<?php echo $item['jumlah']; ?>)</span>
                                </span>
                            </div>
                            <span class="text-gray-900 font-medium">
                                Rp <?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 pt-6 border-t-2 border-orange-100">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span class="text-gray-800">Total Pembayaran</span>
                        <span class="text-orange-500">
                            Rp <?php echo number_format($total_harga, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>

</html>