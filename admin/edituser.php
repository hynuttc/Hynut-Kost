<?php
include '../database.php';

// CEK ID USER
if (!isset($_GET['id'])) {
    die("ID user tidak ditemukan.");
}

$id = intval($_GET['id']);

// AMBIL DATA USER
$query = mysqli_query($conn, "SELECT * FROM user WHERE user_id = $id");

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("Data user tidak ditemukan.");
}

// PROSES UPDATE USER
if (isset($_POST['simpan'])) {

    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $no_wa = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);

    // CEK EMAIL DUPLIKAT
    $cek_email = mysqli_query(
        $conn,
        "SELECT user_id FROM user
         WHERE email = '$email'
         AND user_id != $id"
    );

    if (mysqli_num_rows($cek_email) > 0) {

        echo "<script>
            alert('Email sudah digunakan oleh user lain!');
            window.location.href = 'edituser.php?id=$id';
        </script>";
        exit;

    } else {

        // UPDATE DATA
        $update = mysqli_query(
            $conn,
            "UPDATE user SET
                nama = '$nama',
                email = '$email',
                password = '$password',
                role = '$role',
                no_wa = '$no_wa',
                status_verifikasi = '$status_verifikasi'
             WHERE user_id = $id"
        );

        if ($update) {

            header("Location: user.php");
            exit;

        } else {

            die("Gagal menyimpan data: " . mysqli_error($conn));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data User</title>

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

/* Background */
body {
    background-color: var(--pink-bg);
    font-family: Arial, sans-serif;
    color: var(--text);
}

/* Container */
.container-edit {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

/* Card */
.card-edit {
    background: white;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(217, 133, 163, 0.08);
}

/* Judul */
.judul {
    font-size: 28px;
    font-weight: 600;
    padding: 18px 22px;
    border-bottom: 1px solid var(--pink-border);
    background-color: var(--pink-light);
    color: var(--pink-dark);
}

/* Isi */
.isi {
    padding: 25px;
    background-color: white;
}

/* Label */
.form-label {
    font-size: 17px;
    margin-bottom: 8px;
    color: var(--pink-dark);
    font-weight: 500;
}

/* Input dan Select */
.form-control,
.form-select {
    height: 48px;
    font-size: 16px;
    border: 1px solid var(--pink-border);
    border-radius: 7px;
    color: var(--text);
    background-color: white;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 0.2rem rgba(232, 161, 184, 0.20);
    outline: none;
}

/* Tombol */
.tombol {
    margin-top: 20px;
}

/* Tombol Simpan */
.btn-success {
    background-color: var(--pink) !important;
    border-color: var(--pink) !important;
    color: white !important;
    border-radius: 7px;
}

.btn-success:hover {
    background-color: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
}

/* Tombol Kembali */
.btn-secondary {
    background-color: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
    color: white !important;
    border-radius: 7px;
}

.btn-secondary:hover {
    background-color: #C87593 !important;
    border-color: #C87593 !important;
}

/* Responsif */
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
            Edit Data User
        </div>

        <div class="isi">

            <form method="POST">

                <!-- NAMA -->
                <div class="mb-4">
                    <label class="form-label">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="<?= htmlspecialchars($user['nama']); ?>"
                        required
                    >
                </div>

                <!-- EMAIL -->
                <div class="mb-4">
                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($user['email']); ?>"
                        required
                    >
                </div>

                <!-- PASSWORD -->
                <div class="mb-4">
                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="text"
                        name="password"
                        class="form-control"
                        value="<?= htmlspecialchars($user['password']); ?>"
                        required
                    >
                </div>

                <!-- ROLE -->
                <div class="mb-4">
                    <label class="form-label">
                        Role / Hak Akses
                    </label>

                    <select name="role" class="form-select" required>
                        <option value="Admin" <?= ($user['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="Owner" <?= ($user['role'] == 'Owner') ? 'selected' : ''; ?>>Owner</option>
                        <option value="User" <?= ($user['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>

                <!-- NO WA -->
                <div class="mb-4">
                    <label class="form-label">
                        Nomor WhatsApp
                    </label>

                    <input
                        type="text"
                        name="no_wa"
                        class="form-control"
                        value="<?= htmlspecialchars($user['no_wa']); ?>"
                    >
                </div>

                <!-- STATUS VERIFIKASI -->
                <div class="mb-4">
                    <label class="form-label">
                        Status Verifikasi
                    </label>

                    <select name="status_verifikasi" class="form-select" required>
                        <option value="Menunggu" <?= ($user['status_verifikasi'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Terverifikasi" <?= ($user['status_verifikasi'] == 'Terverifikasi') ? 'selected' : ''; ?>>Terverifikasi</option>
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
                        href="user.php"
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
