<?php include 'header.php'; ?>

<main>
    <!-- Bagian Data Siap Uji -->
    <section id="data-mining" class="py-16 md:py-24 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h3 class="text-2xl md:text-3xl font-bold text-slate-900">Data Mining - Data Siap Uji</h3>
                <p class="mt-2 text-md text-slate-600">Data berikut telah melalui tahap pra-pemrosesan dan siap untuk dianalisis menggunakan Algoritma C4.5.</p>
                 <div class="mt-6">
                    <a href="perhitungan_c45.php" class="bg-amber-500 text-white py-3 px-6 rounded-md font-semibold hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                        Lanjutkan ke Proses Perhitungan C4.5 &rarr;
                    </a>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                 <div class="mb-4">
                     <input type="text" id="searchInput" placeholder="Cari berdasarkan hari, waktu, atau kepadatan..." class="w-full p-2 border border-slate-300 rounded-md">
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kategori Hari</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kategori Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jumlah Kendaraan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tingkat Kepadatan</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody" class="bg-white divide-y divide-slate-200">
                            <!-- Data akan diisi oleh JavaScript dari tabel datasiap_uji -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
