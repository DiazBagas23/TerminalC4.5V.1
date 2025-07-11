<?php
session_start();
include 'db.php';

$error = '';

// Jika pengguna sudah login, alihkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Proses form login saat tombol ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; // Password dari form

    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        // PERBAIKAN: Query sederhana tanpa hash
        // Query ini akan mencari user dengan username DAN password yang cocok
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Jika user ditemukan, login berhasil
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            // Jika tidak ada user yang cocok
            $error = 'Username atau password salah.';
        }
        $stmt->close();
    }
}
$conn->close();
include 'header.php';
?>

<main class="flex-grow flex items-center justify-center bg-slate-50 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-error mb-4"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                    <input type="text" name="username" id="username" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm" required>
                </div>
                <div>
                    <button type="submit" class="w-full bg-slate-800 text-white py-2 px-4 rounded-md font-semibold hover:bg-slate-700">Login</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
