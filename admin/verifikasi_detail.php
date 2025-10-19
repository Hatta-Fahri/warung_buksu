<?php 
include '../layouts/admin/header.php'; 
// ($conn dan $session sudah tersedia dari header)

// ==========================================================
// --- BLOK LOGIKA PHP ---
// --- TIDAK ADA PERUBAHAN SAMA SEKALI DI BAGIAN INI ---
// ==========================================================
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pesanan_id'])) {
    $pesanan_id = $_POST['pesanan_id'];
    $catatan_admin = $_POST['catatan_admin'];
    if (isset($_POST['setuju'])) {
        $stmt = $conn->prepare("UPDATE pesanan SET status_pesanan = 'Diproses', catatan_admin = ? WHERE id = ?");
        $catatan_setuju = empty($catatan_admin) ? 'Pembayaran Lunas.' : $catatan_admin;
        $stmt->bind_param("si", $catatan_setuju, $pesanan_id);
        $stmt->execute();
        $stmt->close();
        header("Location: verifikasi.php?status=sukses");
        exit;
    }
    if (isset($_POST['tolak'])) {
        if (empty($catatan_admin)) {
            $error = "Catatan wajib diisi jika Anda menolak pembayaran.";
        } else {
            $stmt = $conn->prepare("UPDATE pesanan SET status_pesanan = 'Dibatalkan', catatan_admin = ? WHERE id = ?");
            $stmt->bind_param("si", $catatan_admin, $pesanan_id);
            $stmt->execute();
            $stmt->close();
            header("Location: verifikasi.php?status=sukses");
            exit;
        }
    }
}

if (!isset($_GET['id'])) {
    echo "<div class='bg-red-50 p-4 rounded-lg text-red-700 border border-red-200'>Error: ID Pesanan tidak ditemukan.</div>";
    include '../layouts/admin/footer.php'; // Pastikan path footer benar
    exit;
}
$pesanan_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ?");
$stmt->bind_param("i", $pesanan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='bg-red-50 p-4 rounded-lg text-red-700 border border-red-200'>Error: Pesanan tidak ditemukan.</div>";
    include '../layouts/admin/footer.php'; // Pastikan path footer benar
    exit;
}
$pesanan = $result->fetch_assoc();
$stmt->close();

$stmt_detail = $conn->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id = ?");
$stmt_detail->bind_param("i", $pesanan_id);
$stmt_detail->execute();
$details = $stmt_detail->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_detail->close();

// Tutup koneksi hanya jika tidak ada error di atas
if (isset($conn) && $conn->ping()) {
    // Jangan tutup koneksi di sini jika $error bisa terjadi setelahnya
    // $conn->close(); // Pindahkan ke akhir setelah semua proses DB selesai
}
// ==========================================================
// --- AKHIR BLOK LOGIKA PHP ---
// ==========================================================
?>

<div class="mb-6">
     <a href="verifikasi.php" class="inline-flex items-center text-sm text-orange-600 hover:text-orange-800 font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Verifikasi
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
        <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-orange-100">
        <h2 class="text-xl font-bold text-gray-800 flex items-center mb-6">
            <i class="fas fa-image text-orange-500 mr-3"></i>
            Bukti Pembayaran
        </h2>
        
        <div class="mb-6">
            <?php if (!empty($pesanan['bukti_pembayaran'])): ?>
                <a href="../uploads/<?php echo htmlspecialchars($pesanan['bukti_pembayaran']); ?>" target="_blank" class="block border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                    <img src="../uploads/<?php echo htmlspecialchars($pesanan['bukti_pembayaran']); ?>" 
                         alt="Bukti Pembayaran #<?php echo $pesanan_id; ?>" 
                         class="w-full h-auto object-contain max-h-96"> </a>
                <p class="text-xs text-gray-500 mt-2 text-center">Klik gambar untuk memperbesar.</p>
            <?php else: ?>
                <div class="py-10 text-center text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                    <i class="fas fa-file-invoice-dollar fa-2x mb-2 text-gray-400"></i><br>
                    Pelanggan belum meng-upload bukti pembayaran.
                </div>
            <?php endif; ?>
        </div>

        <hr class="my-6 border-gray-200">

        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tindakan Verifikasi</h3>
        <form action="verifikasi_detail.php?id=<?php echo $pesanan_id; ?>" method="POST" class="space-y-4">
            <input type="hidden" name="pesanan_id" value="<?php echo $pesanan_id; ?>">
            
            <div>
                <label for="catatan_admin" class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan <span class="text-red-600 font-normal">(Wajib diisi jika ditolak)</span>
                </label>
                <textarea id="catatan_admin" name="catatan_admin" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                          placeholder="Contoh: Bukti transfer tidak valid / nominal kurang."><?php echo htmlspecialchars($pesanan['catatan_admin'] ?? ''); ?></textarea>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-between gap-3 pt-2">
                <button type="submit" name="tolak" 
                        class="w-full sm:w-auto flex-1 px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm flex items-center justify-center"
                        onclick="return confirm('Yakin ingin MENOLAK pesanan ini? Status akan diubah menjadi Dibatalkan.');">
                    <i class="fas fa-times-circle mr-2"></i> Tolak Pembayaran
                </button>
                <button type="submit" name="setuju" 
                        class="w-full sm:w-auto flex-1 px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center"
                        onclick="return confirm('Yakin ingin MENYETUJUI pembayaran ini? Status akan diubah menjadi Diproses.');">
                    <i class="fas fa-check-circle mr-2"></i> Setujui & Proses
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-orange-100">
        <h2 class="text-xl font-bold text-gray-800 flex items-center mb-6">
             <i class="fas fa-receipt text-orange-500 mr-3"></i>
            Detail Pesanan #<?php echo $pesanan_id; ?>
        </h2>
        
        <div class="mb-6 space-y-2 border-b border-gray-200 pb-4">
            <p class="flex justify-between text-sm"><span class="text-gray-500">Nama Pelanggan:</span> <strong class="text-gray-800"><?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></strong></p>
            <p class="flex justify-between text-sm"><span class="text-gray-500">Nomor Meja:</span> <strong class="text-gray-800"><?php echo htmlspecialchars($pesanan['no_meja']); ?></strong></p>
            <p class="flex justify-between text-sm"><span class="text-gray-500">Waktu Pesan:</span> <strong class="text-gray-800"><?php echo date('d M Y, H:i', strtotime($pesanan['waktu_pesan'])); ?></strong></p>
             <p class="flex justify-between text-sm"><span class="text-gray-500">Status Saat Ini:</span> 
                 <span class="font-semibold text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded text-xs">
                    <?php echo htmlspecialchars($pesanan['status_pesanan']); ?>
                 </span>
            </p>
        </div>

        <h3 class="text-lg font-semibold text-gray-800 mb-4">Item yang Dipesan:</h3>
        <div class="space-y-3 mb-6">
            <?php foreach ($details as $item): ?>
            <div class="flex justify-between items-center text-sm border-b border-dashed pb-2 last:border-b-0">
                <div>
                    <p class="text-gray-800"><?php echo htmlspecialchars($item['nama_menu_saat_pesan']); ?></p>
                    <p class="text-xs text-gray-500">
                        Rp <?php echo number_format($item['harga_saat_pesan'], 0, ',', '.'); ?> x <?php echo $item['jumlah']; ?>
                    </p>
                </div>
                <span class="font-medium text-gray-800">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="flex justify-between items-center text-xl font-bold text-gray-800 border-t border-gray-200 pt-4">
            <span>Total Bayar</span>
            <span class="text-orange-600">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></span>
        </div>
    </div>
</div>

<?php 
// Pindahkan penutupan koneksi ke paling akhir
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
include '../layouts/admin/footer.php'; 
?>