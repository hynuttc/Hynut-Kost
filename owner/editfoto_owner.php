<?php
include '../database.php';

// ==========================================
// CEK ID FOTO KOS
// ==========================================

if (!isset($_GET['id'])) {
    die("ID foto kos tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA FOTO KOS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM foto_kos WHERE foto_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$foto = mysqli_fetch_assoc($query);

if (!$foto) {
    die("Data foto kos tidak ditemukan.");
}

// Ambil daftar Kos untuk opsi dropdown
$query_kos = "SELECT * FROM kos ORDER BY kos_id ASC";
$result_kos = mysqli_query($conn, $query_kos);


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $kos_id    = mysqli_real_escape_string($conn, $_POST['kos_id']);
    $path_foto = $foto['path_foto']; // Default menggunakan file foto lama

    // Cek jika ada file foto baru yang diupload
    if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
        $nama_file     = $_FILES['foto']['name'];
        $tmp_file      = $_FILES['foto']['tmp_name'];
        $folder_tujuan = '../uploads/';

        // Buat folder uploads jika belum ada
        if (!file_exists($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }

        // Hapus file foto lama jika berkasnya ada
        if (!empty($foto['path_foto']) && file_exists($folder_tujuan . $foto['path_foto'])) {
            unlink($folder_tujuan . $foto['path_foto']);
        }

        // Generate nama file baru secara unik
        $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
        $path_foto = time() . '_' . rand(100, 999) . '.' . $ekstensi;

        move_uploaded_file($tmp_file, $folder_tujuan . $path_foto);
    }


    // ======================================
    // UPDATE DATA FOTO KOS
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE foto_kos SET
            kos_id = '$kos_id',
            path_foto = '$path_foto'
         WHERE foto_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: fotokos_owner.php");
        exit;

    } else {

        die("Gagal menyimpan data: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Data Foto Kos</title>

    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
/* =========================================
   TEMA SOFT BABY PINK PASTEL
   HALAMAN EDIT DATA FOTO KOS
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
    background: var(--pink-bg);
    color: var(--text);
    font-family: 'Source Sans Pro', Arial, sans-serif;
}

/* CONTAINER */
.container-edit {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

/* CARD */
.card-edit {
    background: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.10);
    overflow: hidden;
}

/* JUDUL */
.judul {
    background: var(--pink-light);
    color: var(--pink-dark);
    font-size: 28px;
    font-weight: 600;
    padding: 18px 22px;
    border-bottom: 1px solid var(--pink-border);
}

/* ISI */
.isi {
    padding: 25px;
    background: #FFFFFF;
}

/* LABEL */
.form-label {
    display: block;
    color: var(--pink-dark);
    font-size: 17px;
    font-weight: 500;
    margin-bottom: 9px;
}

/* INPUT DAN SELECT */
.form-control,
.form-select {
    width: 100%;
    min-height: 48px;
    padding: 10px 13px;
    font-size: 16px;
    color: var(--text);
    background-color: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    box-shadow: none;
    transition: 0.3s;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
    outline: none;
}

/* INPUT FILE */
input[type="file"].form-control {
    padding: 9px;
    height: auto;
    background: #FFFFFF;
}

input[type="file"]::file-selector-button {
    background: var(--pink-light);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    border-radius: 5px;
    padding: 7px 12px;
    margin-right: 12px;
    cursor: pointer;
}

/* PREVIEW FOTO */
.img-preview-box {
    display: block;
    width: 200px;
    height: 150px;
    object-fit: cover;
    border: 2px solid var(--pink-border);
    border-radius: 10px;
    padding: 4px;
    background: #FFFFFF;
    box-shadow: 0 3px 10px rgba(217, 133, 163, 0.10);
}

/* TEKS KETERANGAN */
.text-muted {
    color: var(--muted) !important;
    font-size: 13px;
}

/* TOMBOL */
.tombol {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 20px;
}

/* TOMBOL SIMPAN */
.btn-simpan {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    padding: 10px 22px;
    background: var(--pink);
    color: #FFFFFF;
    border: 1px solid var(--pink);
    border-radius: 7px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: 0.3s;
}

.btn-simpan:hover {
    background: var(--pink-dark);
    border-color: var(--pink-dark);
    color: #FFFFFF;
}

/* TOMBOL KEMBALI */
.btn-kembali {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 110px;
    padding: 10px 22px;
    background: var(--pink-dark);
    color: #FFFFFF;
    border: 1px solid var(--pink-dark);
    border-radius: 7px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}

.btn-kembali:hover {
    background: #C67594;
    border-color: #C67594;
    color: #FFFFFF;
}

/* RESPONSIVE MOBILE */
@media (max-width: 768px) {
    .container-edit {
        width: 95%;
        margin: 20px auto;
    }

    .judul {
        font-size: 24px;
        padding: 16px 18px;
    }

    .isi {
        padding: 18px;
    }

    .img-preview-box {
        width: 180px;
        height: 135px;
    }

    .tombol {
        gap: 8px;
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


<div class="container-edit">

    <div class="card-edit">


        <!-- JUDUL -->

        <div class="judul">
            Edit Data Foto Kos
        </div>


        <div class="isi">

            <form method="POST" enctype="multipart/form-data">


                <!-- PILIH KOS ID -->

                <div class="mb-4">

                    <label class="form-label">
                        Pilih Kos (Kos ID)
                    </label>

                    <select name="kos_id" class="form-select" required>
                        <option value="">-- Pilih Kos --</option>
                        <?php
                        if ($result_kos && mysqli_num_rows($result_kos) > 0) {
                            while ($row_kos = mysqli_fetch_assoc($result_kos)) {
                                $selected = ($row_kos['kos_id'] == $foto['kos_id']) ? 'selected' : '';
                                $nama = isset($row_kos['nama_kos']) ? $row_kos['nama_kos'] : 'Kos ID: ' . $row_kos['kos_id'];
                                echo "<option value='" . $row_kos['kos_id'] . "' $selected>" . htmlspecialchars($nama) . " (ID: " . $row_kos['kos_id'] . ")</option>";
                            }
                        }
                        ?>
                    </select>

                </div>


                <!-- FOTO SAAT INI -->

                <div class="mb-4">

                    <label class="form-label d-block">
                        Foto Saat Ini
                    </label>

                    <?php if (!empty($foto['path_foto'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($foto['path_foto']); ?>" 
                             alt="Foto Kos Saat Ini" 
                             class="img-preview-box mb-2"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/200x150?text=File+Tidak+Ada';">
                        <br>
                        <small class="text-muted">Nama File: <?= htmlspecialchars($foto['path_foto']); ?></small>
                    <?php else: ?>
                        <p class="text-muted">Belum ada foto terpilih.</p>
                    <?php endif; ?>

                </div>


                <!-- UPLOAD FOTO BARU -->

                <div class="mb-4">

                    <label class="form-label">
                        Ganti Foto Kos (Opsional)
                    </label>

                    <input
                        type="file"
                        name="foto"
                        class="form-control"
                        accept="image/*"
                    >

                    <small class="text-muted mt-1 d-block">
                        Kosongkan jika tidak ingin mengganti foto.
                    </small>

                </div>


                <!-- TOMBOL -->

                <div class="tombol">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-simpan"
                    >
                        Simpan
                    </button>


                    <a
                        href="fotokos_owner.php"
                        class="btn-kembali"
                    >
                        Kembali
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


</body>

</html>
