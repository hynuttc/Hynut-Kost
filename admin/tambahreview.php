<?php
include '../database.php';

/* =====================================================
   AMBIL DATA BOOKING UNTUK DROPDOWN
   ===================================================== */
$query_booking = mysqli_query($conn, "SELECT * FROM booking ORDER BY booking_id ASC");

/* =====================================================
   PROSES SIMPAN REVIEW
   ===================================================== */
if (isset($_POST['simpan'])) {

    $booking_id  = intval($_POST['booking_id']);
    $rating      = intval($_POST['rating']);
    $komentar    = mysqli_real_escape_string($conn, $_POST['komentar']);
    $dibuat_pada = date('Y-m-d H:i:s');

    /* =================================================
       SIMPAN KE DATABASE TABEL REVIEW
       ================================================= */
    $query = "INSERT INTO review (booking_id, rating, komentar, dibuat_pada)
              VALUES ('$booking_id', '$rating', '$komentar', '$dibuat_pada')";

    if (mysqli_query($conn, $query)) {
        header("Location: review.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Review</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


<style>
    /* =========================================
       TEMA SOFT BABY PINK PASTEL
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
    body {
        margin: 0;
        background: var(--pink-bg);
        font-family: Arial, Helvetica, sans-serif;
        color: var(--text);
    }

    /* NAVBAR */
        .navbar-custom {
            height: 70px;
            background: linear-gradient(90deg, #F8DCE6, #FCEAF0);
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--pink-border);
            box-shadow: 0 3px 12px rgba(217, 133, 163, 0.08);
        }

        .navbar-brand-custom {
            color: #B96F8A;
            font-size: 25px;
            margin-left: 40px;
            font-weight: 700;
            text-decoration: none;
        }

        .navbar-brand-custom:hover {
            color: var(--pink-dark);
            text-decoration: none;
        }

    /* CONTENT */
    .content {
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
        padding: 55px 20px 50px;
    }

    /* JUDUL */
    .judul {
        font-size: 42px;
        font-weight: 600;
        color: var(--pink-dark);
        margin-bottom: 20px;
    }

    .garis {
        border-top: 1px solid var(--pink-border);
        margin-bottom: 30px;
    }

    /* FORM */
    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        font-size: 18px;
        color: var(--text);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-control {
        font-size: 17px;
        border: 1px solid var(--pink-border);
        border-radius: 8px;
        padding: 10px 15px;
        background: #FFFFFF;
        color: var(--text);
        transition: 0.3s;
    }

    select.form-control {
        height: 52px;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: var(--pink);
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.20);
        background: #FFFFFF;
    }

    /* TOMBOL */
    .tombol {
        margin-top: 30px;
    }

    .btn {
        font-size: 18px;
        padding: 11px 25px;
        border-radius: 20px;
        transition: 0.3s;
    }

    /* TOMBOL TAMBAH */
    .btn-tambah {
        background: var(--pink);
        border: 1px solid var(--pink);
        color: #FFFFFF;
    }

    .btn-tambah:hover {
        background: var(--pink-dark);
        border-color: var(--pink-dark);
        color: #FFFFFF;
        transform: translateY(-1px);
    }

    /* TOMBOL KEMBALI */
    .btn-kembali {
        background: var(--pink-light);
        border: 1px solid var(--pink-border);
        color: #B96F8A;
    }

    .btn-kembali:hover {
        background: #F1DCE4;
        border-color: #F1DCE4;
        color: #926A7A;
        transform: translateY(-1px);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .content {
            padding: 35px 18px;
        }

        .judul {
            font-size: 30px;
        }

        .navbar-brand-custom {
            margin-left: 20px;
            font-size: 22px;
        }
    }

    @media (max-width: 480px) {
        .judul {
            font-size: 26px;
        }

        .btn {
            font-size: 16px;
            padding: 10px 20px;
        }
    }
</style>


</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom">
    <a href="index.php" class="navbar-brand-custom">
        <i class="fas fa-home"></i> Hynut KOST</a>
</nav>


<!-- CONTENT -->
<div class="content">

    <!-- JUDUL -->
    <div class="judul">
        Tambah Data Review
    </div>

    <div class="garis"></div>

    <!-- FORM TAMBAH REVIEW -->
    <form method="POST">

        <!-- PILIH BOOKING -->
        <div class="form-group">
            <label>Pilih Booking</label>
            <select name="booking_id" class="form-control" required>
                <option value="">-- Pilih Booking ID --</option>
                <?php while ($row_booking = mysqli_fetch_assoc($query_booking)): ?>
                    <option value="<?= $row_booking['booking_id']; ?>">
                        Booking #<?= $row_booking['booking_id']; ?> (Tgl Survey: <?= $row_booking['tanggal_survey']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- RATING -->
        <div class="form-group">
            <label>Rating (1 - 5)</label>
            <select name="rating" class="form-control" required>
                <option value="">-- Pilih Rating --</option>
                <option value="5">5 - Sangat Puas</option>
                <option value="4">4 - Puas</option>
                <option value="3">3 - Cukup</option>
                <option value="2">2 - Kurang</option>
                <option value="1">1 - Sangat Kurang</option>
            </select>
        </div>

        <!-- KOMENTAR -->
        <div class="form-group">
            <label>Komentar</label>
            <textarea name="komentar" class="form-control" rows="4" placeholder="Masukkan ulasan atau komentar" required></textarea>
        </div>

        <!-- BUTTON -->
        <div class="tombol">
            <button type="submit" name="simpan" class="btn btn-tambah">
                Tambah
            </button>

            <a href="review.php" class="btn btn-kembali ml-2">
                Kembali
            </a>
        </div>

    </form>

</div>

</body>
</html>