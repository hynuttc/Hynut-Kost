<?php
$host = "hayabusa.proxy.rlwy.net"; // Ganti dengan host publik Railway Anda
$port = "27848";                  // Masukkan port publik Railway Anda
$user = "root";
$password = "rXsUCHFAYLdIgTmSnYfBQ1OWiAowdyVA";
$database = "railway";

// Tambahkan port pada fungsi mysqli_connect
$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}