       <section id="olah-data" class="py-16 md:py-24 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h3 class="text-2xl md:text-3xl font-bold text-slate-900">Manajemen Data Mentah</h3>
                    <p class="mt-2 text-md text-slate-600">Mengelola data kedatangan kendaraan yang menjadi dasar analisis.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold">Tabel Data Mentah</h4>
                        <button id="add-raw-data-btn" class="bg-slate-800 text-white py-2 px-4 rounded-md font-semibold hover:bg-slate-700 transition-colors text-sm">Tambah Data</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jam</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plat Nomor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Perusahaan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="rawDataTableBody" class="bg-white divide-y divide-slate-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
        