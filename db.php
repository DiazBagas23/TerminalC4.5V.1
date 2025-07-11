<?php
// db.php
// Konfigurasi untuk koneksi ke database
$servername = "localhost"; // Biasanya "localhost" atau sesuai dengan host Anda
$username = "root";      // Ganti dengan username database Anda
$password = "Bastard2329";          // Ganti dengan password database Anda
$dbname = "terminal_db"; // Nama database Anda

// Membuat koneksi menggunakan MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa apakah koneksi berhasil atau gagal
if ($conn->connect_error) {
  // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error dalam format teks biasa.
  // Ini lebih aman karena file ini di-include oleh halaman HTML.
  die("Koneksi database gagal: " . $conn->connect_error);
}

// Mengatur character set ke utf8 untuk mendukung berbagai karakter
$conn->set_charset("utf8");

?>
