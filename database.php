<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    // Menampilkan pesan error asli dari MySQL agar terlihat di browser
    die("GAGAL KONEKSI MYSQL: " . mysqli_connect_error() . " | Host: " . $host . " | User: " . $user . " | Port: " . $port);
}
//echo "Koneksi Berhasil!";
?>