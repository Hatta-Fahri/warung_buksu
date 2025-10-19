</div> </div> <script>
        // Menunggu sampai seluruh halaman (DOM) selesai dimuat
        document.addEventListener('DOMContentLoaded', function() {
            
            // Cari tombol hamburger berdasarkan ID
            const sidebarToggle = document.getElementById('sidebar-toggle');
            
            // Cari sidebar berdasarkan ID
            const sidebar = document.getElementById('sidebar');

            // Cek apakah tombolnya ada
            if (sidebarToggle) {
                // Tambahkan event 'click' pada tombol
                sidebarToggle.addEventListener('click', function() {
                    // Toggle (tambah/hapus) class '-translate-x-full' pada sidebar
                    // Class ini yang membuatnya bergeser ke luar/dalam layar
                    sidebar.classList.toggle('-translate-x-full');
                });
            }
        });
    </script>
    
</body>
</html>