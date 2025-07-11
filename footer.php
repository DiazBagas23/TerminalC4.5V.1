<?php // footer.php ?>
    <footer class="bg-slate-800 text-white mt-auto">
        <div class="container mx-auto px-4 py-6 text-center text-sm">
            <p>&copy; 2025 Diaz Bagas Admaja Tanjung. Aplikasi Web Interaktif untuk Tugas Akhir STMIK Pelita Nusantara.</p>
        </div>
    </footer>
    <script>
        // Skrip kecil untuk menandai link navigasi yang aktif
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
