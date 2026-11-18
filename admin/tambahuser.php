<?php
include '../database.php';

/* =====================================================
   PROSES SIMPAN USER
   ===================================================== */

if (isset($_POST['simpan'])) {

    $nama              = mysqli_real_escape_string($conn, $_POST['nama']);
    $email             = mysqli_real_escape_string($conn, $_POST['email']);
    $password          = mysqli_real_escape_string($conn, $_POST['password']);
    $role              = mysqli_real_escape_string($conn, $_POST['role']);
    $no_wa             = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);

    /* =================================================
       SIMPAN KE DATABASE TABEL USER
       ================================================= */

    $query = "INSERT INTO user 
              (nama, email, password, role, no_wa, status_verifikasi)
              VALUES 
              ('$nama', '$email', '$password', '$role', '$no_wa', '$status_verifikasi')";

    if (mysqli_query($conn, $query)) {

        header("Location: user.php");
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
    <title>Tambah Data User</title>

    <!-- Bootstrap 4 -->
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
.btn-primary {
    background-color: #E8A1B8 !important;
    border-color: #E8A1B8 !important;
    color: #FFFFFF !important;
    border-radius: 20px;
}

.btn-primary:hover {
    background-color: #D985A3 !important;
    border-color: #D985A3 !important;
    color: #FFFFFF !important;
}

/* TOMBOL KEMBALI */
.btn-secondary {
    background-color: #D985A3 !important;
    border-color: #D985A3 !important;
    color: #FFFFFF !important;
    border-radius: 20px;
}

.btn-secondary:hover {
    background-color: #C87594 !important;
    border-color: #C87594 !important;
    color: #FFFFFF !important;
}
 /* TOMBOL TAMBAH BOOKING */
.btn-tambah-booking {
    display: inline-block;
    background-color: #E8A1B8 !important;
    color: #FFFFFF !important;
    border: none !important;
    border-radius: 30px !important;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none !important;
    box-shadow: none !important;
}

.btn-tambah-booking:hover {
    background-color: #D985A3 !important;
    color: #FFFFFF !important;
    text-decoration: none !important;
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
        Tambah Data User
    </div>

    <div class="garis"></div>

    <!-- FORM TAMBAH USER -->
    <form method="POST">

        <!-- NAMA -->
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
        </div>

        <!-- EMAIL -->
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="Masukkan email" required>
        </div>

        <!-- PASSWORD -->
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>

        <!-- ROLE -->
        <div class="form-group">
            <label>Role / Hak Akses</label>
            <select name="role" class="form-control" required>
                <option value="">-- Pilih Role --</option>
                <option value="Admin">Admin</option>
                <option value="Owner">Owner</option>
                <option value="User">User</option>
            </select>
        </div>

        <!-- NO WA -->
        <div class="form-group">
            <label>Nomor WhatsApp</label>
            <input type="text" name="no_wa" class="form-control" placeholder="Contoh: 081234567890">
        </div>

        <!-- STATUS VERIFIKASI -->
        <div class="form-group">
            <label>Status Verifikasi</label>
            <select name="status_verifikasi" class="form-control" required>
                <option value="Menunggu">Menunggu</option>
                <option value="Terverifikasi">Terverifikasi</option>
            </select>
        </div>

<!-- TOMBOL TAMBAH DAN KEMBALI -->
<div class="tombol">
    <button type="submit" name="simpan" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah
    </button>

    <a href="user.php" class="btn btn-secondary">
        Kembali
    </a>
</div>

</div>

</body>
</html>