<?php 
// Panggil header layout
include '../layouts/admin/header.php'; 
// ($conn dan $session sudah tersedia dari header)

$result = $conn->query("SELECT * FROM pesanan 
                       WHERE status_pesanan = 'Menunggu Verifikasi' 
                       ORDER BY waktu_pesan ASC");
?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
        <i class="fas fa-check-circle mr-3 text-green-500"></i>
        <span>Status pesanan berhasil diperbarui.</span>
    </div>
<?php endif; ?>

<div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-orange-100">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center mb-6">
        <i class="fas fa-check-circle text-orange-500 mr-3"></i>
        Verifikasi Pembayaran
    </h1>

    <div class="space-y-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 p-4 rounded-lg border border-gray-200 hover:border-orange-200 hover:bg-orange-50 transition-colors">
                
                <div class="flex-shrink-0 flex flex-col items-center sm:items-start text-center sm:text-left w-full sm:w-auto">
                     <span class="inline-flex items-center justify-center w-10 h-10 bg-orange-100 text-orange-600 rounded-full mb-1">
                        <i class="fas fa-receipt"></i>
                    </span>
                    <p class="font-bold text-lg text-orange-700">#<?php echo $row['id']; ?></p>
                    <p class="text-xs text-gray-500"><?php echo date('d M Y, H:i', strtotime($row['waktu_pesan'])); ?></p>
                </div>

                <div class="flex-grow text-center sm:text-left">
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></p>
                    <p class="text-sm text-gray-600">Meja: <span class="font-medium"><?php echo htmlspecialchars($row['no_meja']); ?></span></p>
                </div>

                <div class="flex-shrink-0 text-center sm:text-right px-4">
                    <p class="text-sm text-gray-500">Total Bayar</p>
                    <p class="font-bold text-lg text-gray-800">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></p>
                </div>

                <div class="flex-shrink-0">
                    <a href="verifikasi_detail.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors shadow-sm">
                        <i class="fas fa-search-dollar mr-2"></i> Lihat & Verifikasi
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="py-10 text-center text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                <i class="fas fa-check-double fa-3x mb-3 text-gray-400"></i>
                <p>Tidak ada pesanan baru yang perlu diverifikasi saat ini.</p>
                <p class="text-sm">Semua pembayaran sudah dicek.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$conn->close();
// Panggil footer layout
include '../layouts/admin/footer.php'; 
?>