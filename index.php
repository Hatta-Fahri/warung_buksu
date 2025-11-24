<?php
// Panggil header layout publik
// Pastikan header publik Anda juga memiliki body bg-orange-50 atau bg-yellow-50
// Jika belum, Anda bisa buka layouts/public/header.php dan ubah class body
include 'layouts/public/header.php';
// ($conn dan $session sudah tersedia dari header)

// Ambil data menu yang statusnya 'Tersedia'
$result = $conn->query("SELECT * FROM menu WHERE status = 'Tersedia' ORDER BY kategori, nama_menu");
?>

<div class="container mx-auto px-4 py-8">

    <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-3 text-green-500"></i>
            <div>
                <strong class="font-bold">Berhasil!</strong>
                <span class="block sm:inline">Menu berhasil ditambahkan ke keranjang Anda.</span>
            </div>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
            <div>
                <strong class="font-bold">Gagal!</strong>
                <span class="block sm:inline">Tidak dapat menambahkan menu. Silakan coba lagi.</span>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mb-10">
        <h2 class="text-4xl font-bold text-orange-800 mb-2 font-serif">Pilihan Menu Lezat</h2>
        <p class="text-gray-600">Silakan pilih menu favorit Anda di dapoer bunasya!</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($menu = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col border border-orange-100 hover:shadow-xl hover:border-orange-300 transition-all duration-300 transform hover:-translate-y-1">

                    <div class="relative">
                        <?php if (!empty($menu['gambar'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($menu['gambar']); ?>"
                                alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>"
                                class="w-full h-52 object-cover">
                        <?php else: ?>
                            <div class="w-full h-52 bg-orange-50 flex items-center justify-center text-orange-300">
                                <i class="fas fa-utensils fa-4x"></i>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-3 right-3 bg-orange-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full shadow"><?php echo htmlspecialchars($menu['kategori']); ?></span>
                    </div>

                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="text-xl font-semibold mb-1 text-gray-800 truncate" title="<?php echo htmlspecialchars($menu['nama_menu']); ?>"><?php echo htmlspecialchars($menu['nama_menu']); ?></h3>
                        <p class="text-2xl font-bold text-orange-700 mb-4">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></p>

                        <div class="mt-auto pt-4 border-t border-gray-100">
                            <form action="cart_action.php" method="POST">
                                <input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>">
                                <input type="hidden" name="action" value="tambah">

                                <div class="flex items-center justify-between mb-3">
                                    <label for="jumlah_<?php echo $menu['id']; ?>" class="text-sm font-medium text-gray-600">Jumlah:</label>
                                    <input type="number" id="jumlah_<?php echo $menu['id']; ?>" name="jumlah" value="1" min="1"
                                        class="w-20 px-2 py-1 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent text-center">
                                </div>

                                <button type="submit"
                                    class="w-full flex items-center justify-center mt-2 bg-orange-600 text-white px-4 py-2.5 rounded-lg hover:bg-orange-700 font-semibold text-center transition-colors shadow hover:shadow-md">
                                    <i class="fas fa-cart-plus mr-1.5"></i> Tambah
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="sm:col-span-2 md:col-span-3 lg:col-span-4 py-12 text-center text-gray-500 border-2 border-dashed border-orange-200 rounded-xl bg-orange-50">
                <i class="fas fa-utensils fa-4x mb-4 text-orange-300"></i>
                <h3 class="text-xl font-semibold text-orange-700">Mohon Maaf...</h3>
                <p>Saat ini belum ada menu yang tersedia.</p>
                <p class="text-sm">Silakan cek kembali nanti.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
// Panggil footer layout publik
include 'layouts/public/footer.php';
?>