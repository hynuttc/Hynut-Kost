
<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KAMAR
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Kamar tidak ditemukan.");
}

$kamar_id = intval($_GET['id']);

// ==========================================
// AMBIL DATA KAMAR SAAT INI
// ==========================================
$query = mysqli_query($conn, "SELECT * FROM kamar WHERE kamar_id = $kamar_id");
if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$data_kamar = mysqli_fetch_assoc($query);

if (!$data_kamar) {
    die("Data kamar tidak ditemukan di database.");
}

// AMBIL LIST KOST UNTUK DROPDOWN
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");

// ==========================================
// PROSES UPDATE KAMAR
// ==========================================
if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $no_kamar     = mysqli_real_escape_string($conn, trim($_POST['no_kamar']));
    $tipe_kamar   = mysqli_real_escape_string($conn, trim($_POST['tipe_kamar']));
    $harga        = intval($_POST['harga']);
    $status_kamar = mysqli_real_escape_string($conn, trim($_POST['status_kamar']));

    $update = "UPDATE kamar SET
                kos_id       = '$kos_id',
                no_kamar     = '$no_kamar',
                tipe_kamar   = '$tipe_kamar',
                harga        = '$harga',
                status_kamar = '$status_kamar'
               WHERE kamar_id = $kamar_id";

    if (mysqli_query($conn, $update)) {
        header("Location: kamar_owner.php");
        exit;
    } else {
        die("Gagal memperbarui data: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Kamar</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

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

* {
    box-sizing: border-box;
}

/* BACKGROUND */

body {
    margin: 0;
    background-color: var(--pink-bg);
    font-family: Arial, Helvetica, sans-serif;
    color: var(--text);
}

/* NAVBAR */

.navbar-custom {
    height: 70px;
    background: var(--pink);
    display: flex;
    align-items: center;
    box-shadow: 0 3px 10px rgba(217, 133, 163, 0.12);
}

.navbar-brand-custom {
    color: white;
    font-size: 25px;
    margin-left: 40px;
    font-weight: 600;
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
    font-size: 38px;
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
    font-size: 17px;
    font-weight: 600;
    color: var(--pink-dark);
    margin-bottom: 10px;
}

.form-control {
    height: 52px;
    font-size: 16px;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    padding: 10px 15px;
    color: var(--text);
    background-color: white;
}

.form-control:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.2);
}

/* TOMBOL */

.tombol {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--pink-border);
}

.btn {
    font-size: 16px;
    padding: 11px 28px;
    border-radius: 7px;
    font-weight: 600;
    transition: 0.3s;
}

/* TOMBOL SIMPAN */

.btn-simpan {
    background-color: var(--pink);
    border-color: var(--pink);
    color: white;
}

.btn-simpan:hover {
    background-color: var(--pink-dark);
    border-color: var(--pink-dark);
    color: white;
}

/* TOMBOL KEMBALI */

.btn-kembali {
    background-color: var(--pink-dark);
    border-color: var(--pink-dark);
    color: white;
}

.btn-kembali:hover {
    background-color: #C67594;
    border-color: #C67594;
    color: white;
}

/* RESPONSIF */

@media (max-width: 576px) {

    .navbar-brand-custom {
        margin-left: 20px;
        font-size: 22px;
    }

    .content {
        padding: 35px 18px;
    }

    .judul {
        font-size: 28px;
    }

    .form-group label {
        font-size: 15px;
    }

    .form-control {
        height: 48px;
        font-size: 15px;
    }

    .tombol {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .tombol .btn {
        width: 100%;
        margin-left: 0 !important;
    }
}

    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom">
    <div class="navbar-brand-custom">
        CRUD PHP
    </div>
</nav>

<!-- CONTENT -->
<div class="content">

    <!-- JUDUL -->
    <div class="judul">
        Edit Data Kamar
    </div>

    <div class="garis"></div>

    <!-- FORM EDIT KAMAR -->
    <form method="POST">

        <!-- PILIH KOST -->
        <div class="form-group">
            <label>Pilih Kost</label>
            <select name="kos_id" class="form-control" required>
                <?php while ($row_kos = mysqli_fetch_assoc($query_kos)): ?>
                    <option value="<?= $row_kos['kos_id']; ?>" <?= ($row_kos['kos_id'] == $data_kamar['kos_id']) ? 'selected' : ''; ?>>
                        <?= !empty($row_kos['nama_kos']) ? htmlspecialchars($row_kos['nama_kos']) : 'Kos ID: ' . $row_kos['kos_id']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- NO KAMAR -->
        <div class="form-group">
            <label>Nomor Kamar</label>
            <input type="text" name="no_kamar" class="form-control" value="<?= htmlspecialchars($data_kamar['no_kamar']); ?>" required>
        </div>

        <!-- TIPE KAMAR -->
        <div class="form-group">
            <label>Tipe Kamar</label>
            <input type="text" name="tipe_kamar" class="form-control" value="<?= htmlspecialchars($data_kamar['tipe_kamar']); ?>" required>
        </div>

        <!-- HARGA -->
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" value="<?= $data_kamar['harga']; ?>" required>
        </div>

        <!-- STATUS KAMAR -->
        <div class="form-group">
            <label>Status Kamar</label>
            <select name="status_kamar" class="form-control" required>
                <option value="Kosong" <?= ($data_kamar['status_kamar'] == 'Kosong') ? 'selected' : ''; ?>>Kosong</option>
                <option value="Terisi" <?= ($data_kamar['status_kamar'] == 'Terisi') ? 'selected' : ''; ?>>Terisi</option>
            </select>
        </div>

        <!-- BUTTON -->
        <div class="tombol">
            <button type="submit" name="simpan" class="btn btn-simpan">
                Simpan
            </button>

            <a href="kamar_owner.php" class="btn btn-kembali ml-2">
                Kembali
            </a>
        </div>

    </form>

</div>

</body>
</html>
