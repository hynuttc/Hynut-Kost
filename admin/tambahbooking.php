<?php
include '../database.php';

/* =====================================================
   AMBIL DATA KAMAR & USER UNTUK DROPDOWN
   ===================================================== */
$query_kamar = mysqli_query($conn, "SELECT * FROM kamar ORDER BY kamar_id ASC");
$query_user  = mysqli_query($conn, "SELECT * FROM user ORDER BY user_id ASC");

/* =====================================================
   PROSES SIMPAN BOOKING
   ===================================================== */
if (isset($_POST['simpan'])) {

    $kamar_id       = intval($_POST['kamar_id']);
    $user_id        = intval($_POST['user_id']);
    $tanggal_survey = mysqli_real_escape_string($conn, $_POST['tanggal_survey']);
    $status_booking = mysqli_real_escape_string($conn, $_POST['status_booking']);
    $dibuat_pada    = date('Y-m-d H:i:s');

    /* =================================================
       SIMPAN KE DATABASE TABEL BOOKING
       ================================================= */
    $query = "INSERT INTO booking (kamar_id, user_id, tanggal_survey, status_booking, dibuat_pada)
              VALUES ('$kamar_id', '$user_id', '$tanggal_survey', '$status_booking', '$dibuat_pada')";

    if (mysqli_query($conn, $query)) {
        header("Location: booking.php");
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
    <title>Tambah Data Booking</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>

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
    background: var(--pink-bg) !important;
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
    margin: 0 auto;
    padding: 55px 20px 50px;
}

/* JUDUL */
.judul {
    font-size: 42px;
    font-weight: 600;
    color: var(--pink-dark) !important;
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
    color: var(--text) !important;
    margin-bottom: 8px;
    font-weight: 600;
}

.form-control {
    width: 100%;
    height: 52px;
    font-size: 17px;
    border: 1px solid var(--pink-border) !important;
    border-radius: 10px !important;
    padding: 10px 15px;
    background: #FFFFFF !important;
    color: var(--text) !important;
    transition: 0.3s;
}

.form-control:focus {
    border-color: var(--pink) !important;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.20) !important;
}

/* TOMBOL */
.tombol {
    margin-top: 30px;
}

.btn {
    font-size: 18px;
    padding: 11px 25px;
    border-radius: 25px !important;
    transition: 0.3s;
}

/* TOMBOL TAMBAH */
.btn-tambah {
    background: var(--pink) !important;
    border: 1px solid var(--pink) !important;
    color: #FFFFFF !important;
}

.btn-tambah:hover {
    background: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
    color: #FFFFFF !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(217, 133, 163, 0.20);
}

/* TOMBOL KEMBALI */
.btn-kembali {
    background: var(--pink-light) !important;
    border: 1px solid var(--pink-border) !important;
    color: #926A7A !important;
}

.btn-kembali:hover {
    background: #F1DCE4 !important;
    border-color: #E8C5D3 !important;
    color: #76505F !important;
    transform: translateY(-2px);
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
        Tambah Data Booking
    </div>

    <div class="garis"></div>

    <!-- FORM TAMBAH BOOKING -->
    <form method="POST">

        <!-- PILIH KAMAR -->
        <div class="form-group">
            <label>Pilih Kamar</label>
            <select name="kamar_id" class="form-control" required>
                <option value="">-- Pilih Kamar --</option>
                <?php while ($row_kamar = mysqli_fetch_assoc($query_kamar)): ?>
                    <option value="<?= $row_kamar['kamar_id']; ?>">
                        <?= !empty($row_kamar['no_kamar']) ? 'Kamar ' . htmlspecialchars($row_kamar['no_kamar']) : 'Kamar ID: ' . $row_kamar['kamar_id']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- PILIH USER -->
        <div class="form-group">
            <label>Pilih User</label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Pilih User --</option>
                <?php while ($row_user = mysqli_fetch_assoc($query_user)): ?>
                    <option value="<?= $row_user['user_id']; ?>">
                        <?= htmlspecialchars($row_user['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- TANGGAL SURVEY -->
        <div class="form-group">
            <label>Tanggal Survey</label>
            <input type="date" name="tanggal_survey" class="form-control" required>
        </div>

        <!-- STATUS BOOKING -->
        <div class="form-group">
            <label>Status Booking</label>
            <select name="status_booking" class="form-control" required>
                <option value="Menunggu">Menunggu</option>
                <option value="Diterima">Diterima</option>
                <option value="Ditolak">Ditolak</option>
                <option value="Batal">Batal</option>
            </select>
        </div>

        <!-- BUTTON -->
        <div class="tombol">
            <button type="submit" name="simpan" class="btn btn-tambah">
                Tambah
            </button>

            <a href="booking.php" class="btn btn-kembali ml-2">
                Kembali
            </a>
        </div>

    </form>

</div>

</body>
</html>