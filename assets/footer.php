    <?php
// Mulai session di setiap halaman admin
session_start();

// Cek apakah user sudah login, jika belum, lempar ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Warung Kak Su</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-800 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-xl font-bold">Warung Kak Su</a>
            <div>
                <a href="menu.php" class="px-3 py-2 rounded hover:bg-blue-700">Kelola Menu</a>
                <a href="pesanan.php" class="px-3 py-2 rounded hover:bg-blue-700">Pesanan</a>
                <a href="../logout.php" class="ml-4 bg-red-500 px-3 py-2 rounded hover:bg-red-600">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto p-4">
        ```

**File: `admin/footer.php`**
```php
        </div>
</body>
</html>