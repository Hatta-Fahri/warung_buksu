<?php 
// Panggil header layout
include '../layouts/admin/header.php'; 
// ($conn dan $session sudah tersedia dari header)

// ==========================================================
// --- BLOK LOGIKA PHP (CRUD) ---
// --- TIDAK ADA PERUBAHAN SAMA SEKALI DI BAGIAN INI ---
// ==========================================================
$error = '';
$success = '';
$edit_mode = false;
$menu_to_edit = [
    'id' => '', 'nama_menu' => '', 'gambar' => '', 'kategori' => 'Makanan', 'harga' => '', 'status' => 'Tersedia'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_menu = $_POST['nama_menu'];
    $kategori = $_POST['kategori'];
    $harga = $_POST['harga'];
    $status = $_POST['status'];
    $upload_dir = "../uploads/";

    if (isset($_POST['simpan_menu'])) {
        $nama_file_gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $file = $_FILES['gambar'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (in_array($file_ext, $allowed_ext) && $file['size'] < 5000000) {
                $nama_file_gambar = "menu_" . time() . "." . $file_ext;
                if (!move_uploaded_file($file['tmp_name'], $upload_dir . $nama_file_gambar)) {
                    $error = "Gagal meng-upload gambar.";
                    $nama_file_gambar = '';
                }
            } else { $error = "File tidak valid (Hanya JPG/PNG, maks 5MB)."; }
        } else { $error = "Gambar menu wajib di-upload."; }
        
        if (empty($nama_menu) || empty($kategori) || empty($harga)) {
            $error = "Nama, Kategori, dan Harga wajib diisi!";
        } elseif (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO menu (nama_menu, gambar, kategori, harga, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssds", $nama_menu, $nama_file_gambar, $kategori, $harga, $status);
            if ($stmt->execute()) { $success = "Menu baru berhasil ditambahkan!"; } 
            else { $error = "Gagal menambahkan menu: ". $stmt->error; }
            $stmt->close();
        }
    }

    if (isset($_POST['update_menu'])) {
        $id = $_POST['id'];
        $gambar_lama = $_POST['gambar_lama'];
        $nama_file_gambar = $gambar_lama;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $file = $_FILES['gambar'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (in_array($file_ext, $allowed_ext) && $file['size'] < 5000000) {
                $nama_file_gambar = "menu_" . time() . "." . $file_ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $nama_file_gambar)) {
                    if (!empty($gambar_lama) && file_exists($upload_dir . $gambar_lama)) {
                        unlink($upload_dir . $gambar_lama);
                    }
                } else {
                    $error = "Gagal meng-upload gambar baru.";
                    $nama_file_gambar = $gambar_lama;
                }
            } else { $error = "File baru tidak valid (Hanya JPG/PNG, maks 5MB)."; }
        }
        if (empty($nama_menu) || empty($kategori) || empty($harga) || empty($id)) {
            $error = "Semua data wajib diisi untuk update!";
        } elseif (empty($error)) {
            $stmt = $conn->prepare("UPDATE menu SET nama_menu = ?, gambar = ?, kategori = ?, harga = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssdsi", $nama_menu, $nama_file_gambar, $kategori, $harga, $status, $id);
            if ($stmt->execute()) {
                $success = "Menu berhasil diperbarui! <a href='menu.php' class='underline'>Kembali ke mode Tambah</a>";
                $edit_mode = true;
                $menu_to_edit = ['id' => $id, 'nama_menu' => $nama_menu, 'gambar' => $nama_file_gambar, 'kategori' => $kategori, 'harga' => $harga, 'status' => $status];
            } else { $error = "Gagal memperbarui menu: ". $stmt->error; }
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt_img = $conn->prepare("SELECT gambar FROM menu WHERE id = ?");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $result_img = $stmt_img->get_result();
        $gambar_lama = ($result_img->num_rows > 0) ? $result_img->fetch_assoc()['gambar'] : null;
        $stmt_img->close();
        $stmt = $conn->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if (!empty($gambar_lama) && file_exists("../uploads/" . $gambar_lama)) {
                @unlink("../uploads/" . $gambar_lama); // Use @ to suppress warning if file not found
            }
            header("Location: menu.php?status=dihapus");
            exit;
        } else { $error = "Gagal menghapus menu: ". $stmt->error; }
        $stmt->close();
    }
    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM menu WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $edit_mode = true;
            $menu_to_edit = $result->fetch_assoc();
        } else { $error = "Menu tidak ditemukan."; }
        $stmt->close();
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'dihapus') {
    $success = "Menu berhasil dihapus.";
}

$result_tabel = $conn->query("SELECT * FROM menu ORDER BY kategori, nama_menu");
// ==========================================================
// --- AKHIR BLOK LOGIKA PHP ---
// ==========================================================
?>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
        <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
        <i class="fas fa-check-circle mr-3 text-green-500"></i>
        <span><?php echo $success; ?></span>
    </div>
<?php endif; ?>

<div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-orange-100 mb-8">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center mb-6">
        <i class="fas <?php echo $edit_mode ? 'fa-edit' : 'fa-plus-circle'; ?> text-orange-500 mr-3"></i>
        <?php echo $edit_mode ? 'Edit Menu' : 'Tambah Menu Baru'; ?>
    </h2>

    <form action="menu.php<?php echo $edit_mode ? '?action=edit&id='.$menu_to_edit['id'] : ''; ?>" method="POST" enctype="multipart/form-data" 
          class="max-w-lg mx-auto space-y-5"> 
        
        <?php if ($edit_mode): ?>
            <input type="hidden" name="id" value="<?php echo $menu_to_edit['id']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($menu_to_edit['gambar']); ?>">
        <?php endif; ?>

        <div>
            <label for="nama_menu" class="block text-sm font-medium text-gray-700 mb-1">Nama Menu</label>
            <input type="text" id="nama_menu" name="nama_menu" required value="<?php echo htmlspecialchars($menu_to_edit['nama_menu']); ?>"
                   placeholder="Contoh: Nasi Goreng Spesial"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>

        <div>
            <label for="gambar" class="block text-sm font-medium text-gray-700 mb-1">
                Gambar Menu <?php echo $edit_mode ? '(Kosongkan jika tidak ganti)' : ''; ?>
            </label>
            <input type="file" id="gambar" name="gambar" <?php echo !$edit_mode ? 'required' : ''; ?>
                   class="w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer focus:outline-none
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-l-lg file:border-0
                          file:text-sm file:font-semibold
                          file:bg-orange-50 file:text-orange-700
                          hover:file:bg-orange-100">
            <?php if ($edit_mode && !empty($menu_to_edit['gambar'])): ?>
                <div class="mt-2 flex items-center gap-2">
                    <img src="../uploads/<?php echo htmlspecialchars($menu_to_edit['gambar']); ?>" alt="Gambar Lama" class="w-12 h-12 object-cover rounded border">
                    <span class="text-xs text-gray-500">Gambar saat ini</span>
                </div>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select id="kategori" name="kategori" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white">
                <option value="Makanan" <?php echo ($menu_to_edit['kategori'] == 'Makanan') ? 'selected' : ''; ?>>Makanan</option>
                <option value="Minuman" <?php echo ($menu_to_edit['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
                <option value="Cemilan" <?php echo ($menu_to_edit['kategori'] == 'Cemilan') ? 'selected' : ''; ?>>Cemilan</option>
            </select>
        </div>

        <div>
            <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
            <input type="number" id="harga" name="harga" min="0" required value="<?php echo htmlspecialchars($menu_to_edit['harga']); ?>"
                   placeholder="Contoh: 25000"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>

        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Ketersediaan</label>
            <select id="status" name="status" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-white">
                <option value="Tersedia">Tersedia (Tampil)</option>
                <option value="Habis" <?php echo ($menu_to_edit['status'] == 'Habis') ? 'selected' : ''; ?>>Habis (Sembunyi)</option>
            </select>
        </div>
        
        <div class="flex <?php echo $edit_mode ? 'justify-between' : 'justify-end'; ?> items-center pt-3">
            <?php if ($edit_mode): ?>
                <a href="menu.php" class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</a>
                <button type="submit" name="update_menu" class="px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-save mr-1"></i> Update Menu
                </button>
            <?php else: ?>
                <button type="submit" name="simpan_menu" class="w-full px-5 py-2 font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors">
                    <i class="fas fa-plus mr-1"></i> Simpan Menu Baru
                </button>
            <?php endif; ?>
        </div>
    </form>
    </div>

<div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-orange-100">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center mb-6">
        <i class="fas fa-list-ul text-orange-500 mr-3"></i>
        Daftar Menu
    </h2>

    <div class="space-y-4">
        <?php if ($result_tabel->num_rows > 0): ?>
            <?php while($row = $result_tabel->fetch_assoc()): ?>
            <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 p-4 rounded-lg border border-gray-200 hover:border-orange-200 hover:bg-orange-50 transition-colors">
                <?php if (!empty($row['gambar'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama_menu']); ?>" 
                         class="w-20 h-20 sm:w-16 sm:h-16 object-cover rounded-lg border flex-shrink-0">
                <?php else: ?>
                    <div class="w-20 h-20 sm:w-16 sm:h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 flex-shrink-0"><i class="fas fa-image text-2xl"></i></div>
                <?php endif; ?>
                
                <div class="flex-grow text-center sm:text-left">
                    <p class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($row['nama_menu']); ?></p>
                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['kategori']); ?> - 
                        <span class="font-medium text-gray-700">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                    </p>
                </div>

                <div class="flex items-center space-x-4 flex-shrink-0">
                    <?php if ($row['status'] == 'Tersedia'): ?>
                        <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                            <i class="fas fa-check-circle mr-1.5"></i> Tersedia
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full">
                            <i class="fas fa-times-circle mr-1.5"></i> Habis
                        </span>
                    <?php endif; ?>
                    
                    <div class="flex space-x-2">
                        <a href="menu.php?action=edit&id=<?php echo $row['id']; ?>" class="w-9 h-9 flex items-center justify-center bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-200 transition-colors" title="Edit">
                            <i class="fas fa-pencil-alt text-sm"></i>
                        </a>
                        <a href="menu.php?action=delete&id=<?php echo $row['id']; ?>" class="w-9 h-9 flex items-center justify-center bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" title="Hapus"
                           onclick="return confirm('Yakin ingin menghapus menu \"<?php echo addslashes(htmlspecialchars($row['nama_menu'])); ?>\"?');">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="py-10 text-center text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                <i class="fas fa-utensils fa-3x mb-3 text-gray-400"></i>
                <p>Belum ada menu yang ditambahkan.</p>
                <p class="text-sm">Gunakan form di atas untuk membuat menu baru.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$conn->close();
// Panggil footer layout
include '../layouts/admin/footer.php'; 
?>  