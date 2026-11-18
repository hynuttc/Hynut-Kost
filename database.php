<?php
// Mengambil dari environment variables (untuk Cloud/Railway), 
// jika kosong, otomatis menggunakan konfigurasi XAMPP lokal.
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'hynutkost'; // Sesuaikan dengan nama database lokal Anda jika berbeda
$port = getenv('MYSQLPORT') ?: 3306;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    // Menampilkan pesan error asli dari MySQL agar terlihat di browser
    die("GAGAL KONEKSI MYSQL: " . mysqli_connect_error() . " | Host: " . $host . " | User: " . $user . " | Port: " . $port);
}

// Menyediakan juga alias variabel $koneksi dan $db agar kompatibel di semua file lain
$koneksi = $conn;
$db = $conn;

//echo "Koneksi Berhasil!";
?>