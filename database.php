<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// Jika host kosong atau bernilai lokal, fallback ke localhost
if (empty($host)) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'hynutkost'; // sesuaikan nama database lokalmu
    $port = '3306';
}

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>