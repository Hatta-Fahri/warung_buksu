<?php
// config/db.php akan otomatis memulai session
require 'config/db.php'; 

$error = '';

// Tentukan direktori target
$target_dir = "uploads/";

// Cek apakah form di-submit
if (isset($_POST['upload']) && isset($_POST['pesanan_id'])) {
    
    $pesanan_id = $_POST['pesanan_id'];
    
    // Cek apakah ada file yang di-upload dan tidak ada error
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        
        $file = $_FILES['bukti_pembayaran'];
        $file_name = basename($file['name']);
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        // Dapatkan ekstensi file (jpg, png, dll)
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Tentukan ekstensi yang diizinkan
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        // 1. Validasi Ekstensi
        if (in_array($file_ext, $allowed_extensions)) {
            
            // 2. Validasi Ukuran File (misal: maks 2MB)
            if ($file_size < 2000000) { // 2,000,000 bytes = 2MB
                
                // 3. Buat Nama File Baru yang Unik
                // Ini sangat penting untuk menghindari file tertimpa
                // Format: pesanan_[id_pesanan]_[timestamp].ext
                $new_file_name = "pesanan_" . $pesanan_id . "_" . time() . "." . $file_ext;
                $target_file = $target_dir . $new_file_name;

                // 4. Pindahkan file dari temp ke folder 'uploads/'
                if (move_uploaded_file($file_tmp, $target_file)) {
                    
                    // 5. Update Database
                    // Ubah status dan simpan nama filenya
                    $stmt = $conn->prepare("UPDATE pesanan SET bukti_pembayaran = ?, status_pesanan = 'Menunggu Verifikasi' WHERE id = ?");
                    $stmt->bind_param("si", $new_file_name, $pesanan_id);
                    
                    if ($stmt->execute()) {
                        // 6. Redirect ke halaman status (akan kita buat di Tahap 7)
                        // Kirim notifikasi sukses
                        header("Location: status_pesanan.php?pesanan_id=" . $pesanan_id . "&upload=sukses");
                        exit;
                    } else {
                        $error = "Gagal memperbarui database.";
                    }
                } else {
                    $error = "Gagal memindahkan file yang di-upload.";
                }
            } else {
                $error = "Ukuran file terlalu besar (Maksimum 2MB).";
            }
        } else {
            $error = "Format file tidak diizinkan (Hanya JPG, JPEG, PNG).";
        }
    } else {
        $error = "Silakan pilih file untuk di-upload.";
    }
    
    // 7. Jika ada error, kembali ke halaman pembayaran
    // Kirim pesan error via URL
    header("Location: pembayaran.php?pesanan_id=" . $pesanan_id . "&error=" . urlencode($error));
    exit;

} else {
    // Jika file diakses langsung tanpa submit
    header("Location: index.php");
    exit;
}

$conn->close();
?>