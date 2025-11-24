<?php
// config/db.php sudah otomatis menjalankan session_start()
require 'config/db.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = '';

// Cek jika form disubmit
if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        // 1. Siapkan query (kita ambil nama_lengkap, role sudah tidak ada)
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 2. Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Password benar, simpan data ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

                header("Location: admin/dashboard.php");
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - dapoer bunasya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts untuk font yang lebih modern -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* Latar belakang dengan gradien lembut */
            background-color: #fff7ed;
            background-image: radial-gradient(#fed7aa 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden grid md:grid-cols-2">

        <!-- Sisi Kiri: Gambar & Branding -->
        <div class="hidden md:block relative">
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?q=80&w=1974&auto=format&fit=crop"
                alt="Interior Warung Makan"
                class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-orange-900 bg-opacity-60 flex flex-col justify-end p-8 text-white">
                <h1 class="text-4xl font-bold leading-tight mb-2">Selamat Datang Kembali</h1>
                <p class="text-orange-200">Sistem Manajemen dapoer bunasya.</p>
            </div>
        </div>

        <!-- Sisi Kanan: Form Login -->
        <div class="p-8 md:p-12 flex flex-col justify-center">
            <div class="text-center md:text-left mb-8">
                <h2 class="text-3xl font-bold text-orange-800">Login Admin</h2>
                <p class="text-gray-500 mt-2">Masukkan kredensial Anda untuk melanjutkan.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center" role="alert">
                    <i class="fas fa-exclamation-triangle mr-3 text-red-500"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="login.php" method="POST">
                <!-- Input Username dengan Ikon -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-user text-gray-400"></i>
                        </span>
                        <input id="username" name="username" type="text" required autofocus
                            placeholder="Masukkan username"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Input Password dengan Ikon -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-lock text-gray-400"></i>
                        </span>
                        <input id="password" name="password" type="password" required
                            placeholder="Masukkan password"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Tombol Login -->
                <div>
                    <button type="submit" name="login"
                        class="w-full flex items-center justify-center px-4 py-3 font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors shadow-lg hover:shadow-orange-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>