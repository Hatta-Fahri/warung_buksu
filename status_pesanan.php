<?php
// config/db.php akan otomatis memulai session
require 'config/db.php';

// 1. Ambil ID pesanan dari URL
if (!isset($_GET['pesanan_id'])) {
    // Jika tidak ada ID, tidak ada yang bisa ditampilkan
    header("Location: index.php");
    exit;
}
$pesanan_id = (int)$_GET['pesanan_id'];

// 2. Ambil data pesanan utama
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ?");
$stmt->bind_param("i", $pesanan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Pesanan tidak ditemukan
    echo "Pesanan tidak ditemukan.";
    $stmt->close();
    $conn->close();
    exit;
}
$pesanan = $result->fetch_assoc();
$stmt->close();

// 3. Ambil data detail item pesanan
$stmt_detail = $conn->prepare("SELECT nama_menu_saat_pesan, jumlah, subtotal FROM detail_pesanan WHERE pesanan_id = ?");
$stmt_detail->bind_param("i", $pesanan_id);
$stmt_detail->execute();
$details = $stmt_detail->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_detail->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan #<?php echo $pesanan_id; ?> - dapoer bunasya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .status-card:hover {
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-orange-50 to-white min-h-screen">
    <?php include 'layouts/public/header.php'; ?>

    <div class="container mx-auto p-4 max-w-2xl">
        <?php if (isset($_GET['upload']) && $_GET['upload'] == 'sukses'): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg mb-6 shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-2xl mr-3"></i>
                    <div>
                        <p class="font-bold text-green-700">Berhasil!</p>
                        <p class="text-green-600">Bukti pembayaran Anda telah terkirim. Admin akan segera memverifikasinya.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-orange-100">
            <div class="flex items-center mb-6">
                <i class="fas fa-receipt text-orange-500 text-3xl mr-3"></i>
                <h2 class="text-3xl font-bold text-gray-800">Status Pesanan</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-orange-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">No. Pesanan</p>
                    <p class="text-lg font-bold text-orange-600">#<?php echo $pesanan_id; ?></p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Nama Pemesan</p>
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($pesanan['nama_pelanggan']); ?></p>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg col-span-2">
                    <p class="text-sm text-gray-600">Nomor Meja</p>
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($pesanan['no_meja']); ?></p>
                </div>
                <?php
                // 5. Logika untuk Status Pesanan
                $status = $pesanan['status_pesanan'];
                $icon = '';
                $bgColor = '';
                $textColor = '';
                $message = '';
                $catatanAdmin = htmlspecialchars($pesanan['catatan_admin'] ?? '');

                switch ($status) {
                    case 'Menunggu Verifikasi':
                        $icon = 'fa-clock';
                        $bgColor = 'bg-yellow-100';
                        $textColor = 'text-yellow-800';
                        $message = 'Pesanan Anda sedang menunggu verifikasi oleh admin.';
                        break;
                    case 'Diproses':
                        $icon = 'fa-utensils';
                        $bgColor = 'bg-blue-100';
                        $textColor = 'text-blue-800';
                        $message = 'Pesanan Anda telah diverifikasi dan sedang disiapkan oleh dapur.';
                        break;
                    case 'Selesai':
                        $icon = 'fa-check-circle';
                        $bgColor = 'bg-green-100';
                        $textColor = 'text-green-800';
                        $message = 'Pesanan Anda telah selesai dan akan diantar ke meja Anda.';
                        break;
                    case 'Dibatalkan':
                        $icon = 'fa-times-circle';
                        $bgColor = 'bg-red-100';
                        $textColor = 'text-red-800';
                        $message = 'Pesanan Anda dibatalkan.';
                        break;
                    case 'Menunggu Pembayaran':
                        $icon = 'fa-wallet';
                        $bgColor = 'bg-gray-100';
                        $textColor = 'text-gray-800';
                        $message = 'Silakan lakukan pembayaran dan upload bukti transfer.';
                        break;
                }
                ?>
            </div>


            <div class="status-card <?php echo $bgColor; ?> <?php echo $textColor; ?> p-6 rounded-xl mb-6">
                <div class="flex items-center">
                    <div class="bg-white p-3 rounded-full mr-4">
                        <i class="fas <?php echo $icon; ?> text-2xl <?php echo $textColor; ?>"></i>
                    </div>
                    <div>
                        <p class="font-bold text-lg"><?php echo $status; ?></p>
                        <p class="text-sm opacity-90"><?php echo $message; ?></p>
                    </div>
                </div>
                <?php if ($status == 'Dibatalkan' && !empty($catatanAdmin)): ?>
                    <div class="mt-4 pt-4 border-t border-red-200">
                        <p class="font-semibold text-red-800">Alasan Pembatalan:</p>
                        <p class="text-red-700"><?php echo nl2br($catatanAdmin); ?></p>
                    </div>
                <?php endif; ?>
            </div>


            <div class="mt-6 text-center space-y-4">
                <?php if ($status == 'Menunggu Pembayaran'): ?>
                    <a href="pembayaran.php?pesanan_id=<?php echo $pesanan_id; ?>"
                        class="inline-flex items-center px-6 py-3 bg-green-500 text-white font-medium rounded-full hover:bg-green-600 transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>
                        Lakukan Pembayaran
                    </a>
                <?php elseif ($status == 'Dibatalkan'): ?>
                    <a href="index.php"
                        class="inline-flex items-center px-6 py-3 bg-orange-500 text-white font-medium rounded-full hover:bg-orange-600 transition-colors">
                        <i class="fas fa-utensils mr-2"></i>
                        Buat Pesanan Baru
                    </a>
                <?php else: ?>
                    <p class="text-gray-500">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Halaman ini akan otomatis diperbarui setiap beberapa saat
                    </p>
                <?php endif; ?>
            </div>

            <hr class="my-6 border-orange-100">

            <div class="bg-orange-50 p-6 rounded-xl">
                <h4 class="text-xl font-semibold mb-4 text-gray-800">
                    <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>
                    Detail Pesanan
                </h4>
                <div class="space-y-3">
                    <?php foreach ($details as $item): ?>
                        <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-utensils text-orange-400 mr-3"></i>
                                <span class="text-gray-700">
                                    <?php echo htmlspecialchars($item['nama_menu_saat_pesan']); ?>
                                    <span class="text-orange-500 font-medium ml-2">x<?php echo $item['jumlah']; ?></span>
                                </span>
                            </div>
                            <span class="font-medium text-gray-900">
                                Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 pt-4 border-t-2 border-orange-200">
                    <div class="flex justify-between items-center text-xl font-bold">
                        <span class="text-gray-800">Total Pembayaran</span>
                        <span class="text-orange-500">
                            Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto refresh setiap 30 detik jika status bukan 'Selesai' atau 'Dibatalkan'
        <?php if (!in_array($status, ['Selesai', 'Dibatalkan'])): ?>
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        <?php endif; ?>
    </script>
</body>

</html>