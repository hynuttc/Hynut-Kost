
<?php
include '../database.php';

// ==========================================
// CEK ID FASILITAS
// ==========================================

if (!isset($_GET['id'])) {
    die("ID fasilitas tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA FASILITAS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM fasilitas WHERE fasilitas_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$fasilitas = mysqli_fetch_assoc($query);

if (!$fasilitas) {
    die("Data fasilitas tidak ditemukan.");
}


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $nama_fasilitas = mysqli_real_escape_string($conn, $_POST['nama_fasilitas']);


    // ======================================
    // UPDATE DATA FASILITAS
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE fasilitas SET
            nama_fasilitas = '$nama_fasilitas'
         WHERE fasilitas_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: fasilitas.php");
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

    <title>Edit Data Fasilitas</title>


    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>
 /* =========================================
   TEMA SOFT BABY PINK PASTEL
   HALAMAN EDIT DATA FASILITAS
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

/* INPUT */
.form-control {
    width: 100%;
    min-height: 48px;
    padding: 10px 13px;
    font-size: 16px;
    color: var(--text);
    background: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    box-shadow: none;
    transition: 0.3s;
}

.form-control:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
    outline: none;
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
            Edit Data Fasilitas
        </div>


        <div class="isi">

            <form method="POST">


                <!-- NAMA FASILITAS -->

                <div class="mb-4">

                    <label class="form-label">
                        Nama Fasilitas
                    </label>

                    <input
                        type="text"
                        name="nama_fasilitas"
                        class="form-control"
                        value="<?= htmlspecialchars($fasilitas['nama_fasilitas']); ?>"
                        required
                    >

                </div>


                <!-- TOMBOL -->

                <div class="tombol">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-simpan px-4"
                    >
                        Simpan
                    </button>


                    <a
                        href="fasilitas.php"
                        class="btn btn-kembali px-4"
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
