<?php
$host = getenv('MYSQLHOST') ?: getenv('PGHOST');
$user = getenv('MYSQLUSER') ?: getenv('PGUSER');
$pass = getenv('MYSQLPASSWORD') ?: getenv('PGPASSWORD');
$db   = getenv('MYSQLDATABASE') ?: getenv('PGDATABASE');
$port = getenv('MYSQLPORT') ?: getenv('PGPORT');

// Cek apakah variabel terbaca atau kosong
if (!$host) {
    die("Error: Variabel environment Railway belum terbaca. Pastikan Variable References sudah terpasang.");
}

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>