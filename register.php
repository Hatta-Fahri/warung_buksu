<?php
require 'config/db.php'; // config/db.php sudah otomatis session_start()
$error = '';
$success = '';

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // 1. Validasi Sederhana
    if (empty($nama_lengkap) || empty($username) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    }
    // 2. Cek apakah password cocok
    elseif ($password != $konfirmasi_password) {
        $error = 'Password dan Konfirmasi Password tidak cocok!';
    }
    // 3. Cek panjang password (opsional tapi bagus)
    elseif (strlen($password) < 6) {
        $error = 'Password minimal harus 6 karakter.';
    }
    else {
        // 4. Cek apakah username sudah ada
        $stmt_cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_cek->bind_param("s", $username);
        $stmt_cek->execute();
        $result_cek = $stmt_cek->get_result();
        
        if ($result_cek->num_rows > 0) {
            $error = 'Username ini sudah dipakai. Silakan pilih username lain.';
        } else {
            // 5. SEMUA AMAN, HASH PASSWORD DAN SIMPAN
            // Ini adalah bagian terpenting
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt_insert = $conn->prepare("INSERT INTO users (nama_lengkap, username, password) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $nama_lengkap, $username, $hashed_password);
            
            if ($stmt_insert->execute()) {
                $success = "Akun Admin Utama berhasil dibuat!";
            } else {
                $error = "Terjadi kesalahan database. Gagal membuat akun.";
            }
            $stmt_insert->close();
        }
        $stmt_cek->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin Utama - Warung Kak Su</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h2 class="text-3xl font-bold text-center text-gray-900">Buat Akun Admin</h2>
        <p class="text-center text-sm text-gray-600">Halaman ini hanya untuk setup awal.</p>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <a href="login.php" class="font-bold underline">Klik di sini untuk Login</a>
            </div>
        <?php else: ?>
        
        <form class="space-y-6" action="register.php" method="POST">
            <div>
                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" type="text" required
                       class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input id="username" name="username" type="text" required
                       class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input id="konfirmasi_password" name="konfirmasi_password" type="password" required
                       class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <button type="submit" name="register"
                        class="w-full px-4 py-2 font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Buat Akun
                </button>
            </div>
        </form>
        
        <?php endif; ?>
    </div>
</body>
</html>