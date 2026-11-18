<?php
include '../database.php';

// ==========================================
// CEK ID KAMAR
// ==========================================

if (!isset($_GET['id'])) {
    die("ID kamar tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA KAMAR
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kamar WHERE kamar_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$kamar = mysqli_fetch_assoc($query);

if (!$kamar) {
    die("Data kamar tidak ditemukan.");
}

// AMBIL LIST KOST UNTUK DROPDOWN
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $no_kamar     = mysqli_real_escape_string($conn, $_POST['no_kamar']);
    $tipe_kamar   = mysqli_real_escape_string($conn, $_POST['tipe_kamar']);
    $harga        = intval($_POST['harga']);
    $status_kamar = mysqli_real_escape_string($conn, $_POST['status_kamar']);


    // ======================================
    // UPDATE DATA KAMAR
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE kamar SET
            kos_id = '$kos_id',
            no_kamar = '$no_kamar',
            tipe_kamar = '$tipe_kamar',
            harga = '$harga',
            status_kamar = '$status_kamar'
         WHERE kamar_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: kamar.php");
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

    <title>Edit Data Kamar</title>


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
    height: 48px;
    font-size: 16px;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    color: var(--text);
    background-color: #fff;
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
            Edit Data Kamar
        </div>


        <div class="isi">

            <form method="POST">


                <!-- PILIH KOST -->

                <div class="mb-4">

                    <label class="form-label">
                        Pilih Kost
                    </label>

                    <select name="kos_id" class="form-select" required>
                        <?php while ($row_kos = mysqli_fetch_assoc($query_kos)): ?>
                            <option value="<?= $row_kos['kos_id']; ?>" <?= ($row_kos['kos_id'] == $kamar['kos_id']) ? 'selected' : ''; ?>>
                                <?= isset($row_kos['nama_kos']) ? htmlspecialchars($row_kos['nama_kos']) : 'Kos ID: ' . $row_kos['kos_id']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                </div>


                <!-- NO KAMAR -->

                <div class="mb-4">

                    <label class="form-label">
                        Nomor Kamar
                    </label>

                    <input
                        type="text"
                        name="no_kamar"
                        class="form-control"
                        value="<?= htmlspecialchars($kamar['no_kamar']); ?>"
                        required
                    >

                </div>


                <!-- TIPE KAMAR -->

                <div class="mb-4">

                    <label class="form-label">
                        Tipe Kamar
                    </label>

                    <input
                        type="text"
                        name="tipe_kamar"
                        class="form-control"
                        value="<?= htmlspecialchars($kamar['tipe_kamar']); ?>"
                        required
                    >

                </div>


                <!-- HARGA -->

                <div class="mb-4">

                    <label class="form-label">
                        Harga (Rp)
                    </label>

                    <input
                        type="number"
                        name="harga"
                        class="form-control"
                        value="<?= htmlspecialchars($kamar['harga']); ?>"
                        required
                    >

                </div>


                <!-- STATUS KAMAR -->

                <div class="mb-4">

                    <label class="form-label">
                        Status Kamar
                    </label>

                    <select name="status_kamar" class="form-select" required>
                        <option value="Kosong" <?= ($kamar['status_kamar'] == 'Kosong') ? 'selected' : ''; ?>>Kosong</option>
                        <option value="Terisi" <?= ($kamar['status_kamar'] == 'Terisi') ? 'selected' : ''; ?>>Terisi</option>
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
                        href="kamar.php"
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