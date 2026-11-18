<?php
include '../database.php';

/* AMBIL DATA KOST & FASILITAS */
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");
$query_fasilitas = mysqli_query($conn, "SELECT * FROM fasilitas ORDER BY fasilitas_id ASC");

/* PROSES SIMPAN KOST FASILITAS */
if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $fasilitas_id = intval($_POST['fasilitas_id']);

    $query = "INSERT INTO kos_fasilitas (kos_id, fasilitas_id)
              VALUES ('$kos_id', '$fasilitas_id')";

    if (mysqli_query($conn, $query)) {
        header("Location: kos_fasilitas.php");
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
    <title>Tambah Data Kost Fasilitas - Hynut KOST</title>

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

    /* DASAR */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

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
        padding: 20px 25px 50px;
    }

    /* JUDUL */
    .judul {
        font-size: 32px;
        font-weight: 600;
        color: var(--pink-dark);
        margin-bottom: 20px;
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
        border-radius: 15px;
        padding: 35px;
        box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
    }

    /* FORM GROUP */
    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        font-size: 17px;
        color: var(--text);
        font-weight: 600;
        margin-bottom: 9px;
    }

    .form-group label i {
        color: var(--pink-dark);
        margin-right: 5px;
    }

    /* INPUT DAN DROPDOWN */
    .form-control {
        height: 52px;
        font-size: 16px;
        font-family: 'Poppins', sans-serif;
        border: 1px solid var(--pink-border);
        border-radius: 8px;
        padding: 10px 15px;
        color: var(--text);
        background: #FFFFFF;
        transition: 0.3s;
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
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .btn {
        font-family: 'Poppins', sans-serif;
        font-size: 16px;
        font-weight: 600;
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
            padding: 25px 18px 35px;
        }

        .judul {
            font-size: 28px;
        }

        .navbar-brand-custom {
            margin-left: 20px;
            font-size: 22px;
        }

        .form-card {
            padding: 25px 20px;
        }
    }

    @media (max-width: 480px) {
        .judul {
            font-size: 24px;
        }

        .form-card {
            padding: 22px 18px;
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
        <i class="fas fa-layer-group"></i>
        Tambah Data Kost Fasilitas
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

            <!-- PILIH FASILITAS -->
            <div class="form-group">
                <label>
                    <i class="fas fa-list-ul"></i>
                    Pilih Fasilitas
                </label>

                <select name="fasilitas_id" class="form-control" required>
                    <option value="">-- Pilih Fasilitas --</option>

                    <?php while ($row_fasilitas = mysqli_fetch_assoc($query_fasilitas)): ?>
                        <option value="<?= $row_fasilitas['fasilitas_id']; ?>">
                            <?= htmlspecialchars($row_fasilitas['nama_fasilitas']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- BUTTON -->
            <div class="tombol">

                <button type="submit" name="simpan" class="btn btn-tambah">
                    <i class="fas fa-plus"></i> Tambah
                </button>

                <a href="kos_fasilitas.php" class="btn btn-kembali">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
