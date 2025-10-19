<?php
/*
 * File: logout.php
 * Tugas: Menghancurkan session dan mengakhiri status login admin.
 */

// 1. Panggil file config
// Ini penting karena file config/db.php akan otomatis memulai session
// (session_start()) yang kita perlukan untuk menghancurkannya.
require 'config/db.php';

// 2. Hapus semua variabel session
// Ini akan mengosongkan $_SESSION['user_id'], $_SESSION['username'], dll.
session_unset();

// 3. Hancurkan session
// Ini akan menghapus data session dari server
session_destroy();

// 4. Alihkan (redirect) pengguna kembali ke halaman login
// Pengguna sekarang sudah tidak login.
header("Location: login.php");

// 5. Pastikan tidak ada kode lain yang dieksekusi setelah redirect
exit;

?>