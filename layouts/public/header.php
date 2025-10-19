<?php
// config/db.php sudah otomatis session_start()
require 'config/db.php'; 

// Hitung jumlah item di keranjang
$jumlah_item_di_keranjang = 0;
if (isset($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        $jumlah_item_di_keranjang += $item['jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Kak Su</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .menu-hover:hover { transform: scale(1.05); transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gradient-to-b from-orange-50 to-white">
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-2 group">
                        <i class="fas fa-utensils text-orange-500 text-2xl group-hover:rotate-12 transition-all"></i>
                        <span class="text-2xl font-bold bg-gradient-to-r from-orange-500 to-red-600 bg-clip-text text-transparent">
                            Warung Kak Su
                        </span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="menu-hover hidden md:block text-gray-600 hover:text-orange-500 transition-colors">
                        <i class="fas fa-home mr-1"></i> Beranda
                    </a>
                    <a href="keranjang.php" class="relative menu-hover p-2 bg-orange-50 rounded-full hover:bg-orange-100">
                        <i class="fas fa-shopping-basket text-2xl text-orange-500"></i>
                        <?php if ($jumlah_item_di_keranjang > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center border-2 border-white animate-pulse">
                                <?php echo $jumlah_item_di_keranjang; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>


    <div class="container mx-auto p-4">