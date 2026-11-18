<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

// ========================================
// MENYESUAIKAN KONEKSI DATABASE SECARA FLEKSIBEL
// ========================================
if (!isset($conn) || !($conn instanceof mysqli)) {
    if (isset($db) && ($db instanceof mysqli)) {
        $conn = $db;
    } elseif (isset($koneksi) && ($koneksi instanceof mysqli)) {
        $conn = $koneksi;
    }
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}

if (isset($_POST['submit_review'])) {
    $booking_id = $_POST['booking_id'];
    $rating     = $_POST['rating'];
    $komentar   = mysqli_real_escape_string($conn, $_POST['komentar']);

    // INSERT data ke tabel review
    $query = "INSERT INTO review (booking_id, rating, komentar, dibuat_pada) 
              VALUES ('$booking_id', '$rating', '$komentar', NOW())";

    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>
                alert('Ulasan berhasil dikirim!');
                window.location.href='profil.php';
              </script>";
    } else {
        echo "Gagal menyimpan ulasan: " . mysqli_error($conn);
    }
}
?>