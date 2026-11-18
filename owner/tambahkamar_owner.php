<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* AMBIL DATA KOST */
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");

if (!$query_kos) {
    die("Query Kos Error: " . mysqli_error($conn));
}

/* PROSES SIMPAN KAMAR */
if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $no_kamar     = mysqli_real_escape_string($conn, trim($_POST['no_kamar']));
    $tipe_kamar   = mysqli_real_escape_string($conn, trim($_POST['tipe_kamar']));
    $harga        = intval($_POST['harga']);
    $status_kamar = mysqli_real_escape_string($conn, trim($_POST['status_kamar']));

    $query = "INSERT INTO kamar
              (kos_id, no_kamar, tipe_kamar, harga, status_kamar)
              VALUES
              ('$kos_id', '$no_kamar', '$tipe_kamar', '$harga', '$status_kamar')";

    if (mysqli_query($conn, $query)) {
        header("Location: kamar_owner.php");
        exit;
    } else {
        die("Gagal menyimpan data kamar: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kamar - Hynut KOST</title>

    <!-- FONT POPPINS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- BOOTSTRAP -->
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
    --white: #FFFFFF;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* DASAR */
body {
    font-family: 'Poppins', sans-serif;
    background: var(--pink-bg);
    color: var(--text);
    min-height: 100vh;
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
    margin: 35px auto;
    padding: 0 25px 50px;
}

/* JUDUL */
.judul {
    font-size: 28px;
    font-weight: 700;
    color: var(--pink-dark);
    margin-bottom: 10px;
}

.judul i {
    color: var(--pink);
}

.garis {
    border-top: 1px solid var(--pink-border);
    margin-bottom: 30px;
}

/* FORM CARD */
.form-card {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 18px;
    padding: 35px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

/* FORM GROUP */
.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-size: 14px;
    color: var(--text);
    font-weight: 700;
    margin-bottom: 9px;
}

.form-group label i {
    color: var(--pink-dark);
    margin-right: 5px;
}

/* INPUT */
.form-control {
    height: 50px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    border: 1px solid var(--pink-border);
    border-radius: 10px;
    padding: 10px 15px;
    color: var(--text);
    background: var(--pink-bg);
}

.form-control::placeholder {
    color: #B99AA6;
}

.form-control:focus {
    border-color: var(--pink);
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.15);
    background: var(--white);
}

/* DROPDOWN */
select.form-control {
    cursor: pointer;
}

select.form-control option {
    color: var(--text);
    background: var(--white);
}

/* TOMBOL */
.tombol {
    margin-top: 30px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 700;
    padding: 12px 25px;
    border-radius: 25px;
    transition: 0.3s;
}

/* TOMBOL TAMBAH */
.btn-tambah {
    background: var(--pink);
    border: 1px solid var(--pink);
    color: var(--white);
}

.btn-tambah:hover {
    background: var(--pink-dark);
    border-color: var(--pink-dark);
    color: var(--white);
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(217, 133, 163, 0.20);
}

/* TOMBOL KEMBALI */
.btn-kembali {
    background: var(--pink-light);
    border: 1px solid var(--pink-border);
    color: var(--pink-dark);
}

.btn-kembali:hover {
    background: #F8DCE6;
    border-color: var(--pink);
    color: #B96F8A;
    transform: translateY(-2px);
}

/* RESPONSIVE */
@media (max-width: 576px) {
    .navbar-brand-custom {
        margin-left: 20px;
    }

    .content {
        padding: 0 15px 35px;
        margin-top: 25px;
    }

    .judul {
        font-size: 23px;
    }

    .form-card {
        padding: 22px 18px;
        border-radius: 15px;
    }

    .tombol {
        flex-direction: column;
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
    <a href="index.php" class="navbar-brand-custom">
        <i class="fas fa-home"></i> Hynut KOST</a>
</nav>

<!-- CONTENT -->
<div class="content">

    <!-- JUDUL -->
    <div class="judul">
        <i class="fas fa-bed"></i>
        Tambah Data Kamar
    </div>

    <div class="garis"></div>

    <!-- FORM CARD -->
    <div class="form-card">

        <form method="POST">

            <!-- PILIH KOST -->
            <div class="form-group">
                <label>
                    <i class="fas fa-home"></i>
                    Pilih Kost
                </label>

                <select name="kos_id" class="form-control" required>
                    <option value="">-- Pilih Kost --</option>

                    <?php while ($row_kos = mysqli_fetch_assoc($query_kos)): ?>
                        <option value="<?= $row_kos['kos_id']; ?>">
                            <?= isset($row_kos['nama_kos']) ? htmlspecialchars($row_kos['nama_kos']) : 'Kos ID: ' . $row_kos['kos_id']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- NOMOR KAMAR -->
            <div class="form-group">
                <label>
                    <i class="fas fa-door-closed"></i>
                    Nomor Kamar
                </label>

                <input
                    type="text"
                    name="no_kamar"
                    class="form-control"
                    placeholder="Contoh: A-01, 101"
                    required
                >
            </div>

            <!-- TIPE KAMAR -->
            <div class="form-group">
                <label>
                    <i class="fas fa-bed"></i>
                    Tipe Kamar
                </label>

                <input
                    type="text"
                    name="tipe_kamar"
                    class="form-control"
                    placeholder="Contoh: Standard, Deluxe, VIP"
                    required
                >
            </div>

            <!-- HARGA -->
            <div class="form-group">
                <label>
                    <i class="fas fa-money-bill-wave"></i>
                    Harga (Rp)
                </label>

                <input
                    type="number"
                    name="harga"
                    class="form-control"
                    placeholder="Contoh: 1000000"
                    min="0"
                    required
                >
            </div>

            <!-- STATUS KAMAR -->
            <div class="form-group">
                <label>
                    <i class="fas fa-info-circle"></i>
                    Status Kamar
                </label>

                <select name="status_kamar" class="form-control" required>
                    <option value="Kosong">Kosong</option>
                    <option value="Terisi">Terisi</option>
                </select>
            </div>

            <!-- BUTTON -->
            <div class="tombol">

                <button type="submit" name="simpan" class="btn btn-tambah">
                    <i class="fas fa-plus"></i> Tambah
                </button>

                <a href="kamar_owner.php" class="btn btn-kembali">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
