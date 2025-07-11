<?php
session_start();

include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// =================================================================
// FUNGSI-FUNGSI BANTU
// =================================================================

function get_kategori_hari($dateString) {
    $libur_nasional = ['2025-05-01', '2025-05-17'];
    if (in_array($dateString, $libur_nasional)) return 'Libur Nasional';
    $dayOfWeek = date('w', strtotime($dateString));
    return ($dayOfWeek == 0 || $dayOfWeek == 6) ? 'Akhir Pekan' : 'Hari Kerja';
}

function get_kategori_waktu($hour) {
    if ($hour >= 5 && $hour < 11) return 'Pagi';
    if ($hour >= 11 && $hour < 15) return 'Siang';
    return 'Sore';
}

function get_tingkat_kepadatan($count) {
    if ($count > 20) return 'Padat';
    if ($count > 10) return 'Sedang';
    return 'Sepi';
}

// =================================================================
// PROSES PRA-PEMROSESAN DATA
// =================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['preprocess'])) {
    $conn->query("TRUNCATE TABLE datasiap_uji");
    $result = $conn->query("SELECT tanggal, jam_kedatangan FROM datamentah");
    $hourly_counts = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hour_key = date('Y-m-d H', strtotime($row['tanggal'] . ' ' . $row['jam_kedatangan']));
            $hourly_counts[$hour_key] = ($hourly_counts[$hour_key] ?? 0) + 1;
        }
    }
    if (!empty($hourly_counts)) {
        $stmt = $conn->prepare("INSERT INTO datasiap_uji (waktu, kategori_hari, kategori_waktu, jumlah_kendaraan, tingkat_kepadatan) VALUES (?, ?, ?, ?, ?)");
        foreach ($hourly_counts as $waktu_full => $jumlah) {
            $waktu_dt = new DateTime($waktu_full . ':00:00');
            $waktu_db = $waktu_dt->format('Y-m-d H:i:s');
            $kategori_hari = get_kategori_hari($waktu_dt->format('Y-m-d'));
            $kategori_waktu = get_kategori_waktu((int)$waktu_dt->format('H'));
            $tingkat_kepadatan = get_tingkat_kepadatan($jumlah);
            $stmt->bind_param("sssis", $waktu_db, $kategori_hari, $kategori_waktu, $jumlah, $tingkat_kepadatan);
            $stmt->execute();
        }
        $stmt->close();
        $_SESSION['message'] = 'Pra-pemrosesan berhasil!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Tidak ada data mentah untuk diproses.';
        $_SESSION['message_type'] = 'error';
    }
    header("Location: proses_mining.php");
    exit();
}

// =================================================================
// FUNGSI-FUNGSI PERHITUNGAN C4.5
// =================================================================

$hasil_perhitungan = null;

function calculate_entropy($data) {
    if (empty($data)) return 0;
    $total = count($data);
    $classCounts = array_count_values(array_column($data, 'tingkat_kepadatan'));
    $entropy = 0;
    foreach ($classCounts as $count) {
        if ($count > 0) $entropy -= ($count / $total) * log($count / $total, 2);
    }
    return $entropy;
}

function calculate_gain($data, $attribute, $totalEntropy) {
    $total = count($data);
    $attributeValues = array_unique(array_column($data, $attribute));
    $weightedEntropy = 0;
    foreach ($attributeValues as $value) {
        $subset = array_filter($data, function($row) use ($attribute, $value) { return $row[$attribute] === $value; });
        $weightedEntropy += (count($subset) / $total) * calculate_entropy($subset);
    }
    return $totalEntropy - $weightedEntropy;
}

/**
 * PERBAIKAN: Fungsi rekursif untuk membangun struktur pohon keputusan.
 * @param array $data Data latih saat ini.
 * @param array $attributes Daftar atribut yang masih bisa digunakan.
 * @return mixed Array yang merepresentasikan node atau string nama kelas (leaf).
 */
function build_tree($data, $attributes) {
    $class_labels = array_column($data, 'tingkat_kepadatan');
    // Base case 1: Jika semua data dalam satu kelas, return kelas tersebut.
    if (count(array_unique($class_labels)) === 1) {
        return $class_labels[0];
    }
    // Base case 2: Jika tidak ada atribut lagi, return kelas mayoritas.
    if (empty($attributes)) {
        $counts = array_count_values($class_labels);
        arsort($counts);
        return key($counts);
    }

    $totalEntropy = calculate_entropy($data);
    $best_attribute = null;
    $max_gain = -1;

    foreach ($attributes as $attr) {
        $gain = calculate_gain($data, $attr, $totalEntropy);
        if ($gain > $max_gain) {
            $max_gain = $gain;
            $best_attribute = $attr;
        }
    }

    $tree = ['attribute' => $best_attribute, 'children' => []];
    $remaining_attributes = array_diff($attributes, [$best_attribute]);
    $attribute_values = array_unique(array_column($data, $best_attribute));

    foreach ($attribute_values as $value) {
        $subset = array_filter($data, function($row) use ($best_attribute, $value) { return $row[$best_attribute] === $value; });
        $tree['children'][$value] = build_tree($subset, $remaining_attributes);
    }

    return $tree;
}

/**
 * PERBAIKAN: Fungsi rekursif untuk merender HTML dari struktur pohon.
 * @param mixed $node Node pohon (array) atau nama kelas (string).
 * @return string String HTML.
 */
function render_tree_html($node) {
    // Jika node adalah leaf (hasil akhir)
    if (is_string($node)) {
        $color = 'text-slate-700';
        if ($node === 'Padat') $color = 'text-red-600';
        if ($node === 'Sedang') $color = 'text-amber-600';
        if ($node === 'Sepi') $color = 'text-green-600';
        return "<span class=\"font-bold $color\">" . strtoupper($node) . "</span>";
    }

    $html = "<ul>";
    $html .= "<li><span class=\"font-semibold text-slate-800\">" . ucwords(str_replace('_', ' ', $node['attribute'])) . "?</span>";
    $html .= "<ul>";
    foreach ($node['children'] as $value => $childNode) {
        $html .= "<li>";
        $html .= "<span class=\"font-medium text-slate-600\">" . htmlspecialchars($value) . " &rarr; </span>";
        $html .= render_tree_html($childNode);
        $html .= "</li>";
    }
    $html .= "</ul></li></ul>";
    return $html;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['calculate'])) {
    $result = $conn->query("SELECT kategori_hari, kategori_waktu, tingkat_kepadatan FROM datasiap_uji");
    $data_latih = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { $data_latih[] = $row; }
    }

    if (!empty($data_latih)) {
        $totalEntropy = calculate_entropy($data_latih);
        $attributes = ['kategori_hari', 'kategori_waktu'];
        $gains = [];
        foreach ($attributes as $attr) { $gains[$attr] = calculate_gain($data_latih, $attr, $totalEntropy); }
        arsort($gains);
        
        $hasil_perhitungan = [
            'total_entropy' => $totalEntropy,
            'gains' => $gains,
            // PERBAIKAN: Membangun struktur pohon dinamis
            'tree' => build_tree($data_latih, $attributes)
        ];
    } else {
        $hasil_perhitungan = ['error' => 'Data latih tidak ditemukan atau kosong.'];
    }
}

$data_siap_uji = $conn->query("SELECT * FROM datasiap_uji ORDER BY waktu DESC");
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message'], $_SESSION['message_type']);
}

include 'header.php';
?>

<main class="flex-grow">
    <section id="data-mining" class="py-16 md:py-24 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h3 class="text-2xl md:text-3xl font-bold text-slate-900">Proses Data Mining</h3>
                <p class="mt-2 text-md text-slate-600">Lakukan pra-pemrosesan data mentah, lalu lanjutkan dengan perhitungan C4.5.</p>
            </div>

            <!-- LANGKAH 1: PRA-PEMROSESAN -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h4 class="text-xl font-bold text-slate-900 mb-2">Langkah 1: Pra-pemrosesan Data</h4>
                <p class="text-slate-600 mb-4">Klik tombol ini untuk mengubah data dari tabel `datamentah` menjadi data agregat per jam yang siap dianalisis dan menyimpannya ke tabel `datasiap_uji`.</p>
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                <form action="proses_mining.php" method="POST">
                    <button type="submit" name="preprocess" class="w-full bg-slate-800 text-white py-2 px-4 rounded-md font-semibold hover:bg-slate-700">Proses Data Mentah ke Data Uji</button>
                </form>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Tabel Data Siap Uji -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h4 class="text-xl font-bold text-slate-900 mb-4">Data Latih (Hasil Pra-pemrosesan)</h4>
                    <div class="overflow-auto max-h-96">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Waktu</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Hari</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Waktu</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase">Kepadatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <?php if ($data_siap_uji && $data_siap_uji->num_rows > 0): ?>
                                    <?php while($row = $data_siap_uji->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-4 py-2 text-sm whitespace-nowrap"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($row['waktu']))); ?></td>
                                            <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($row['kategori_hari']); ?></td>
                                            <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($row['kategori_waktu']); ?></td>
                                            <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($row['tingkat_kepadatan']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center p-4">Data belum diproses. Klik tombol di atas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- LANGKAH 2: Perhitungan C4.5 -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h4 class="text-xl font-bold text-slate-900 mb-4">Langkah 2: Perhitungan C4.5</h4>
                    <form action="proses_mining.php#hasil" method="POST">
                        <button type="submit" name="calculate" class="w-full bg-amber-500 text-white py-2 px-4 rounded-md font-semibold hover:bg-amber-600">Mulai Perhitungan Gain</button>
                    </form>

                    <?php if ($hasil_perhitungan): ?>
                    <div id="hasil" class="mt-6 space-y-4">
                        <?php if (isset($hasil_perhitungan['error'])): ?>
                            <div class="alert alert-error"><?php echo $hasil_perhitungan['error']; ?></div>
                        <?php else: ?>
                            <div>
                                <h5 class="font-semibold mb-2">Hasil Perhitungan Gain</h5>
                                <div class="p-4 border rounded-lg bg-slate-50 space-y-2">
                                    <p><strong>Total Entropy (S):</strong> <?php echo number_format($hasil_perhitungan['total_entropy'], 4); ?></p>
                                    <?php foreach ($hasil_perhitungan['gains'] as $attr => $gain): ?>
                                    <div class="pt-2 border-t"><p><strong>Gain(<?php echo ucwords(str_replace('_', ' ', $attr)); ?>):</strong> <?php echo number_format($gain, 4); ?></p></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-semibold mb-2">Visualisasi Pohon Keputusan</h5>
                                <div class="p-4 border rounded-lg bg-slate-50 rule-tree">
                                    <?php 
                                        // PERBAIKAN: Merender pohon keputusan secara dinamis
                                        echo render_tree_html($hasil_perhitungan['tree']); 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php 
if ($conn) { $conn->close(); }
include 'footer.php'; 
?>
