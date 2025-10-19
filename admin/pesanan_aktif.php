<?php 
// Panggil header layout
include '../layouts/admin/header.php'; 
// ($conn dan $session sudah tersedia dari header)

// ==========================================================
// --- BLOK LOGIKA PHP ---
// --- TIDAK ADA PERUBAHAN SAMA SEKALI DI BAGIAN INI ---
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tandai_selesai'])) {
    $pesanan_id = $_POST['pesanan_id'];
    $stmt = $conn->prepare("UPDATE pesanan SET status_pesanan = 'Selesai' WHERE id = ?");
    $stmt->bind_param("i", $pesanan_id);
    $stmt->execute();
    $stmt->close();
    header("Location: pesanan_aktif.php?status=selesai");
    exit;
}

$result = $conn->query("SELECT * FROM pesanan WHERE status_pesanan = 'Diproses' ORDER BY waktu_pesan ASC");

$pesanan_terkelompok = [];
if ($result->num_rows > 0) {
    while($pesanan = $result->fetch_assoc()) {
        $id = $pesanan['id'];
        $pesanan_terkelompok[$id] = ['data' => $pesanan, 'items' => []];
    }
    
    $stmt_detail = $conn->prepare("SELECT p.id AS pesanan_id, dp.nama_menu_saat_pesan, dp.jumlah 
                                  FROM detail_pesanan dp
                                  JOIN pesanan p ON dp.pesanan_id = p.id
                                  WHERE p.status_pesanan = 'Diproses'");
    $stmt_detail->execute();
    $details = $stmt_detail->get_result();
    
    while ($item = $details->fetch_assoc()) {
        if (isset($pesanan_terkelompok[$item['pesanan_id']])) {
            $pesanan_terkelompok[$item['pesanan_id']]['items'][] = $item;
        }
    }
}
// ==========================================================
// --- AKHIR BLOK LOGIKA PHP ---
// ==========================================================
?>

<!-- ========================================================== -->
<!-- BAGIAN HTML DIMULAI DI SINI (DESAIN BARU) -->
<!-- ========================================================== -->

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-utensils text-orange-500 mr-3"></i>
        Pesanan Aktif (Dapur)
    </h1>
    <p class="text-gray-600 mt-1">Daftar pesanan yang sudah lunas dan perlu disiapkan.</p>
</div>

<!-- Notifikasi -->
<?php if (isset($_GET['status']) && $_GET['status'] == 'selesai'): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
        <i class="fas fa-check-circle mr-3 text-green-500"></i>
        <span>Pesanan berhasil ditandai sebagai Selesai.</span>
    </div>
<?php endif; ?>

<!-- Grid untuk Kartu Pesanan -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

    <?php if (empty($pesanan_terkelompok)): ?>
        <!-- Pesan Jika Kosong -->
        <div class="md:col-span-2 lg:col-span-3 xl:col-span-4 py-12 text-center text-gray-500 border-2 border-dashed border-gray-300 rounded-xl">
            <i class="fas fa-mug-hot fa-3x mb-3 text-gray-400"></i>
            <h3 class="text-xl font-semibold">Tidak ada pesanan aktif.</h3>
            <p class="text-sm">Dapur bisa santai sejenak.</p>
        </div>
    <?php else: ?>
        <?php foreach ($pesanan_terkelompok as $id => $data): ?>
            <?php $pesanan = $data['data']; ?>
            <!-- Kartu Pesanan ("Tiket Dapur") -->
            <div class="bg-white rounded-xl shadow-lg border border-orange-100 flex flex-col overflow-hidden transition-all transform hover:-translate-y-1">
                
                <!-- Header Kartu -->
                <div class="p-4 border-b border-gray-200 bg-orange-50">
                    <div class="flex justify-between items-baseline">
                        <div>
                            <p class="text-xs text-orange-700 font-semibold">MEJA</p>
                            <p class="text-3xl font-bold text-orange-600"><?php echo htmlspecialchars($pesanan['no_meja']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-700">#<?php echo $id; ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('H:i', strtotime($pesanan['waktu_pesan'])); ?></p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Oleh: <?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></p>
                </div>
                
                <!-- Daftar Item -->
                <div class="p-4 space-y-3 flex-grow">
                    <?php if (empty($data['items'])): ?>
                        <p class="text-sm text-gray-500 text-center py-4">Tidak ada rincian item.</p>
                    <?php else: ?>
                        <?php foreach ($data['items'] as $item): ?>
                            <div class="flex justify-between items-center text-gray-800 border-b border-dashed pb-2 last:border-b-0">
                                <span class="text-base"><?php echo htmlspecialchars($item['nama_menu_saat_pesan']); ?></span>
                                <span class="text-base font-bold">x <?php echo $item['jumlah']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="p-3 bg-gray-50 border-t">
                    <form action="pesanan_aktif.php" method="POST" onsubmit="return confirm('Yakin pesanan ini sudah diantar ke pelanggan?');">
                        <input type="hidden" name="pesanan_id" value="<?php echo $id; ?>">
                        <button type="submit" name="tandai_selesai"
                                class="w-full flex items-center justify-center px-4 py-2 font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none shadow-sm">
                            <i class="fas fa-check-circle mr-2"></i> Tandai Selesai
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php 
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
include '../layouts/admin/footer.php'; 
?>