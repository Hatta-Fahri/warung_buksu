<?php
// 1. PANGGIL KONEKSI & SESSION DULU
// File config/db.php akan otomatis menjalankan session_start()
// Kita panggil berdasarkan path relatif dari file admin (cth: dashboard.php)
require_once '../config/db.php';

// 2. SETELAH SESSION DIMULAI, KITA CEK LOGIN
// Cek apakah user sudah login, jika belum, lempar ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Path relatif dari file admin
    exit;
}
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - dapoer bunasya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-gray-100">

    <aside id="sidebar"
        class="bg-white text-gray-700 w-64 h-screen fixed top-0 left-0 z-40
                  overflow-y-auto transition-transform duration-300 ease-in-out
                  -translate-x-full md:translate-x-0 border-r border-gray-200 shadow-sm">

        <div class="p-4 border-b border-gray-200">
            <a href="dashboard.php" class="text-2xl font-bold text-orange-600">dapoer bunasya</a>
        </div>

        <nav class="mt-4 p-2">
            <ul class="flex flex-col space-y-2">

                <li>
                    <?php
                    $isActive = ($current_page == 'dashboard.php');
                    $classes = $isActive ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600';
                    $iconClasses = $isActive ? 'text-orange-600' : 'text-orange-500';
                    ?>
                    <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $classes; ?>">
                        <i class="fas fa-chart-pie w-6 text-center mr-2 <?php echo $iconClasses; ?>"></i>
                        Dashboard
                    </a>
                </li>

                <li>
                    <?php
                    // Jika halaman saat ini adalah 'menu.php', 'menu_tambah.php', dll.
                    // (Berdasarkan kode kita, hanya 'menu.php' yang relevan)
                    $isActive = ($current_page == 'menu.php');
                    $classes = $isActive ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600';
                    $iconClasses = $isActive ? 'text-orange-600' : 'text-orange-500';
                    ?>
                    <a href="menu.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $classes; ?>">
                        <i class="fas fa-book-open w-6 text-center mr-2 <?php echo $iconClasses; ?>"></i>
                        Kelola Menu
                    </a>
                </li>

                <li>
                    <?php
                    // Menjadi aktif jika di 'verifikasi.php' ATAU 'verifikasi_detail.php'
                    $isActive = ($current_page == 'verifikasi.php' || $current_page == 'verifikasi_detail.php');
                    $classes = $isActive ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600';
                    $iconClasses = $isActive ? 'text-orange-600' : 'text-orange-500';
                    ?>
                    <a href="verifikasi.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $classes; ?>">
                        <i class="fas fa-check-circle w-6 text-center mr-2 <?php echo $iconClasses; ?>"></i>
                        Verifikasi Pesanan
                        <?php
                        // Notifikasi (Logika tidak berubah)
                        $q_notif = $conn->query("SELECT COUNT(id) AS total FROM pesanan WHERE status_pesanan = 'Menunggu Verifikasi'");
                        $notif = $q_notif->fetch_assoc();
                        if ($notif['total'] > 0) {
                            echo '<span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">' . $notif['total'] . '</span>';
                        }
                        ?>
                    </a>
                </li>

                <li>
                    <?php
                    $isActive = ($current_page == 'pesanan_aktif.php');
                    $classes = $isActive ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600';
                    $iconClasses = $isActive ? 'text-orange-600' : 'text-orange-500';
                    ?>
                    <a href="pesanan_aktif.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $classes; ?>">
                        <i class="fas fa-utensils w-6 text-center mr-2 <?php echo $iconClasses; ?>"></i>
                        Pesanan Aktif
                    </a>
                </li>

                <li>
                    <?php
                    $isActive = ($current_page == 'laporan.php');
                    $classes = $isActive ? 'bg-orange-100 text-orange-700 font-semibold' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600';
                    $iconClasses = $isActive ? 'text-orange-600' : 'text-orange-500';
                    ?>
                    <a href="laporan.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $classes; ?>">
                        <i class="fas fa-chart-line w-6 text-center mr-2 <?php echo $iconClasses; ?>"></i>
                        Laporan
                    </a>
                </li>
            </ul>

            <ul class="mt-8 border-t border-gray-200 pt-4">
                <li>
                    <a href="../logout.php" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-red-600 bg-red-500 transition-colors mx-2">
                        <i class="fas fa-sign-out-alt w-6 text-center mr-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <div class="md:ml-64 flex flex-col min-h-screen">

        <header class="md:hidden bg-white text-orange-600 p-4 shadow-md flex justify-between items-center sticky top-0 z-30 border-b border-gray-200">
            <button id="sidebar-toggle" class="text-2xl text-orange-600">
                <i class="fas fa-bars"></i>
            </button>
            <a href="dashboard.php" class="text-xl font-bold text-orange-600">dapoer bunasya</a>
            <div class="w-6"></div>
        </header>

        <div class="container mx-auto p-4 flex-grow">