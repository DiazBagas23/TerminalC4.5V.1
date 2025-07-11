<?php
session_start();

include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$message = '';
$message_type = '';

// Proses Tambah / Update Data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $id = $_POST['id_datamentah'];
    $tanggal = $_POST['tanggal'];
    $jam = $_POST['jam_kedatangan'];
    $plat = $_POST['plat_nomor'];
    $perusahaan = $_POST['nama_perusahaan'];
    $kategori = $_POST['kategori_kendaraan'];
    $model = $_POST['model_kendaraan'];

    if (empty($id)) { // Tambah data baru
        $stmt = $conn->prepare("INSERT INTO datamentah (tanggal, jam_kedatangan, plat_nomor, nama_perusahaan, kategori_kendaraan, model_kendaraan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $tanggal, $jam, $plat, $perusahaan, $kategori, $model);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Data berhasil ditambahkan!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal menambahkan data: ' . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
    } else { // Update data
        $stmt = $conn->prepare("UPDATE datamentah SET tanggal=?, jam_kedatangan=?, plat_nomor=?, nama_perusahaan=?, kategori_kendaraan=?, model_kendaraan=? WHERE id_datamentah=?");
        $stmt->bind_param("ssssssi", $tanggal, $jam, $plat, $perusahaan, $kategori, $model, $id);
         if ($stmt->execute()) {
            $_SESSION['message'] = 'Data berhasil diperbarui!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Gagal memperbarui data: ' . $stmt->error;
            $_SESSION['message_type'] = 'error';
        }
    }
    $stmt->close();
    header("Location: data_mentah.php");
    exit();
}

// Proses Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM datamentah WHERE id_datamentah = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Data berhasil dihapus!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Gagal menghapus data.';
        $_SESSION['message_type'] = 'error';
    }
    $stmt->close();
    header("Location: data_mentah.php");
    exit();
}

// Ambil data untuk ditampilkan di tabel
$data_mentah = $conn->query("SELECT * FROM datamentah ORDER BY tanggal DESC, jam_kedatangan DESC");

// Ambil data untuk form edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM datamentah WHERE id_datamentah = $id");
    $edit_data = $result->fetch_assoc();
}

// Tampilkan pesan notifikasi
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

include 'header.php';
?>

<main class="flex-grow">
    <section id="olah-data" class="py-16 md:py-24 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form Tambah/Ubah Data -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="text-xl font-bold text-slate-900 mb-4"><?php echo $edit_data ? 'Ubah' : 'Tambah'; ?> Data Mentah</h3>
                        <form action="data_mentah.php" method="POST">
                            <input type="hidden" name="id_datamentah" value="<?php echo $edit_data['id_datamentah'] ?? ''; ?>">
                            <div class="space-y-4">
                                <div><label for="tanggal" class="block text-sm font-medium">Tanggal</label><input type="date" name="tanggal" class="mt-1 w-full rounded-md border-slate-300" value="<?php echo $edit_data['tanggal'] ?? ''; ?>" required></div>
                                <div><label for="jam_kedatangan" class="block text-sm font-medium">Jam</label><input type="time" name="jam_kedatangan" class="mt-1 w-full rounded-md border-slate-300" value="<?php echo $edit_data['jam_kedatangan'] ?? ''; ?>" required></div>
                                <div><label for="plat_nomor" class="block text-sm font-medium">Plat Nomor</label><input type="text" name="plat_nomor" class="mt-1 w-full rounded-md border-slate-300" value="<?php echo $edit_data['plat_nomor'] ?? ''; ?>" required></div>
                                <div><label for="nama_perusahaan" class="block text-sm font-medium">Perusahaan</label><input type="text" name="nama_perusahaan" class="mt-1 w-full rounded-md border-slate-300" value="<?php echo $edit_data['nama_perusahaan'] ?? ''; ?>"></div>
                                <div><label for="kategori_kendaraan" class="block text-sm font-medium">Kategori</label><select name="kategori_kendaraan" class="mt-1 w-full rounded-md border-slate-300"><option <?php echo ($edit_data['kategori_kendaraan'] ?? '') == 'AKDP' ? 'selected' : ''; ?>>AKDP</option><option <?php echo ($edit_data['kategori_kendaraan'] ?? '') == 'AKAP' ? 'selected' : ''; ?>>AKAP</option></select></div>
                                <div><label for="model_kendaraan" class="block text-sm font-medium">Model</label><select name="model_kendaraan" class="mt-1 w-full rounded-md border-slate-300"><option <?php echo ($edit_data['model_kendaraan'] ?? '') == 'Minibus' ? 'selected' : ''; ?>>Minibus</option><option <?php echo ($edit_data['model_kendaraan'] ?? '') == 'Bus Sedang' ? 'selected' : ''; ?>>Bus Sedang</option><option <?php echo ($edit_data['model_kendaraan'] ?? '') == 'Bus Besar' ? 'selected' : ''; ?>>Bus Besar</option></select></div>
                            </div>
                            <div class="mt-6 flex justify-end space-x-3">
                                <?php if ($edit_data): ?><a href="data_mentah.php" class="bg-slate-200 text-slate-700 py-2 px-4 rounded-md hover:bg-slate-300">Batal</a><?php endif; ?>
                                <button type="submit" name="save" class="bg-slate-800 text-white py-2 px-4 rounded-md hover:bg-slate-700">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Data Mentah -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <h3 class="text-xl font-bold text-slate-900 mb-4">Daftar Data Mentah</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Jam</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Plat</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-200">
                                    <?php if ($data_mentah->num_rows > 0): ?>
                                        <?php while($row = $data_mentah->fetch_assoc()): ?>
                                            <tr>
                                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['jam_kedatangan']); ?></td>
                                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($row['plat_nomor']); ?></td>
                                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                                    <a href="data_mentah.php?edit=<?php echo $row['id_datamentah']; ?>" class="text-amber-600 hover:text-amber-900">Ubah</a>
                                                    <a href="data_mentah.php?delete=<?php echo $row['id_datamentah']; ?>" class="text-red-600 hover:text-red-900 ml-4" onclick="return confirm('Anda yakin ingin menghapus data ini?');">Hapus</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center p-4">Tidak ada data.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php 
$conn->close();
include 'footer.php'; 
?>
