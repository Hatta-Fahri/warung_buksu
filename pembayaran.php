<?php 
// Panggil header layout publik
include 'layouts/public/header.php';
// ($conn dan $session sudah tersedia dari header)

// 1. Cek apakah ada 'pesanan_id' di URL
if (!isset($_GET['pesanan_id'])) {
    header("Location: index.php");
    exit;
}

$pesanan_id = (int)$_GET['pesanan_id'];

// 2. Ambil data pesanan utama
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ?");
$stmt->bind_param("i", $pesanan_id);
$stmt->execute();
$result_pesanan = $stmt->get_result();

if ($result_pesanan->num_rows === 0) {
    echo "Pesanan tidak ditemukan.";
    include 'layouts/public/footer.php';
    exit;
}
$pesanan = $result_pesanan->fetch_assoc();

// 3. Ambil data detail pesanan (menu yang dipesan)
$stmt_detail = $conn->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id = ?");
$stmt_detail->bind_param("i", $pesanan_id);
$stmt_detail->execute();
$details = $stmt_detail->get_result();

// 4. Cek status
// Jika status BUKAN 'Menunggu Pembayaran', lempar ke halaman pelacakan
if ($pesanan['status_pesanan'] != 'Menunggu Pembayaran') {
    header("Location: status_pesanan.php?pesanan_id=" . $pesanan_id);
    exit;
}
?>

<div class="container mx-auto p-4 max-w-3xl">

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <strong class="font-bold">Upload Gagal!</strong>
            <span class="block sm:inline"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
        
        <div class="text-center">
            <i class="fas fa-wallet text-4xl text-blue-500 mb-3"></i>
            <h2 class="text-3xl font-bold text-gray-900">Pesanan Diterima!</h2>
            <p class="text-lg text-gray-600 mt-2">No. Pesanan: <span class="font-semibold text-blue-600">#<?php echo $pesanan_id; ?></span></p>
            <p class="text-xl font-semibold text-yellow-600 mt-1">Status: <?php echo $pesanan['status_pesanan']; ?></p>
        </div>
        
        <hr class="my-6">

        <div>
            <h3 class="text-xl font-semibold mb-3">Rincian Pesanan Anda:</h3>
            <div class="space-y-3 border p-4 rounded-md bg-gray-50">
                <?php while($item = $details->fetch_assoc()): ?>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold"><?php echo htmlspecialchars($item['nama_menu_saat_pesan']); ?></p>
                        <p class="text-sm text-gray-600">
                            Rp <?php echo number_format($item['harga_saat_pesan'], 0, ',', '.'); ?> x <?php echo $item['jumlah']; ?>
                        </p>
                    </div>
                    <span class="font-semibold">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="flex justify-between text-2xl font-bold mt-4 pt-4 border-t">
                <span>Total Bayar</span>
                <span>Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <hr class="my-6">

        <div>
            <h3 class="text-xl font-semibold mb-3">Upload Bukti Pembayaran</h3>
            
            <div class="bg-blue-50 p-4 rounded-md border border-blue-200 mb-4">
                <h4 class="font-semibold text-blue-800">Silakan lakukan transfer ke:</h4>
                <p class="mt-2"><strong>Bank BCA:</strong> 123-456-7890 (a/n Warung Kak Su)</p>
                <p class="mt-1"><strong>QRIS:</strong> (Tampilkan gambar QRIS Anda di sini)</p>
            </div>
            
            <p class="text-sm text-center text-gray-600 mb-4">Setelah transfer, upload bukti Anda di bawah ini.</p>

            <form action="proses_upload.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="pesanan_id" value="<?php echo $pesanan_id; ?>">
                
                <div>
                    <label for="bukti_pembayaran" class="block text-sm font-medium text-gray-700">Pilih File (JPG, PNG, maks 5MB)</label>
                    <input id="bukti_pembayaran" name="bukti_pembayaran" type="file" required
                           class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100">
                </div>

                <div class="mt-6">
                    <button type="submit" name="upload"
                            class="w-full px-4 py-3 font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Konfirmasi & Kirim Bukti
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$conn->close();
// Panggil footer layout publik
include 'layouts/public/footer.php'; 
?>