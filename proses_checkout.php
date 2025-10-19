<?php
// config/db.php akan otomatis memulai session
require 'config/db.php'; 

// Cek apakah tombol checkout ditekan DAN keranjang tidak kosong
if (isset($_POST['checkout']) && !empty($_SESSION['keranjang'])) {

    // 1. Ambil data dari form checkout.php
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $no_meja = $_POST['no_meja'];

    // 2. Hitung total harga dari session
    $total_harga = 0;
    foreach ($_SESSION['keranjang'] as $item) {
        $total_harga += $item['harga'] * $item['jumlah'];
    }

    // 3. Mulai Transaksi Database
    // Ini PENTING agar data pasti masuk ke 2 tabel (pesanan & detail)
    // atau tidak sama sekali jika ada error.
    $conn->begin_transaction();

    try {
        // 4. Simpan ke tabel 'pesanan'
        // Status otomatis 'Menunggu Pembayaran' sesuai default tabel
        $stmt_pesanan = $conn->prepare("INSERT INTO pesanan (nama_pelanggan, no_meja, total_harga) VALUES (?, ?, ?)");
        $stmt_pesanan->bind_param("ssd", $nama_pelanggan, $no_meja, $total_harga);
        $stmt_pesanan->execute();
        
        // 5. Ambil ID pesanan yang baru saja dibuat
        $pesanan_id = $conn->insert_id;
        
        // 6. Siapkan statement untuk tabel 'detail_pesanan'
        $stmt_detail = $conn->prepare("INSERT INTO detail_pesanan (pesanan_id, menu_id, nama_menu_saat_pesan, harga_saat_pesan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)");

        // 7. Looping keranjang dan simpan satu per satu ke 'detail_pesanan'
        foreach ($_SESSION['keranjang'] as $menu_id => $item) {
            $subtotal = $item['harga'] * $item['jumlah'];
            $stmt_detail->bind_param(
                "iisdis", // Tipe data (integer, integer, string, double, integer, string)
                $pesanan_id,
                $menu_id,
                $item['nama'],
                $item['harga'],
                $item['jumlah'],
                $subtotal
            );
            $stmt_detail->execute();
        }

        // 8. Jika semua berhasil, commit transaksi
        $conn->commit();

        // 9. Kosongkan keranjang belanja
        unset($_SESSION['keranjang']);

        // 10. Redirect pelanggan ke Halaman Pembayaran
        // Kita kirim ID pesanannya via URL
        header("Location: pembayaran.php?pesanan_id=" . $pesanan_id);
        exit;

    } catch (Exception $e) {
        // 11. Jika terjadi error, batalkan semua (rollback)
        $conn->rollback();
        
        // Redirect kembali ke keranjang dengan pesan error
        header("Location: keranjang.php?status=gagal_checkout");
        exit;
    }

    $stmt_pesanan->close();
    $stmt_detail->close();

} else {
    // Jika user mengakses file ini secara langsung atau keranjang kosong
    header("Location: index.php");
    exit;
}

$conn->close();
?>