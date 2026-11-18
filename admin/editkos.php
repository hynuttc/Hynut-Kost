<?php
include '../database.php';

// ==========================================
// CEK ID KOS
// ==========================================

if (!isset($_GET['id'])) {
    die("ID kos tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA KOS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kos WHERE kos_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$kos = mysqli_fetch_assoc($query);

if (!$kos) {
    die("Data kos tidak ditemukan.");
}


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $owner_id          = intval($_POST['owner_id']);
    $nama_kos          = mysqli_real_escape_string($conn, $_POST['nama_kos']);
    $alamat            = mysqli_real_escape_string($conn, $_POST['alamat']);
    $latitude          = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude         = mysqli_real_escape_string($conn, $_POST['longitude']);
    $tipe_kos          = mysqli_real_escape_string($conn, $_POST['tipe_kos']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);


    // ======================================
    // UPDATE DATA KOS
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE kos SET
            owner_id = '$owner_id',
            nama_kos = '$nama_kos',
            alamat = '$alamat',
            latitude = '$latitude',
            longitude = '$longitude',
            tipe_kos = '$tipe_kos',
            status_verifikasi = '$status_verifikasi'
         WHERE kos_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: kos.php");
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

    <title>Edit Data Kos</title>


    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


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

/* BACKGROUND */

body {
    background-color: var(--pink-bg);
    color: var(--text);
    font-family: Arial, sans-serif;
}

.container-edit {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

/* CARD */

.card-edit {
    background: #fff;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(217, 133, 163, 0.08);
}

/* JUDUL */

.judul {
    font-size: 28px;
    font-weight: 600;
    padding: 18px 22px;
    border-bottom: 1px solid var(--pink-border);
    color: var(--pink-dark);
    background-color: var(--pink-light);
}

/* ISI */

.isi {
    padding: 25px;
    background-color: #fff;
}

/* LABEL */

.form-label {
    font-size: 17px;
    margin-bottom: 8px;
    color: var(--pink-dark);
    font-weight: 500;
}

/* INPUT DAN SELECT */

.form-control,
.form-select {
    font-size: 16px;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    color: var(--text);
    background-color: #fff;
}

.form-control,
.form-select {
    height: 48px;
}

textarea.form-control {
    height: auto;
    min-height: 100px;
    resize: vertical;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.2);
}

/* TOMBOL */

.tombol {
    margin-top: 20px;
}

.btn-success {
    background-color: var(--pink) !important;
    border-color: var(--pink) !important;
    color: #fff !important;
    border-radius: 7px;
}

.btn-success:hover {
    background-color: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
}

.btn-secondary {
    background-color: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
    color: #fff !important;
    border-radius: 7px;
}

.btn-secondary:hover {
    background-color: #C67594 !important;
    border-color: #C67594 !important;
}

/* RESPONSIF */

@media (max-width: 768px) {
    .container-edit {
        width: 95%;
        margin: 20px auto;
    }

    .judul {
        font-size: 24px;
    }

    .isi {
        padding: 18px;
    }

    .tombol .btn {
        margin-bottom: 5px;
    }
}

    </style>

</head>


<body>


<div class="container-edit">

    <div class="card-edit">


        <!-- JUDUL -->

        <div class="judul">
            Edit Data Kos
        </div>


        <div class="isi">

            <form method="POST">


                <!-- OWNER ID -->

                <div class="mb-4">

                    <label class="form-label">
                        Owner ID
                    </label>

                    <input
                        type="number"
                        name="owner_id"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['owner_id']); ?>"
                        required
                    >

                </div>


                <!-- NAMA KOS -->

                <div class="mb-4">

                    <label class="form-label">
                        Nama Kos
                    </label>

                    <input
                        type="text"
                        name="nama_kos"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['nama_kos']); ?>"
                        required
                    >

                </div>


                <!-- ALAMAT -->

                <div class="mb-4">

                    <label class="form-label">
                        Alamat
                    </label>

                    <textarea
                        name="alamat"
                        class="form-control"
                        rows="3"
                        required
                    ><?= htmlspecialchars($kos['alamat']); ?></textarea>

                </div>


                <!-- LATITUDE -->

                <div class="mb-4">

                    <label class="form-label">
                        Latitude
                    </label>

                    <input
                        type="text"
                        name="latitude"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['latitude']); ?>"
                    >

                </div>


                <!-- LONGITUDE -->

                <div class="mb-4">

                    <label class="form-label">
                        Longitude
                    </label>

                    <input
                        type="text"
                        name="longitude"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['longitude']); ?>"
                    >

                </div>


                <!-- TIPE KOS -->

                <div class="mb-4">

                    <label class="form-label">
                        Tipe Kos
                    </label>

                    <select name="tipe_kos" class="form-select" required>
                        <option value="Putra" <?= ($kos['tipe_kos'] == 'Putra') ? 'selected' : ''; ?>>Putra</option>
                        <option value="Putri" <?= ($kos['tipe_kos'] == 'Putri') ? 'selected' : ''; ?>>Putri</option>
                        <option value="Campur" <?= ($kos['tipe_kos'] == 'Campur') ? 'selected' : ''; ?>>Campur</option>
                    </select>

                </div>


                <!-- STATUS VERIFIKASI -->

                <div class="mb-4">

                    <label class="form-label">
                        Status Verifikasi
                    </label>

                    <select name="status_verifikasi" class="form-select" required>
                        <option value="Menunggu" <?= ($kos['status_verifikasi'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Aktif" <?= ($kos['status_verifikasi'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Non-aktif" <?= ($kos['status_verifikasi'] == 'Non-aktif') ? 'selected' : ''; ?>>Non-aktif</option>
                    </select>

                </div>


                <!-- TOMBOL -->

                <div class="tombol">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-success px-4"
                    >
                        Simpan
                    </button>


                    <a
                        href="kos.php"
                        class="btn btn-secondary px-4"
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