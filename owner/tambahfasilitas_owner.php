<?php
include '../database.php';

/* =====================================================
   AMBIL DATA KOST & FASILITAS UNTUK DROPDOWN
   ===================================================== */
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");
$query_fasilitas = mysqli_query($conn, "SELECT * FROM fasilitas ORDER BY fasilitas_id ASC");

/* =====================================================
   PROSES SIMPAN KOST FASILITAS
   ===================================================== */
if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $fasilitas_id = intval($_POST['fasilitas_id']);

    /* =================================================
       SIMPAN KE DATABASE TABEL KOS_FASILITAS
       ================================================= */
    $query = "INSERT INTO kos_fasilitas (kos_id, fasilitas_id) VALUES ('$kos_id', '$fasilitas_id')";

    if (mysqli_query($conn, $query)) {
        header("Location: kosfasilitas_owner.php");
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
    <title>Tambah Data Kost Fasilitas</title>

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
    body {
        margin: 0;
        background-color: var(--pink-bg);
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
        padding: 55px 20px 50px 20px;
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
        background-color: #FFFFFF;
        color: var(--text);
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: var(--pink);
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.20);
        background-color: #FFFFFF;
    }

    /* TOMBOL */
    .tombol {
        margin-top: 30px;
    }

    .btn {
        font-size: 18px;
        padding: 11px 25px;
        border-radius: 25px;
        transition: 0.3s;
    }

    /* TOMBOL TAMBAH */
    .btn-tambah {
        background-color: var(--pink);
        border-color: var(--pink);
        color: white;
    }

    .btn-tambah:hover {
        background-color: var(--pink-dark);
        border-color: var(--pink-dark);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(217, 133, 163, 0.2);
    }

    /* TOMBOL KEMBALI */
    .btn-kembali {
        background-color: #F1DCE4;
        border-color: #F1DCE4;
        color: #926A7A;
    }

    .btn-kembali:hover {
        background-color: #E8C5D3;
        border-color: #E8C5D3;
        color: #76505F;
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
        Tambah Data Kost Fasilitas
    </div>

    <div class="garis"></div>

    <!-- FORM TAMBAH KOST FASILITAS -->
    <form method="POST">

        <!-- PILIH KOST -->
        <div class="form-group">
            <label>Pilih Kost</label>
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
            <label>Pilih Fasilitas</label>
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
                Tambah
            </button>

            <a href="kosfasilitas_owner.php" class="btn btn-kembali ml-2">
                Kembali
            </a>
        </div>

    </form>

</div>

</body>
</html>