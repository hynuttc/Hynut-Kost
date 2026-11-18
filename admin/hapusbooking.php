<?php
session_start();
require_once '../database.php';

// Ambil ID booking
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: booking.php");
    exit;
}

$id = (int) $_GET['id'];

// Hapus data booking
$stmt = mysqli_prepare($conn, "DELETE FROM booking WHERE booking_id = ?");

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    // Kembali ke halaman Booking Owner
    header("Location: booking.php");
    exit;
} else {
    echo "Gagal menghapus booking: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
?>