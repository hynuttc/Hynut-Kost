<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

// Menyesuaikan koneksi database secara fleksibel
if (!isset($db) || !($db instanceof mysqli)) {
    if (isset($conn) && ($conn instanceof mysqli)) {
        $db = $conn;
    } elseif (isset($koneksi) && ($koneksi instanceof mysqli)) {
        $db = $koneksi;
    }
}

if (!isset($db) || !($db instanceof mysqli)) {
    die("Koneksi database gagal. Periksa kembali file database.php.");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$query = mysqli_query($db, "SELECT * FROM user WHERE user_id = '$user_id'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = mysqli_real_escape_string($db, trim($_POST['nama'] ?? ''));
    $email = mysqli_real_escape_string($db, trim($_POST['email'] ?? ''));
    $no_wa = mysqli_real_escape_string($db, trim($_POST['no_wa'] ?? ''));

    $nama_foto = $_SESSION['foto'] ?? 'default.jpeg';
    $foto_lama = $nama_foto;
    $upload_berhasil = false;

    if ($nama === '' || $email === '' || $no_wa === '') {
        $error = "Semua kolom wajib diisi!";
    }

    // UPLOAD FOTO
    if (
        empty($error) &&
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $error = "Terjadi kesalahan saat mengunggah foto!";
        } else {

            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_ext = strtolower(pathinfo(
                $_FILES['foto']['name'],
                PATHINFO_EXTENSION
            ));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed_ext, true)) {
                $error = "Format foto harus JPG, JPEG, PNG, atau GIF!";
            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $error = "Ukuran foto maksimal 2 MB!";
            } elseif (getimagesize($file_tmp) === false) {
                $error = "File yang diunggah bukan gambar yang valid!";
            } else {

                $nama_foto = 'user_' . $user_id . '.' . $file_ext;
                $tujuan = 'uploads/' . $nama_foto;

                if (move_uploaded_file($file_tmp, $tujuan)) {
                    $upload_berhasil = true;
                } else {
                    $error = "Gagal mengunggah foto baru!";
                }
            }
        }
    }

    // UPDATE DATABASE
    if (empty($error)) {

        $update = mysqli_query(
            $db,
            "UPDATE user SET
                nama = '$nama',
                email = '$email',
                no_wa = '$no_wa'
             WHERE user_id = '$user_id'"
        );

        if ($update) {

            if ($upload_berhasil) {

                // Hapus foto lama setelah foto baru berhasil disimpan
                if (
                    !empty($foto_lama) &&
                    $foto_lama !== 'default.jpeg' &&
                    $foto_lama !== $nama_foto
                ) {
                    $old_file = 'uploads/' . basename($foto_lama);

                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }

                // Bersihkan file dengan ekstensi lama
                foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                    $old_file = 'uploads/user_' . $user_id . '.' . $ext;

                    if (
                        file_exists($old_file) &&
                        basename($old_file) !== $nama_foto
                    ) {
                        unlink($old_file);
                    }
                }

                $_SESSION['foto'] = $nama_foto;
            }

            $_SESSION['nama'] = $nama;

            header("Location: profil.php?update=" . time());
            exit;

        } else {
            $error = "Gagal memperbarui profil!";
        }
    }
}

// FOTO PROFIL
$foto_user = (
    !empty($_SESSION['foto']) &&
    file_exists('uploads/' . basename($_SESSION['foto']))
)
    ? 'uploads/' . basename($_SESSION['foto']) . '?v=' . time()
    : 'uploads/default.jpeg';

$nama_user = htmlspecialchars($user['nama'] ?? '');
$email_user = htmlspecialchars($user['email'] ?? '');
$no_wa_user = htmlspecialchars($user['no_wa'] ?? '');
$role_user = htmlspecialchars($user['role'] ?? 'User');

$foto_html = htmlspecialchars($foto_user);
$nama_html = htmlspecialchars($_SESSION['nama'] ?? $user['nama']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Hynut KOST</title>
    <link rel="icon" href="uploads/logohynut.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

/* =========================================
   RESET DAN DASAR
========================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--pink-bg);
    color: var(--text);
    min-height: 100vh;
}

a {
    text-decoration: none;
}

/* =========================================
   NAVBAR
========================================= */

.navbar {
    background: var(--white);
    border-bottom: 1px solid var(--pink-border);
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(217, 133, 163, 0.07);
}

.navbar-container {
    width: 100%;
    max-width: 1300px;
    margin: auto;
    padding: 14px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.logo {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.logo-icon {
    width: 34px;
    height: 34px;
    object-fit: contain;
}

.logo-text-group {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.logo-title {
    color: var(--pink-dark);
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.logo-subtitle {
    color: var(--pink-dark);
    font-size: 14px;
    font-weight: 700;
}

.nav-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.btn-back-home {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--pink-dark);
    background: var(--pink-light);
    border: 1px solid var(--pink-border);
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    transition: 0.3s;
}

.btn-back-home:hover {
    background: var(--pink);
    color: var(--white);
    transform: translateY(-1px);
}

.btn-profile {
    background: var(--pink-light);
    color: var(--pink-dark) !important;
    border: 1px solid var(--pink-border);
    padding: 5px 15px 5px 5px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 9px;
    font-weight: 700 !important;
    margin-left: auto;
}

.navbar-profile-img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--pink);
}

/* =========================================
   LAYOUT UTAMA
========================================= */

.layout {
    max-width: 1300px;
    margin: 35px auto;
    padding: 0 25px;
    display: grid;
    grid-template-columns: 285px 1fr;
    gap: 28px;
    align-items: start;
}

/* =========================================
   SIDEBAR
========================================= */

.sidebar {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    padding: 28px 20px 0;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.sidebar-user {
    text-align: center;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--pink-border);
}

.avatar-container {
    width: 110px;
    height: 110px;
    margin: 0 auto 13px;
    border-radius: 50%;
    border: 4px solid var(--pink);
    padding: 3px;
    overflow: hidden;
}

.avatar-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.sidebar-user strong {
    display: block;
    font-size: 18px;
    color: var(--text);
    margin-bottom: 4px;
}

.sidebar-user span {
    color: var(--muted);
    font-size: 13px;
    text-transform: capitalize;
}

.menu-list {
    list-style: none;
    margin-top: 20px;
}

.menu-list li {
    margin-bottom: 8px;
}

.menu-list a {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 13px 17px;
    color: var(--text);
    font-size: 14px;
    font-weight: 600;
    border-radius: 12px;
    transition: 0.3s;
}

.menu-list a:hover,
.menu-list a.active {
    background: var(--pink-light);
    color: var(--pink-dark);
    font-weight: 700;
}

.menu-icon {
    font-size: 18px;
    width: 25px;
    text-align: center;
    color: var(--pink-dark);
}

.sidebar-decoration {
    margin: 30px -20px 0;
    padding: 25px 10px 15px;
    text-align: center;
    background: linear-gradient(
        180deg,
        var(--white) 0%,
        var(--pink-light) 100%
    );
    font-size: 12px;
    color: var(--pink-dark);
    font-weight: 600;
}

/* =========================================
   KONTEN UTAMA
========================================= */

.main-content {
    min-width: 0;
}

/* =========================================
   WELCOME BANNER
========================================= */

.welcome-banner {
    min-height: 170px;
    padding: 30px 38px;
    border-radius: 22px;
    background: linear-gradient(
        120deg,
        #F5D2DF,
        var(--pink-light)
    );
    border: 1px solid var(--pink-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
    margin-bottom: 22px;
}

.welcome-text {
    position: relative;
    z-index: 2;
}

.welcome-text h1 {
    font-size: 28px;
    color: var(--text);
    margin-bottom: 8px;
    font-weight: 800;
}

.welcome-text p {
    color: var(--muted);
    font-size: 14px;
}

.welcome-icon {
    position: relative;
    z-index: 2;
    margin-right: 20px;
}

.welcome-icon img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    opacity: 0.9;
}

.welcome-circle {
    position: absolute;
    width: 240px;
    height: 240px;
    background: rgba(255, 255, 255, 0.35);
    border-radius: 50%;
    right: 100px;
    top: 35px;
}

/* =========================================
   KARTU INFORMASI PROFIL
========================================= */

.main-card {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.section-title {
    font-size: 18px;
    color: var(--text);
    font-weight: 800;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* =========================================
   FOTO DAN DETAIL PROFIL
========================================= */

.profile-view-grid {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 30px;
    align-items: center;
}

.profile-avatar-box {
    text-align: center;
}

.profile-avatar-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 4px solid var(--pink);
    padding: 3px;
    overflow: hidden;
    margin: 0 auto 12px;
    background: var(--pink-bg);
}

.profile-avatar-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-details-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.profile-item {
    display: flex;
    justify-content: flex-start;
    gap: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--pink-border);
    font-size: 14px;
}

.profile-label {
    color: var(--muted);
    font-weight: 600;
    width: 160px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-value {
    color: var(--text);
    font-weight: 700;
    text-align: left;
}

/* =========================================
   TOMBOL AKSI
========================================= */

.profile-actions {
    margin-top: 25px;
    display: flex;
    gap: 12px;
}

.btn-edit {
    background: var(--pink);
    color: var(--white);
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
    box-shadow: 0 4px 14px rgba(217, 133, 163, 0.18);
}

.btn-edit:hover {
    background: var(--pink-dark);
    color: var(--white);
    transform: translateY(-1px);
}

.btn-keluar {
    background: var(--white);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
    cursor: pointer;
}

.btn-keluar:hover {
    background: var(--pink-light);
    border-color: var(--pink);
    color: var(--pink-dark);
}

/* =========================================
   RESPONSIVE TABLET
========================================= */

@media (max-width: 1000px) {
    .layout {
        grid-template-columns: 240px 1fr;
        gap: 20px;
    }

    .profile-view-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .profile-avatar-large {
        margin: 0 auto 15px;
    }

    .profile-item {
        justify-content: space-between;
    }
}

/* =========================================
   RESPONSIVE HP
========================================= */

@media (max-width: 768px) {
    .navbar-container {
        padding: 12px 18px;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
    }

    .nav-center {
        position: static;
        transform: none;
        order: 3;
        width: 100%;
        text-align: center;
    }

    .btn-profile {
        margin-left: 0;
        order: 2;
    }

    .layout {
        grid-template-columns: 1fr;
        padding: 0 15px;
        margin: 20px auto;
    }

    .sidebar-decoration {
        display: none;
    }

    .menu-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .menu-list li {
        margin: 0;
    }

    .welcome-banner {
        padding: 25px 20px;
        min-height: 140px;
    }

    .welcome-text h1 {
        font-size: 22px;
    }

    .welcome-icon {
        display: none;
    }

    .main-card {
        padding: 22px;
    }

    .profile-actions {
        flex-direction: column;
    }

    .btn-edit,
    .btn-keluar {
        width: 100%;
        justify-content: center;
    }
}

/* =========================================
   RESPONSIVE HP KECIL
========================================= */

@media (max-width: 480px) {
    .navbar-container {
        padding: 12px;
    }

    .logo-title {
        font-size: 18px;
    }

    .btn-back-home {
        font-size: 12px;
        padding: 8px 14px;
    }

    .layout {
        padding: 0 12px;
    }

    .sidebar {
        padding: 22px 15px 0;
    }

    .menu-list a {
        padding: 11px 10px;
        font-size: 12px;
        gap: 8px;
    }

    .main-card {
        padding: 18px;
    }

    .profile-item {
        flex-direction: column;
        gap: 5px;
    }

    .profile-label {
        width: auto;
    }

    .profile-value {
        overflow-wrap: anywhere;
    }

    .welcome-text h1 {
        font-size: 20px;
    }

    .welcome-text p {
        font-size: 12px;
    }
}

    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">
            <img src="uploads/logohynut.png" alt="Logo Hynut Kost" class="logo-icon">
            <div class="logo-text-group">
                <span class="logo-title">Hynut</span>
                <span class="logo-subtitle">KOST</span>
            </div>
        </a>

        <div class="nav-center">
            <a href="index.php" class="btn-back-home">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <a href="profil.php" class="btn-profile">
            <img src="<?= $foto_html ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
            <span><?= $nama_html ?></span>
        </a>
    </div>
</nav>

<!-- LAYOUT UTAMA -->
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="avatar-container">
                <img src="<?= $foto_html ?>" alt="Foto Profil" onerror="this.src='uploads/default.jpeg';">
            </div>
            <strong><?= $nama_html ?></strong>
            <span><?= $role_user ?></span>
        </div>

        <ul class="menu-list">
            <li>
                <a href="profil.php" class="active">
                    <span class="menu-icon"><i class="fa-solid fa-house"></i></span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="favorit.php">
                    <span class="menu-icon"><i class="fa-solid fa-heart"></i></span>
                    Favorit
                </a>
            </li>
            <li>
                <a href="booking.php">
                    <span class="menu-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    Pesanan
                </a>
            </li>
            <li>
                <a href="tentang.php">
                    <span class="menu-icon"><i class="fa-solid fa-circle-info"></i></span>
                    Tentang
                </a>
            </li>
        </ul>

        <div class="sidebar-decoration">
            <p>Temukan KOST Impianmu!</p>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <!-- HERO BANNER -->
        <section class="welcome-banner">
            <div class="welcome-circle"></div>
            <div class="welcome-text">
                <h1> Halo, <?= $nama_html ?>!</h1>
                <p>Selamat datang kembali di halaman profil kamu.</p>
            </div>
            <div class="welcome-icon">
                <img src="uploads/logohynut.png" alt="Logo Hynut Kost">
            </div>
        </section>

        <!-- KARTU INFORMASI PROFIL -->
        <section class="main-card">
            <div class="section-title">
                <i class="fa-solid fa-user-pen" style="color: var(--pink);"></i> Informasi Profil
            </div>

            <div class="profile-view-grid">
                <div class="profile-avatar-box">
                    <div class="profile-avatar-large">
                        <img src="<?= $foto_html ?>" alt="Foto Profil" onerror="this.src='uploads/default.jpeg';">
                    </div>
                    <span style="font-size: 12px; color: var(--muted); font-weight: 600;">Foto profil akun kamu</span>
                </div>

                <div class="profile-details-list">
                    <div class="profile-item">
                        <span class="profile-label"><i class="fa-solid fa-user"></i> Nama Lengkap</span>
                        <span class="profile-value"><?= $nama_user ?></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label"><i class="fa-solid fa-envelope"></i> Email</span>
                        <span class="profile-value"><?= htmlspecialchars($user['email'] ?? '-') ?></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label"><i class="fa-solid fa-shield-halved"></i> Role</span>
                        <span class="profile-value"><?= $role_user ?></span>
                    </div>
                    <div class="profile-item">
                        <span class="profile-label"><i class="fa-solid fa-phone"></i> No. HP / WA</span>
                        <span class="profile-value"><?= htmlspecialchars($user['no_wa'] ?? '-') ?></span>
                    </div>
                    <div class="profile-item" style="border-bottom: none;">
                        <span class="profile-label"><i class="fa-solid fa-circle-check"></i> Status Verifikasi</span>
                        <span class="profile-value" style="color: #28a745;">Terverifikasi</span>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="edit.php" class="btn-edit">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Profil
                </a>
                <a href="#" id="btnKeluar" class="btn-keluar">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar Akun
                </a>
            </div>
        </section>

    </main>

</div>

<!-- MODAL KUSTOM KONFIRMASI KELUAR -->
<div id="customLogoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: #ffffff; padding: 25px 30px; border-radius: 22px; border: 1px solid #f3dce8; width: 350px; text-align: center; box-shadow: 0 10px 25px rgba(199,47,120,0.1);">
        <div style="font-size: 38px; color: #c72f78; margin-bottom: 10px;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <h3 style="font-size: 18px; color: #17213c; margin-bottom: 8px; font-weight: 800;">Konfirmasi Keluar</h3>
        <p style="font-size: 13px; color: #626274; margin-bottom: 20px;">Apakah kamu yakin ingin keluar dari akun?</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button id="btnBatalLogout" style="flex: 1; padding: 10px; border-radius: 30px; border: 1px solid #f5c7dc; background: #fff0f7; color: #c72f78; font-weight: 700; font-size: 13px; cursor: pointer;">Batal</button>
            <button id="btnYaLogout" style="flex: 1; padding: 10px; border-radius: 30px; border: none; background: #c72f78; color: #fff; font-weight: 700; font-size: 13px; cursor: pointer;">Ya, Keluar</button>
        </div>
    </div>
</div>

<script>
const btnKeluar = document.getElementById('btnKeluar');
const customLogoutModal = document.getElementById('customLogoutModal');
const btnBatalLogout = document.getElementById('btnBatalLogout');
const btnYaLogout = document.getElementById('btnYaLogout');

if (btnKeluar) {
    btnKeluar.addEventListener('click', function(e) {
        e.preventDefault();
        customLogoutModal.style.display = 'flex';
    });
}

if (btnBatalLogout) {
    btnBatalLogout.addEventListener('click', function() {
        customLogoutModal.style.display = 'none';
    });
}

if (btnYaLogout) {
    btnYaLogout.addEventListener('click', function() {
        window.location.href = 'logout.php';
    });
}

window.addEventListener('click', function(e) {
    if (e.target === customLogoutModal) {
        customLogoutModal.style.display = 'none';
    }
});
</script>

</body>
</html>