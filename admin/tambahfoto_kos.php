
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
        header("Location: foto_kos.php");
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

    <!-- FONT POPPINS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            font-family: 'Poppins', sans-serif;
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
            margin-left: auto;
            margin-right: auto;
            padding: 55px 20px 50px 20px;
        }

        /* JUDUL */
        .judul {
            font-size: 32px;
            font-weight: 700;
            color: var(--pink-dark);
            margin-bottom: 10px;
        }

        .judul i {
            color: var(--pink);
            margin-right: 5px;
        }

        .subjudul {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 22px;
        }

        .garis {
            border-top: 1px solid var(--pink-border);
            margin-bottom: 30px;
        }

        /* FORM CARD */
        .form-card {
            background: #FFFFFF;
            border: 1px solid var(--pink-border);
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(217, 133, 163, 0.08);
        }

        /* FORM */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 16px;
            color: var(--text);
            margin-bottom: 9px;
            font-weight: 600;
        }

        .form-group label i {
            color: var(--pink-dark);
            margin-right: 5px;
        }

        /* INPUT */
        .form-control {
            height: 52px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
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

        /* FILE INPUT */
        .form-control-file {
            width: 100%;
            padding: 14px;
            border: 1px dashed var(--pink);
            border-radius: 8px;
            background-color: var(--pink-bg);
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            color: var(--muted);
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
            font-size: 15px;
            font-weight: 600;
            padding: 11px 25px;
            border-radius: 25px;
            transition: 0.3s;
        }

        /* TOMBOL TAMBAH */
        .btn-tambah {
            background-color: var(--pink);
            border: 1px solid var(--pink);
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
            border: 1px solid #F1DCE4;
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

            .form-card {
                padding: 25px;
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

            .tombol {
                flex-direction: column;
            }

            .tombol .btn {
                width: 100%;
            }

            .form-card {
                padding: 20px 16px;
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

    <div class="subjudul">
        Tambahkan foto kos untuk melengkapi informasi properti.
    </div>

    <div class="garis"></div>

    <!-- FORM CARD -->
    <div class="form-card">

        <form method="POST" enctype="multipart/form-data">

            <!-- PILIH KOS -->
            <div class="form-group">
                <label>
                    <i class="fas fa-home"></i>
                    Pilih Kos
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
                    <i class="fas fa-camera"></i>
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

                <a href="foto_kos.php" class="btn btn-kembali">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>
