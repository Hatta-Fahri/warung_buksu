<?php
// config/db.php akan otomatis memulai session
require 'config/db.php'; 

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Periksa apakah 'action' ada
if (isset($_POST['action'])) {
    
    // ======================================================
    // 1. Aksi: TAMBAH item ke keranjang
    // ======================================================
    if ($_POST['action'] == 'tambah') {
        if (isset($_POST['menu_id']) && isset($_POST['jumlah'])) {
            $menu_id = (int)$_POST['menu_id'];
            $jumlah = (int)$_POST['jumlah'];
            
            // Ambil data menu dari DB untuk validasi (harga, nama, gambar)
            $stmt = $conn->prepare("SELECT nama_menu, gambar, harga, status FROM menu WHERE id = ?");
            $stmt->bind_param("i", $menu_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $menu = $result->fetch_assoc();
                
                // Cek apakah menu tersedia
                if ($menu['status'] == 'Tersedia' && $jumlah > 0) {
                    
                    // Cek apakah item sudah ada di keranjang
                    if (isset($_SESSION['keranjang'][$menu_id])) {
                        // Jika sudah ada, tambahkan jumlahnya
                        $_SESSION['keranjang'][$menu_id]['jumlah'] += $jumlah;
                    } else {
                        // Jika belum ada, tambahkan sebagai item baru
                        $_SESSION['keranjang'][$menu_id] = [
                            'nama' => $menu['nama_menu'],
                            'harga' => $menu['harga'],
                            'gambar' => $menu['gambar'], // Kita simpan gambar juga
                            'jumlah' => $jumlah
                        ];
                    }
                    // Redirect kembali ke index dengan notifikasi sukses
                    header("Location: index.php?status=sukses");
                    exit;
                }
            }
        }
        // Jika gagal
        header("Location: index.php?status=gagal");
        exit;
    }
    
    // ======================================================
    // 2. Aksi: UPDATE jumlah item di keranjang
    // ======================================================
    if ($_POST['action'] == 'update') {
        if (isset($_POST['menu_id']) && isset($_POST['jumlah'])) {
            $menu_id = (int)$_POST['menu_id'];
            $jumlah = (int)$_POST['jumlah'];
            
            if ($jumlah > 0) {
                // Update jumlah jika item masih ada di keranjang
                if (isset($_SESSION['keranjang'][$menu_id])) {
                    $_SESSION['keranjang'][$menu_id]['jumlah'] = $jumlah;
                }
            } else {
                // Jika jumlah 0 atau kurang, HAPUS item
                unset($_SESSION['keranjang'][$menu_id]);
            }
        }
        // Kembali ke halaman keranjang
        header("Location: keranjang.php");
        exit;
    }
    
    // ======================================================
    // 3. Aksi: HAPUS item dari keranjang
    // ======================================================
    if ($_POST['action'] == 'hapus') {
        if (isset($_POST['menu_id'])) {
            $menu_id = (int)$_POST['menu_id'];
            // Hapus item dari session keranjang
            unset($_SESSION['keranjang'][$menu_id]);
        }
        // Kembali ke halaman keranjang
        header("Location: keranjang.php");
        exit;
    }

} else {
    // Jika tidak ada aksi, kembali ke index
    header("Location: index.php");
    exit;
}

$conn->close();
?>