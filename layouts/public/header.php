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
</head>
<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white p-4 shadow-md sticky top-0 z-10">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold">Warung Kak Su</a>
            <a href="keranjang.php" class="relative">
                <i class="fas fa-shopping-cart text-2xl"></i>
                <?php if ($jumlah_item_di_keranjang > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                        <?php echo $jumlah_item_di_keranjang; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <div class="container mx-auto p-4">