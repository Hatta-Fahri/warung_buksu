<?php 
include '../layouts/admin/header.php';  
// ($conn sudah di-include dari header.php)

// -- LOGIKA FILTER TANGGAL --
$filter_sql = ""; // SQL condition for filtering
$filter_text = "Semua Waktu";
$tanggal_awal = date('Y-m-01'); 
$tanggal_akhir = date('Y-m-t'); 

$filter_get = $_GET['filter'] ?? 'bulan_ini'; 

if ($filter_get == 'hari_ini') {
    $filter_sql = "AND DATE(p.waktu_pesan) = CURDATE()"; // Alias 'p' needed later
    $filter_text = "Hari Ini (" . date('d M Y') . ")";
} elseif ($filter_get == 'bulan_ini') {
    $filter_sql = "AND DATE(p.waktu_pesan) BETWEEN '{$tanggal_awal}' AND '{$tanggal_akhir}'"; // Alias 'p'
    $filter_text = "Bulan Ini (" . date('M Y') . ")";
} elseif ($filter_get == 'semua') {
    $filter_sql = "";
    $filter_text = "Semua Waktu";
}
// -- AKHIR LOGIKA FILTER --


// 1. Ambil data pesanan yang SUDAH SELESAI (Query utama)
//    BARU: Tambahkan alias 'p' untuk tabel pesanan
$query_str = "SELECT p.* FROM pesanan p 
              WHERE p.status_pesanan = 'Selesai' 
              {$filter_sql} 
              ORDER BY p.waktu_pesan DESC";
$result = $conn->query($query_str);

// 2. Hitung Total Pendapatan & Jumlah Pesanan
$total_pendapatan = 0;
$total_pesanan = $result->num_rows;
$ids_pesanan_selesai = []; // Kumpulkan ID untuk query berikutnya
while ($row = $result->fetch_assoc()) {
    $total_pendapatan += $row['total_harga'];
    $ids_pesanan_selesai[] = $row['id']; // Tambahkan ID
}
$result->data_seek(0); // Kembalikan pointer result

// 3. BARU: Hitung Total Item Terjual
$total_item_terjual = 0;
if (!empty($ids_pesanan_selesai)) {
    $ids_string = implode(',', $ids_pesanan_selesai); // Ubah array ID jadi string '1,2,3'
    $q_total_item = $conn->query("SELECT SUM(jumlah) AS total FROM detail_pesanan WHERE pesanan_id IN ({$ids_string})");
    $total_item_terjual = $q_total_item->fetch_assoc()['total'] ?? 0;
}

// 4. BARU: Hitung Rata-Rata Nilai Pesanan (AOV)
$aov = ($total_pesanan > 0) ? ($total_pendapatan / $total_pesanan) : 0;

// 5. BARU: Ambil Menu Terlaris Sesuai Periode Filter
$menu_terlaris = [];
if (!empty($ids_pesanan_selesai)) {
    $q_menu_terlaris = $conn->query("
        SELECT m.nama_menu, SUM(dp.jumlah) as total_terjual
        FROM detail_pesanan dp
        JOIN menu m ON dp.menu_id = m.id
        WHERE dp.pesanan_id IN ({$ids_string})
        GROUP BY dp.menu_id
        ORDER BY total_terjual DESC
        LIMIT 5
    ");
    while ($menu_row = $q_menu_terlaris->fetch_assoc()) {
        $menu_terlaris[] = $menu_row;
    }
}

?>

<div class="bg-white p-6 rounded-xl shadow-lg border border-orange-100 mb-8">
    <div class="flex flex-col md:flex-row justify-between md:items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
             <i class="fas fa-chart-line text-orange-500 mr-3"></i> Laporan Penjualan
        </h1>
        
        <form action="laporan.php" method="GET" class="mt-4 md:mt-0">
            <select name="filter" onchange="this.form.submit()" 
                    class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white text-sm">
                <option value="bulan_ini" <?php echo ($filter_get == 'bulan_ini') ? 'selected' : ''; ?>>Bulan Ini</option>
                <option value="hari_ini" <?php echo ($filter_get == 'hari_ini') ? 'selected' : ''; ?>>Hari Ini</option>
                <option value="semua" <?php echo ($filter_get == 'semua') ? 'selected' : ''; ?>>Semua Waktu</option>
            </select>
        </form>
    </div>
    <p class="text-sm text-gray-600">Menampilkan laporan untuk: <span class="font-semibold text-gray-800"><?php echo $filter_text; ?></span></p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider mb-1">Total Pendapatan</h4>
        <p class="text-3xl font-bold text-gray-800">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></p>
        <div class="mt-2 text-green-500 flex items-center text-xs"><i class="fas fa-wallet mr-1"></i> Penjualan Selesai</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider mb-1">Total Pesanan</h4>
        <p class="text-3xl font-bold text-gray-800"><?php echo $total_pesanan; ?></p>
         <div class="mt-2 text-blue-500 flex items-center text-xs"><i class="fas fa-receipt mr-1"></i> Transaksi Berhasil</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider mb-1">Item Terjual</h4>
        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_item_terjual, 0, ',', '.'); ?></p>
        <div class="mt-2 text-purple-500 flex items-center text-xs"><i class="fas fa-shopping-basket mr-1"></i> Total Kuantitas</div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider mb-1">Rata-Rata Pesanan</h4>
        <p class="text-3xl font-bold text-gray-800">Rp <?php echo number_format($aov, 0, ',', '.'); ?></p>
         <div class="mt-2 text-yellow-500 flex items-center text-xs"><i class="fas fa-calculator mr-1"></i> Per Transaksi</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2 bg-white p-6 md:p-8 rounded-xl shadow-lg border border-orange-100">
        <h2 class="text-xl font-bold text-gray-800 flex items-center mb-6">
            <i class="fas fa-list-alt text-orange-500 mr-3"></i>
            Rincian Transaksi Selesai
        </h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-orange-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-orange-700 uppercase tracking-wider">No. Pesanan</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-orange-700 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-orange-700 uppercase tracking-wider">Pelanggan</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-orange-700 uppercase tracking-wider">Meja</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-orange-700 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-orange-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-orange-700">#<?php echo $row['id']; ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo date('d M Y, H:i', strtotime($row['waktu_pesan'])); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600"><?php echo htmlspecialchars($row['no_meja']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 font-semibold text-right">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                <i class="fas fa-folder-open fa-2x mb-2 text-gray-400"></i><br>
                                Belum ada data penjualan untuk periode ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border border-orange-100">
         <h2 class="text-xl font-bold text-gray-800 flex items-center mb-6">
            <i class="fas fa-star text-orange-500 mr-3"></i>
            Menu Terlaris Periode Ini
        </h2>
        <?php if (!empty($menu_terlaris)): ?>
            <ol class="list-decimal list-inside space-y-3">
                <?php foreach ($menu_terlaris as $menu): ?>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-gray-800"><?php echo htmlspecialchars($menu['nama_menu']); ?></span>
                        <span class="font-semibold text-orange-600 bg-orange-100 px-2 py-0.5 rounded">
                            <?php echo $menu['total_terjual']; ?>x
                        </span>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
             <div class="py-10 text-center text-gray-500">
                <i class="fas fa-utensils fa-2x mb-2 text-gray-400"></i><br>
                Belum ada data menu terlaris untuk periode ini.
            </div>
        <?php endif; ?>
    </div>

</div>

<?php 
$conn->close();
include '../layouts/admin/footer.php'; 
?>