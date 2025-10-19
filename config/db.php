<?php
/*
 * File: config/db.php
 * Ini adalah file konfigurasi utama untuk koneksi database dan session.
 */

// 1. Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Sesuaikan dengan username XAMPP Anda
define('DB_PASS', '');     // Sesuaikan dengan password XAMPP Anda (biasanya kosong)
define('DB_NAME', 'db_warung');

// 2. Buat Koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. Cek Koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// 4. Mulai Session
// Ini PENTING. Dengan meletakkannya di sini, setiap file yang me-require 'config/db.php'
// akan otomatis memiliki akses ke session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>