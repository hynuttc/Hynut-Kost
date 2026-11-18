
<?php
include '../database.php';

// CEK ID REVIEW
if (!isset($_GET['id'])) {
    die("ID review tidak ditemukan.");
}

$id = intval($_GET['id']);

// AMBIL DATA REVIEW
$query = mysqli_query($conn, "SELECT * FROM review WHERE review_id = $id");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$review = mysqli_fetch_assoc($query);

if (!$review) {
    die("Data review tidak ditemukan.");
}

// AMBIL LIST BOOKING
$query_booking = mysqli_query($conn, "SELECT * FROM booking ORDER BY booking_id ASC");

// PROSES UPDATE
if (isset($_POST['simpan'])) {

    $booking_id = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);

    $update = mysqli_query(
        $conn,
        "UPDATE review SET
            booking_id = '$booking_id',
            rating = '$rating',
            komentar = '$komentar'
         WHERE review_id = $id"
    );

    if ($update) {
        header("Location: review.php");
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Data Review</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

.form-select,
input.form-control {
    height: 48px;
}

textarea.form-control {
    height: auto;
    min-height: 110px;
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

        <div class="judul">
            Edit Data Review
        </div>

        <div class="isi">

            <form method="POST">

                <!-- PILIH BOOKING -->
                <div class="mb-4">

                    <label class="form-label">
                        Pilih Booking
                    </label>

                    <select name="booking_id" class="form-select" required>

                        <?php while ($row_booking = mysqli_fetch_assoc($query_booking)): ?>

                            <option
                                value="<?= $row_booking['booking_id']; ?>"
                                <?= ($row_booking['booking_id'] == $review['booking_id']) ? 'selected' : ''; ?>
                            >
                                Booking #<?= $row_booking['booking_id']; ?>
                                (Tgl Survey: <?= htmlspecialchars($row_booking['tanggal_survey']); ?>)
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <!-- RATING -->
                <div class="mb-4">

                    <label class="form-label">
                        Rating (1 - 5)
                    </label>

                    <select name="rating" class="form-select" required>

                        <option value="5" <?= ($review['rating'] == 5) ? 'selected' : ''; ?>>5 - Sangat Puas</option>
                        <option value="4" <?= ($review['rating'] == 4) ? 'selected' : ''; ?>>4 - Puas</option>
                        <option value="3" <?= ($review['rating'] == 3) ? 'selected' : ''; ?>>3 - Cukup</option>
                        <option value="2" <?= ($review['rating'] == 2) ? 'selected' : ''; ?>>2 - Kurang</option>
                        <option value="1" <?= ($review['rating'] == 1) ? 'selected' : ''; ?>>1 - Sangat Kurang</option>

                    </select>

                </div>

                <!-- KOMENTAR -->
                <div class="mb-4">

                    <label class="form-label">
                        Komentar
                    </label>

                    <textarea
                        name="komentar"
                        class="form-control"
                        rows="4"
                        required
                    ><?= htmlspecialchars($review['komentar']); ?></textarea>

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
                        href="review.php"
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
