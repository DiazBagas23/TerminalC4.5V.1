<?php
session_start();
include 'db.php';

// 1. Ambil data untuk KPI Cards dan Charts
$sql = "SELECT tingkat_kepadatan, kategori_hari FROM datasiap_uji";
$result = $conn->query($sql);

$kpi = ['Padat' => 0, 'Sedang' => 0, 'Sepi' => 0];
$barChartData = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $kpi[$row['tingkat_kepadatan']]++;
        if (!isset($barChartData[$row['kategori_hari']])) {
            $barChartData[$row['kategori_hari']] = ['Padat' => 0, 'Sedang' => 0, 'Sepi' => 0];
        }
        $barChartData[$row['kategori_hari']][$row['tingkat_kepadatan']]++;
    }
}

// 2. Logika untuk Simulasi
$prediction_result = null;
$prediction_color = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['predict'])) {
    $hari = $_POST['sim-hari'];
    $waktu = $_POST['sim-waktu'];
    
    if ($waktu === 'Pagi' || $waktu === 'Siang') {
        $prediction_result = "Padat"; $prediction_color = "text-red-600";
    } elseif ($waktu === 'Sore' && $hari === 'Akhir Pekan') {
        $prediction_result = "Sedang"; $prediction_color = "text-amber-600";
    } else {
        $prediction_result = "Sepi"; $prediction_color = "text-green-600";
    }
}

include 'header.php';
?>

<main class="flex-grow">
    <section class="py-16 md:py-24 bg-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-5xl font-bold text-slate-900 leading-tight">Klasifikasi Kepadatan Terminal Lubuk Pakam</h2>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">Aplikasi web interaktif untuk analisis dan prediksi kepadatan terminal menggunakan Algoritma C4.5.</p>
        </div>
    </section>

    <section id="dashboard" class="py-16 md:py-24">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12"><h3 class="text-2xl md:text-3xl font-bold text-slate-900">Dasbor Analisis</h3></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500"><h4 class="text-sm font-medium text-slate-500">Total Jam Padat</h4><p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $kpi['Padat']; ?></p></div>
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-amber-500"><h4 class="text-sm font-medium text-slate-500">Total Jam Sedang</h4><p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $kpi['Sedang']; ?></p></div>
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500"><h4 class="text-sm font-medium text-slate-500">Total Jam Sepi</h4><p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $kpi['Sepi']; ?></p></div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md"><h4 class="text-lg font-semibold text-slate-800 mb-4 text-center">Distribusi Kepadatan</h4><div class="chart-container"><canvas id="densityPieChart"></canvas></div></div>
                <div class="bg-white p-6 rounded-lg shadow-md"><h4 class="text-lg font-semibold text-slate-800 mb-4 text-center">Kepadatan per Hari</h4><div class="chart-container"><canvas id="densityBarChart"></canvas></div></div>
            </div>
        </div>
    </section>

    <section id="simulasi" class="py-16 md:py-24 bg-slate-50">
         <div class="container mx-auto px-4">
            <div class="text-center mb-12"><h3 class="text-2xl md:text-3xl font-bold text-slate-900">Simulasi Prediksi</h3></div>
            <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
                <form action="index.php#simulasi" method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="sim-hari" class="block text-sm font-medium">Kategori Hari</label><select name="sim-hari" id="sim-hari" class="mt-1 block w-full rounded-md border-slate-300"><option>Hari Kerja</option><option>Akhir Pekan</option><option>Libur Nasional</option></select></div>
                        <div><label for="sim-waktu" class="block text-sm font-medium">Kategori Waktu</label><select name="sim-waktu" id="sim-waktu" class="mt-1 block w-full rounded-md border-slate-300"><option>Pagi</option><option>Siang</option><option>Sore</option></select></div>
                    </div>
                    <div class="mt-6"><button type="submit" name="predict" class="w-full bg-slate-800 text-white py-3 px-4 rounded-md font-semibold hover:bg-slate-700">Prediksi</button></div>
                </form>
                <?php if ($prediction_result): ?>
                <div class="mt-6 text-center"><h4 class="font-medium text-slate-600">Hasil Prediksi:</h4><p class="text-2xl font-bold mt-1 <?php echo $prediction_color; ?>"><?php echo $prediction_result; ?></p></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pieCtx = document.getElementById('densityPieChart');
    if (pieCtx) { new Chart(pieCtx, { type: 'doughnut', data: { labels: <?php echo json_encode(array_keys($kpi)); ?>, datasets: [{ data: <?php echo json_encode(array_values($kpi)); ?>, backgroundColor: ['#ef4444', '#f59e0b', '#22c55e'] }] }, options: { responsive: true } }); }
    const barCtx = document.getElementById('densityBarChart');
    if (barCtx) {
        const barLabels = <?php echo json_encode(array_keys($barChartData)); ?>;
        const barData = <?php echo json_encode($barChartData); ?>;
        new Chart(barCtx, { type: 'bar', data: { labels: barLabels, datasets: [ { label: 'Padat', data: barLabels.map(l => barData[l]['Padat']), backgroundColor: '#ef4444' }, { label: 'Sedang', data: barLabels.map(l => barData[l]['Sedang']), backgroundColor: '#f59e0b' }, { label: 'Sepi', data: barLabels.map(l => barData[l]['Sepi']), backgroundColor: '#22c55e' } ] }, options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } } });
    }
});
</script>

<?php 
$conn->close();
include 'footer.php'; 
?>
