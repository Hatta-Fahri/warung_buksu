<?php
// 1. PANGGIL KONEKSI & SESSION DULU
// File config/db.php akan otomatis menjalankan session_start()
// Kita gunakan require_once agar aman jika dipanggil berkali-kali
require_once '../config/db.php';

// 2. SETELAH SESSION DIMULAI OLEH DB.PHP, BARU KITA CEK LOGIN
// Cek apakah user sudah login, jika belum, lempar ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Warung Kak Su</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Style kustom untuk scrollbar di sidebar (opsional, tapi rapi) */
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-100">

    <aside id="sidebar" 
           class="bg-blue-800 text-white w-64 h-screen fixed top-0 left-0 z-40
                  overflow-y-auto transition-transform duration-300 ease-in-out
                  -translate-x-full md:translate-x-0">
        
        <div class="p-4 border-b border-blue-700">
            <a href="dashboard.php" class="text-2xl font-bold">Warung Kak Su</a>
        </div>
        
        <nav class="mt-4">
            <ul class="flex flex-col space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center px-4 py-3 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-chart-pie w-6 text-center mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="menu.php" class="flex items-center px-4 py-3 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-book-open w-6 text-center mr-2"></i>
                        Kelola Menu
                    </a>
                </li>
                <li>
                    <a href="verifikasi.php" class="flex items-center px-4 py-3 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check-circle w-6 text-center mr-2"></i>
                        Verifikasi
                        <?php
                        // 3. $conn PASTI SUDAH ADA, kita tidak perlu cek if(!isset($conn))
                        // Notifikasi pesanan baru
                        $q_notif = $conn->query("SELECT COUNT(id) AS total FROM pesanan WHERE status_pesanan = 'Menunggu Verifikasi'");
                        $notif = $q_notif->fetch_assoc();
                        if ($notif['total'] > 0) {
                            echo '<span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">' . $notif['total'] . '</span>';
                        }
                        ?>
                    </a>
                </li>
                <li>
                    <a href="pesanan_aktif.php" class="flex items-center px-4 py-3 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-utensils w-6 text-center mr-2"></i>
                        Pesanan Aktif
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="flex items-center px-4 py-3 rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-chart-line w-6 text-center mr-2"></i>
                        Laporan
                    </a>
                </li>
            </ul>
            
            <ul class="mt-8 border-t border-blue-700 pt-4">
                 <li>
                    <a href="../logout.php" class="flex items-center px-4 py-3 rounded hover:bg-red-500 bg-red-600 transition-colors mx-2">
                        <i class="fas fa-sign-out-alt w-6 text-center mr-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <div class="md:ml-64 flex flex-col min-h-screen">
    
        <header class="md:hidden bg-blue-800 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-30">
            <button id="sidebar-toggle" class="text-2xl">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="text-xl font-bold">Warung Kak Su</a>
            <div class="w-6"></div> 
        </header>

        <div class="container mx-auto p-4 flex-grow">
            ```

---

File `admin/footer.php` dan logikanya sudah benar, jadi **tidak perlu diubah**.

Dengan perbaikan di `header.php` ini, error PHP-nya akan hilang dan link navigasi Anda akan berfungsi dengan normal.