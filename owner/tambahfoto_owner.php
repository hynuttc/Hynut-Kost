
<?php
include '../database.php';

/* =====================================================
   PROSES SIMPAN FOTO KOS
   ===================================================== */

if (isset($_POST['simpan'])) {

    $kos_id = mysqli_real_escape_string($conn, $_POST['kos_id']);

    $nama_file = $_FILES['foto']['name'];
    $tmp_file = $_FILES['foto']['tmp_name'];
    $folder_tujuan = '../uploads/';

    if (!file_exists($folder_tujuan)) {
        mkdir($folder_tujuan, 0777, true);
    }

    if (!empty($nama_file)) {
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
        $path_foto = time() . '_' . rand(100, 999) . '.' . $ekstensi;

        move_uploaded_file($tmp_file, $folder_tujuan . $path_foto);
    } else {
        $path_foto = '';
    }

    $query = "INSERT INTO foto_kos (kos_id, path_foto)
              VALUES ('$kos_id', '$path_foto')";

    if (mysqli_query($conn, $query)) {
        header("Location: fotokos_owner.php");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}

// Ambil daftar Kos
$query_kos = "SELECT * FROM kos ORDER BY kos_id ASC";
$result_kos = mysqli_query($conn, $query_kos);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto Kos - Hynut KOST</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
    color: var(--pink-dark) !important;
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

/* UPLOAD FOTO */
.form-control-file {
    width: 100%;
    padding: 14px;
    border: 1px dashed var(--pink);
    border-radius: 12px;
    background: var(--pink-bg);
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    color: var(--muted);
}

.form-control-file:focus {
    outline: none;
    border-color: var(--pink-dark);
    box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.15);
}

.form-text {
    font-size: 12px;
    color: var(--muted);
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
        <i class="fas fa-images"></i>
        Tambah Data Foto Kos
    </div>

    <div class="garis"></div>

    <!-- FORM CARD -->
    <div class="form-card">

        <form method="POST" enctype="multipart/form-data">

            <!-- PILIH KOS -->
            <div class="form-group">
                <label>
                    <i class="fas fa-home" style="color: #c72f78;"></i>
                    Pilih Kos (Kos ID)
                </label>

                <select name="kos_id" class="form-control" required>
                    <option value="">-- Pilih Kos --</option>

                    <?php
                    if ($result_kos && mysqli_num_rows($result_kos) > 0) {
                        while ($row_kos = mysqli_fetch_assoc($result_kos)) {
                            $nama = isset($row_kos['nama_kos'])
                                ? $row_kos['nama_kos']
                                : 'Kos ID: ' . $row_kos['kos_id'];

                            echo "<option value='" . $row_kos['kos_id'] . "'>" .
                                htmlspecialchars($nama) .
                                " (ID: " . $row_kos['kos_id'] . ")</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- UPLOAD FOTO -->
            <div class="form-group">
                <label>
                    <i class="fas fa-camera" style="color: #c72f78;"></i>
                    Upload File Foto Kos
                </label>

                <input
                    type="file"
                    name="foto"
                    class="form-control-file"
                    accept="image/*"
                    required
                >

                <small class="form-text mt-2">
                    <i class="fas fa-info-circle"></i>
                    Pilih berkas gambar (.jpg, .jpeg, .png, .webp).
                </small>
            </div>

            <!-- BUTTON -->
            <div class="tombol">

                <button type="submit" name="simpan" class="btn btn-tambah">
                    <i class="fas fa-plus"></i> Tambah
                </button>

                <a href="fotokos_owner.php" class="btn btn-kembali">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
