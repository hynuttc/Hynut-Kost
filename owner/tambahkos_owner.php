<?php
include '../database.php';

/* =====================================================
   PROSES SIMPAN DATA KOST
   ===================================================== */

if (isset($_POST['simpan'])) {

    $owner_id          = mysqli_real_escape_string($conn, $_POST['owner_id']);
    $nama_kos          = mysqli_real_escape_string($conn, $_POST['nama_kos']);
    $alamat            = mysqli_real_escape_string($conn, $_POST['alamat']);
    $latitude          = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude         = mysqli_real_escape_string($conn, $_POST['longitude']);
    $tipe_kos          = mysqli_real_escape_string($conn, $_POST['tipe_kos']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);

    /* =================================================
       SIMPAN KE DATABASE TABEL KOS
       ================================================= */

    $query = "INSERT INTO kos 
              (owner_id, nama_kos, alamat, latitude, longitude, tipe_kos, status_verifikasi)
              VALUES 
              ('$owner_id', '$nama_kos', '$alamat', '$latitude', '$longitude', '$tipe_kos', '$status_verifikasi')";

    if (mysqli_query($conn, $query)) {

        header("Location: kosowner.php");
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
    <title>Tambah Data Kost</title>

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
        color: var(--text);
        font-family: Arial, Helvetica, sans-serif;
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
        height: 52px;
        font-size: 17px;
        border: 1px solid var(--pink-border);
        border-radius: 8px;
        padding: 10px 15px;
        background: #FFFFFF;
        color: var(--text);
        transition: 0.3s;
    }

    textarea.form-control {
        height: auto;
        min-height: 100px;
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
        Tambah Data Kost
    </div>

    <div class="garis"></div>

    <!-- FORM TAMBAH KOST -->
    <form method="POST">

        <!-- OWNER ID -->
        <div class="form-group">
            <label>ID Owner (Pemilik)</label>
            <input type="number" name="owner_id" class="form-control" placeholder="Masukkan ID Owner" required>
        </div>

        <!-- NAMA KOS -->
        <div class="form-group">
            <label>Nama Kost</label>
            <input type="text" name="nama_kos" class="form-control" placeholder="Masukkan nama kost" required>
        </div>

        <!-- ALAMAT -->
        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap kost" required></textarea>
        </div>

        <!-- LATITUDE -->
        <div class="form-group">
            <label>Latitude</label>
            <input type="text" name="latitude" class="form-control" placeholder="Contoh: -6.200000">
        </div>

        <!-- LONGITUDE -->
        <div class="form-group">
            <label>Longitude</label>
            <input type="text" name="longitude" class="form-control" placeholder="Contoh: 106.816666">
        </div>

        <!-- TIPE KOS -->
        <div class="form-group">
            <label>Tipe Kost</label>
            <select name="tipe_kos" class="form-control" required>
                <option value="">-- Pilih Tipe Kost --</option>
                <option value="Putra">Putra</option>
                <option value="Putri">Putri</option>
                <option value="Campur">Campur</option>
            </select>
        </div>

       <!-- STATUS VERIFIKASI -->
<div class="form-group">
    <label>Status Verifikasi</label>

    <select name="status_verifikasi" class="form-control" required>

        <option value="Menunggu">Menunggu</option>

        <option value="Aktif">Aktif</option>

        <option value="Non-aktif">Non-aktif</option>

    </select>
</div>

        <!-- BUTTON -->
        <div class="tombol">
            <button type="submit" name="simpan" class="btn btn-tambah">
                Tambah
            </button>

            <a href="kosowner.php" class="btn btn-kembali ml-2">
                Kembali
            </a>
        </div>

    </form>

</div>

</body>
</html>