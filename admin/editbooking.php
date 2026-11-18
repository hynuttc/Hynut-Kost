
<?php
session_start();
require_once '../database.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($booking_id <= 0) { 
    header("Location: booking.php"); 
    exit; 
}

$query_booking = mysqli_query($conn, "SELECT b.*, u.nama AS nama_user, k.no_kamar, kos.nama_kos 
                                      FROM booking b
                                      JOIN user u ON b.user_id = u.user_id
                                      JOIN kamar k ON b.kamar_id = k.kamar_id
                                      JOIN kos ON k.kos_id = kos.kos_id
                                      WHERE b.booking_id = '$booking_id'");
$booking = mysqli_fetch_assoc($query_booking);

if (!$booking) { 
    die("Data booking tidak ditemukan."); 
}

// Hanya memproses dan mengubah status_booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_booking = mysqli_real_escape_string($conn, $_POST['status_booking']);

    $update = "UPDATE booking SET status_booking = '$status_booking' WHERE booking_id = '$booking_id'";
    if (mysqli_query($conn, $update)) {
        header("Location: booking.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Booking - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
 /* =========================================
   TEMA SOFT BABY PINK PASTEL
   HALAMAN EDIT DATA BOOKING ADMIN
========================================= */

:root {
    --pink: #E8A1B8;
    --pink-dark: #D985A3;
    --pink-light: #FCEAF0;
    --pink-bg: #FFF7FA;
    --pink-border: #F5DCE5;
    --text: #51424A;
    --muted: #927F87;
}

/* DASAR */
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: var(--pink-bg);
    color: var(--text);
    font-family: 'Source Sans Pro', Arial, sans-serif;
}

/* CARD UTAMA */
.card-custom {
    width: 90%;
    max-width: 850px;
    margin: 40px auto;
    padding: 28px;
    background: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.10);
}

/* JUDUL */
.card-custom h2 {
    color: var(--pink-dark);
    font-size: 28px;
    font-weight: 600;
    padding-bottom: 15px;
    margin-bottom: 25px !important;
    border-bottom: 1px solid var(--pink-border);
}

/* LABEL */
.card-custom label {
    display: block;
    color: var(--pink-dark);
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 9px;
}

/* INPUT DAN SELECT */
.card-custom .form-control {
    width: 100%;
    min-height: 46px;
    padding: 10px 13px;
    color: var(--text);
    background-color: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    font-size: 15px;
    box-shadow: none;
    transition: 0.3s;
}

/* INPUT READONLY */
.card-custom .form-control[readonly] {
    background: #FFF7FA;
    color: var(--muted);
    cursor: default;
}

/* FOCUS INPUT */
.card-custom .form-control:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
    outline: none;
}

/* DROPDOWN STATUS */
.card-custom select.form-control {
    cursor: pointer;
}

/* TOMBOL SIMPAN */
.btn-simpan {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    padding: 10px 22px;
    background: var(--pink);
    color: #FFFFFF !important;
    border: 1px solid var(--pink);
    border-radius: 7px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    cursor: pointer;
    transition: 0.3s;
}

.btn-simpan:hover {
    background: var(--pink-dark);
    border-color: var(--pink-dark);
    color: #FFFFFF !important;
}

/* TOMBOL KEMBALI */
.btn-kembali {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    padding: 10px 22px;
    background: var(--pink-dark);
    color: #FFFFFF !important;
    border: 1px solid var(--pink-dark);
    border-radius: 7px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none !important;
    transition: 0.3s;
}

.btn-kembali:hover {
    background: #C67594;
    border-color: #C67594;
    color: #FFFFFF !important;
}

/* RESPONSIVE MOBILE */
@media (max-width: 768px) {
    .card-custom {
        width: 95%;
        margin: 20px auto;
        padding: 18px;
    }

    .card-custom h2 {
        font-size: 24px;
        margin-bottom: 20px !important;
    }

    .card-custom label {
        font-size: 15px;
    }

    .btn-simpan,
    .btn-kembali {
        min-width: 100px;
        padding: 9px 17px;
    }
}
    </style>
</head>
<body>

<div class="card-custom">
    <h2 class="mb-4">Edit Data Booking</h2>

    <form method="POST">
        <div class="form-group mb-3">
            <label>Nama Pemesan</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_user']); ?>" readonly>
        </div>

        <div class="form-group mb-3">
            <label>Kost & Kamar</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_kos']); ?> - Kamar <?= htmlspecialchars($booking['no_kamar']); ?>" readonly>
        </div>

        <div class="form-group mb-3">
            <label>Tanggal Survey</label>
            <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($booking['tanggal_survey'])); ?>" readonly>
        </div>

        <div class="form-group mb-4">
            <label>Status Booking</label>
            <select name="status_booking" class="form-control" required>
                <option value="Menunggu" <?= $booking['status_booking'] === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="Disetujui" <?= $booking['status_booking'] === 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                <option value="Ditolak" <?= $booking['status_booking'] === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>

        <div class="pt-2">
            <button type="submit" class="btn btn-simpan">Simpan</button>
            <a href="booking.php" class="btn btn-kembali ml-2">Kembali</a>
        </div>
    </form>
</div>

</body>
</html>
