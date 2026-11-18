
<?php
include '../database.php';

// CEK ID FOTO KOS
if (!isset($_GET['id'])) {
    die("ID foto kos tidak ditemukan.");
}

$id = intval($_GET['id']);

// AMBIL DATA FOTO KOS
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

// AMBIL DAFTAR KOS
$query_kos = "SELECT * FROM kos ORDER BY kos_id ASC";
$result_kos = mysqli_query($conn, $query_kos);

// PROSES SIMPAN UPDATE
if (isset($_POST['simpan'])) {

    $kos_id = mysqli_real_escape_string(
        $conn,
        $_POST['kos_id']
    );

    $path_foto = $foto['path_foto'];

    // CEK UPLOAD FOTO BARU
    if (
        isset($_FILES['foto']['name']) &&
        $_FILES['foto']['name'] != ''
    ) {

        $nama_file = $_FILES['foto']['name'];
        $tmp_file = $_FILES['foto']['tmp_name'];
        $folder_tujuan = '../uploads/';

        if (!file_exists($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }

        $ekstensi = pathinfo(
            $nama_file,
            PATHINFO_EXTENSION
        );

        $path_foto = time() . '_' . rand(100, 999) . '.' . $ekstensi;

        if (move_uploaded_file(
            $tmp_file,
            $folder_tujuan . $path_foto
        )) {

            // HAPUS FOTO LAMA
            if (
                !empty($foto['path_foto']) &&
                file_exists($folder_tujuan . $foto['path_foto'])
            ) {
                unlink($folder_tujuan . $foto['path_foto']);
            }

        } else {
            die("Gagal mengupload foto.");
        }
    }

    // UPDATE DATA
    $update = mysqli_query(
        $conn,
        "UPDATE foto_kos SET
            kos_id = '$kos_id',
            path_foto = '$path_foto'
         WHERE foto_id = $id"
    );

    if ($update) {
        header("Location: foto_kos.php");
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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
/* =========================================
   TEMA SOFT BABY PINK PASTEL
   WARNA TETAP DIPERTAHANKAN
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

/* =========================================
   DASAR HALAMAN
========================================= */

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

/* =========================================
   CONTAINER FORM
========================================= */

.container-edit {
    width: 90%;
    max-width: 850px;
    margin: 40px auto;
    padding: 0;
}

/* =========================================
   CARD EDIT
========================================= */

.card-edit {
    width: 100%;
    background: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(217, 133, 163, 0.10);
}

/* =========================================
   JUDUL
========================================= */

.judul {
    background: var(--pink-light);
    color: var(--pink-dark);
    font-size: 28px;
    font-weight: 600;
    padding: 22px 25px;
    margin: 0;
    border-bottom: 1px solid var(--pink-border);
}

/* =========================================
   ISI FORM
========================================= */

.isi {
    padding: 25px;
}

.isi form {
    width: 100%;
}

/* =========================================
   LABEL FORM
========================================= */

.form-label {
    display: block;
    color: var(--pink-dark);
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 9px;
}

/* =========================================
   INPUT DAN SELECT
========================================= */

.form-control,
.form-select {
    display: block;
    width: 100%;
    min-height: 43px;
    padding: 10px 13px;
    color: var(--text);
    background-color: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    font-size: 14px;
    outline: none;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

/* INPUT FILE */

input[type="file"].form-control {
    padding: 9px;
    background: #FFFFFF;
}

/* =========================================
   JARAK ANTAR FORM
========================================= */

.mb-4 {
    margin-bottom: 23px !important;
}

/* =========================================
   FOTO SAAT INI
========================================= */

.img-preview-box {
    display: block;
    width: 100%;
    max-width: 400px;
    height: 250px;
    object-fit: cover;
    border: 2px solid var(--pink-border);
    border-radius: 10px;
    padding: 4px;
    background: #FFFFFF;
    margin: 8px 0 12px;
}

/* =========================================
   KETERANGAN FOTO
========================================= */

.text-muted {
    display: block;
    color: var(--muted) !important;
    font-size: 13px;
    margin-top: 7px;
    line-height: 1.5;
}

/* =========================================
   TOMBOL SIMPAN DAN KEMBALI
========================================= */

.tombol {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-top: 25px;
}

/* SIMPAN */

.btn-simpan {
    background: var(--pink-dark);
    color: #FFFFFF;
    border: 1px solid var(--pink-dark);
    border-radius: 7px;
    padding: 10px 25px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.btn-simpan:hover {
    background: var(--pink);
    border-color: var(--pink);
    color: #FFFFFF;
}

/* KEMBALI */

.btn-kembali {
    display: inline-block;
    background: var(--pink-dark);
    color: #FFFFFF;
    border: 1px solid var(--pink-dark);
    border-radius: 7px;
    padding: 10px 25px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}

.btn-kembali:hover {
    background: var(--pink);
    color: #FFFFFF;
    border-color: var(--pink);
}

/* =========================================
   RESPONSIVE HP DAN TABLET
========================================= */

@media (max-width: 768px) {

    .container-edit {
        width: 95%;
        margin: 20px auto;
    }

    .judul {
        font-size: 23px;
        padding: 18px 20px;
    }

    .isi {
        padding: 18px;
    }

    .img-preview-box {
        max-width: 100%;
        height: 220px;
    }

    .tombol {
        flex-wrap: wrap;
    }

}

/* =========================================
   RESPONSIVE HP KECIL
========================================= */

@media (max-width: 480px) {

    .container-edit {
        width: 96%;
        margin: 15px auto;
    }

    .judul {
        font-size: 21px;
    }

    .isi {
        padding: 15px;
    }

    .btn-simpan,
    .btn-kembali {
        padding: 9px 20px;
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

                <!-- PILIH KOS -->
                <div class="mb-4">

                    <label class="form-label">
                        Pilih Kos
                    </label>

                    <select name="kos_id"
                            class="form-select"
                            required>

                        <option value="">
                            -- Pilih Kos --
                        </option>

                        <?php
                        if (
                            $result_kos &&
                            mysqli_num_rows($result_kos) > 0
                        ) {
                            while (
                                $row_kos = mysqli_fetch_assoc($result_kos)
                            ) {

                                $selected = (
                                    $row_kos['kos_id'] == $foto['kos_id']
                                ) ? 'selected' : '';

                                $nama = isset($row_kos['nama_kos'])
                                    ? $row_kos['nama_kos']
                                    : 'Kos ID: ' . $row_kos['kos_id'];

                                echo "<option value='" .
                                    $row_kos['kos_id'] .
                                    "' $selected>" .
                                    htmlspecialchars($nama) .
                                    " (ID: " .
                                    $row_kos['kos_id'] .
                                    ")</option>";
                            }
                        }
                        ?>

                    </select>

                </div>

                <!-- FOTO SAAT INI -->
                <div class="mb-4">

                    <label class="form-label">
                        Foto Saat Ini
                    </label>

                    <?php if (!empty($foto['path_foto'])): ?>

                        <img
                            src="../uploads/<?= htmlspecialchars($foto['path_foto']); ?>"
                            alt="Foto Kos Saat Ini"
                            class="img-preview-box"
                        >

                        <small class="text-muted">
                            Nama File:
                            <?= htmlspecialchars($foto['path_foto']); ?>
                        </small>

                    <?php else: ?>

                        <p class="text-muted">
                            Belum ada foto terpilih.
                        </p>

                    <?php endif; ?>

                </div>

                <!-- GANTI FOTO -->
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

                    <small class="text-muted">
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
                        href="foto_kos.php"
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
