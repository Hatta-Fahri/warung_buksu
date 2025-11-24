<?php
// Panggil header layout
include '../layouts/admin/header.php';
// ($conn dan $session sudah tersedia dari header)

/*
 * ==========================================================
 * AMBIL DATA STATISTIK UNTUK DASHBOARD
 * (Bagian ini tidak berubah)
 * ==========================================================
 */
$q_verif = $conn->query("SELECT COUNT(id) AS total FROM pesanan WHERE status_pesanan = 'Menunggu Verifikasi'");
$stat_verif = $q_verif->fetch_assoc()['total'];
$q_proses = $conn->query("SELECT COUNT(id) AS total FROM pesanan WHERE status_pesanan = 'Diproses'");
$stat_proses = $q_proses->fetch_assoc()['total'];
$q_omzet = $conn->query("SELECT COALESCE(SUM(total_harga), 0) AS total FROM pesanan WHERE status_pesanan = 'Selesai' AND DATE(waktu_pesan) = CURDATE()");
$stat_omzet = $q_omzet->fetch_assoc()['total'];
$q_menu = $conn->query("SELECT COUNT(id) AS total FROM menu WHERE status = 'Tersedia'");
$stat_menu = $q_menu->fetch_assoc()['total'];

/*
 * ==========================================================
 * PERSIAPAN DATA UNTUK GRAFIK (CHARTS)
 * ==========================================================
 */

// 2. Data Grafik Penjualan Harian (Tidak berubah)
$daily_sales_labels = [];
$daily_sales_data = [];
$q_daily_sales = $conn->query("SELECT DATE(waktu_pesan) as tanggal, SUM(total_harga) as total_penjualan FROM pesanan WHERE status_pesanan = 'Selesai' AND waktu_pesan >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(waktu_pesan) ORDER BY tanggal");
while ($row = $q_daily_sales->fetch_assoc()) {
    $daily_sales_labels[] = date('d/m', strtotime($row['tanggal']));
    $daily_sales_data[] = $row['total_penjualan'];
}

// 3. Data Grafik Menu Terpopuler (Tidak berubah)
$popular_menu_labels = [];
$popular_menu_data = [];
$q_popular_menu = $conn->query("SELECT m.nama_menu, COUNT(dp.menu_id) as jumlah_terjual FROM detail_pesanan dp JOIN menu m ON dp.menu_id = m.id JOIN pesanan p ON dp.pesanan_id = p.id WHERE p.status_pesanan = 'Selesai' AND p.waktu_pesan >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY m.id ORDER BY jumlah_terjual DESC LIMIT 5");
while ($row = $q_popular_menu->fetch_assoc()) {
    $popular_menu_labels[] = $row['nama_menu'];
    $popular_menu_data[] = $row['jumlah_terjual'];
}

// --- PERUBAHAN DI SINI: Persiapan Data Grafik Status Pesanan ---
// 4a. Definisikan SEMUA status yang mungkin ada, dalam urutan yang diinginkan
$all_possible_statuses = [
    'Menunggu Verifikasi',
    'Diproses',
    'Selesai',
    'Dibatalkan',          // Pastikan 'Dibatalkan' ada di sini
    'Menunggu Pembayaran'
];

// 4b. Ambil data jumlah per status dari database (query tetap sama)
$status_counts_from_db = [];
$q_status_dist = $conn->query("
    SELECT status_pesanan, COUNT(id) as jumlah
    FROM pesanan
    WHERE waktu_pesan >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY status_pesanan
");
while ($row = $q_status_dist->fetch_assoc()) {
    $status_counts_from_db[$row['status_pesanan']] = $row['jumlah'];
}

// 4c. Siapkan array final untuk chart, pastikan semua status ada (meskipun 0)
$order_status_labels = [];
$order_status_data = [];
foreach ($all_possible_statuses as $status) {
    $order_status_labels[] = $status; // Tambahkan label status
    // Cek apakah status ini ada di hasil query, jika ada ambil jumlahnya, jika tidak, set 0
    $order_status_data[] = isset($status_counts_from_db[$status]) ? $status_counts_from_db[$status] : 0;
}

?>

<div class="bg-white p-8 rounded-xl shadow-lg border border-orange-100 mb-8">
    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-user-circle text-orange-500 mr-3"></i>
        Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!
    </h1>
    <p class="text-gray-600 mt-2">Ini adalah rangkuman aktivitas dapoer bunasya hari ini.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <a href="verifikasi.php" class="block bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-1 border border-orange-100">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider">Perlu Verifikasi</h4>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stat_verif; ?></p>
            </div>
            <div class="bg-orange-50 p-3 rounded-full">
                <i class="fas fa-exclamation-circle text-3xl text-orange-500"></i>
            </div>
        </div>
        <p class="text-sm text-gray-600 mt-2">Pesanan menunggu dicek</p>
    </a>

    <a href="pesanan_aktif.php" class="block bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-1 border border-orange-100">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider">Sedang Diproses</h4>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stat_proses; ?></p>
            </div>
            <div class="bg-orange-50 p-3 rounded-full">
                <i class="fas fa-utensils text-3xl text-orange-500"></i>
            </div>
        </div>
        <p class="text-sm text-gray-600 mt-2">Pesanan aktif di dapur</p>
    </a>

    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider">Omzet Hari Ini</h4>
                <p class="text-3xl font-bold text-gray-800 mt-1">Rp <?php echo number_format($stat_omzet, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-orange-50 p-3 rounded-full">
                <i class="fas fa-wallet text-3xl text-orange-500"></i>
            </div>
        </div>
        <p class="text-sm text-gray-600 mt-2">Total penjualan selesai</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-orange-600 text-sm font-medium uppercase tracking-wider">Menu Tersedia</h4>
                <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $stat_menu; ?></p>
            </div>
            <div class="bg-orange-50 p-3 rounded-full">
                <i class="fas fa-book-open text-3xl text-orange-500"></i>
            </div>
        </div>
        <p class="text-sm text-gray-600 mt-2">Item menu siap dijual</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <h3 class="text-xl font-bold text-gray-800 flex items-center mb-4">
            <i class="fas fa-chart-line text-orange-500 mr-2"></i>
            Penjualan 7 Hari Terakhir
        </h3>
        <div class="relative h-52">
            <canvas id="dailySalesChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md border border-orange-100">
        <h3 class="text-xl font-bold text-gray-800 flex items-center mb-4">
            <i class="fas fa-star text-orange-500 mr-2"></i>
            Menu Terpopuler
        </h3>
        <div class="relative h-52">
            <canvas id="popularMenuChart"></canvas>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-md border border-orange-100 mb-8">
    <h3 class="text-xl font-bold text-gray-800 flex items-center mb-4">
        <i class="fas fa-chart-pie text-orange-500 mr-2"></i>
        Distribusi Status Pesanan (30 Hari Terakhir)
    </h3>
    <div class="relative h-52 max-w-md mx-auto">
        <canvas id="orderStatusChart"></canvas>
    </div>
</div>

<div class="bg-white p-8 rounded-xl shadow-lg border border-orange-100">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center mb-6">
        <i class="fas fa-bolt text-orange-500 mr-3"></i>
        Akses Cepat
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="verifikasi.php" class="group flex items-center p-4 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors">
            <div class="p-3 bg-white rounded-full mr-4 shadow-sm group-hover:shadow">
                <i class="fas fa-exclamation-circle text-xl text-orange-500"></i>
            </div>
            <div>
                <p class="font-semibold text-lg text-gray-800">Verifikasi Pembayaran</p>
                <p class="text-sm text-gray-600">Setujui atau tolak pesanan baru.</p>
            </div>
        </a>
        <a href="menu.php" class="group flex items-center p-4 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors">
            <div class="p-3 bg-white rounded-full mr-4 shadow-sm group-hover:shadow">
                <i class="fas fa-book-open text-xl text-orange-500"></i>
            </div>
            <div>
                <p class="font-semibold text-lg text-gray-800">Kelola Menu</p>
                <p class="text-sm text-gray-600">Tambah, edit, atau hapus menu.</p>
            </div>
        </a>
        <a href="laporan.php" class="group flex items-center p-4 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors">
            <div class="p-3 bg-white rounded-full mr-4 shadow-sm group-hover:shadow">
                <i class="fas fa-chart-line text-xl text-orange-500"></i>
            </div>
            <div>
                <p class="font-semibold text-lg text-gray-800">Lihat Laporan</p>
                <p class="text-sm text-gray-600">Pantau total penjualan.</p>
            </div>
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Menunggu DOM siap
    document.addEventListener('DOMContentLoaded', function() {

        // Ambil data dari PHP
        const dailySalesLabels = <?php echo json_encode($daily_sales_labels); ?>;
        const dailySalesData = <?php echo json_encode($daily_sales_data); ?>;
        const popularMenuLabels = <?php echo json_encode($popular_menu_labels); ?>;
        const popularMenuData = <?php echo json_encode($popular_menu_data); ?>;
        const orderStatusLabels = <?php echo json_encode($order_status_labels); ?>;
        const orderStatusData = <?php echo json_encode($order_status_data); ?>;

        // Buat Grafik Penjualan Harian
        new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
                labels: dailySalesLabels,
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: dailySalesData,
                    borderColor: '#f97316',
                    backgroundColor: '#fff7ed',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- INI PENTING
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // Buat Grafik Menu Terpopuler
        new Chart(document.getElementById('popularMenuChart'), {
            type: 'bar',
            data: {
                labels: popularMenuLabels,
                datasets: [{
                    label: 'Jumlah Terjual',
                    data: popularMenuData,
                    backgroundColor: '#fed7aa',
                    borderColor: '#f97316',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- INI PENTING
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Buat Grafik Status Pesanan
        new Chart(document.getElementById('orderStatusChart'), {
            type: 'doughnut',
            data: {
                labels: orderStatusLabels,
                datasets: [{
                    data: orderStatusData,
                    backgroundColor: [
                        '#fcd34d', // Menunggu Verifikasi
                        '#60a5fa', // Diproses
                        '#34d399', // Selesai
                        '#f87171', // Dibatalkan
                        '#9ca3af' // Menunggu Pembayaran
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // <-- INI PENTING
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    });
</script>

<?php
// Tutup koneksi database
$conn->close();
// Panggil footer layout
include '../layouts/admin/footer.php';
?>