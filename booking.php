<?php
session_name('HYNUTKOST_SESSION');
session_start();
require_once __DIR__ . '/database.php';

// Menyesuaikan koneksi database menggunakan variabel $conn / $db / $koneksi
if (!isset($db) && isset($conn)) {
    $db = $conn;
} elseif (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}

if (!isset($db) || !($db instanceof mysqli)) {
    die("Koneksi database gagal. Periksa kembali file database.php.");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses Hapus Booking (Fungsi tetap berjalan seperti semula)
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    if ($booking_id > 0) {
        $delete = mysqli_query($db, "DELETE FROM booking WHERE booking_id = '$booking_id' AND user_id = '$user_id'");
        if ($delete) {
            echo "<script>alert('Booking berhasil dihapus!'); window.location.href='booking.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus booking.'); window.location.href='booking.php';</script>";
        }
        exit;
    }
}

// Mengambil data pengguna
$stmt_user = mysqli_prepare($db, "SELECT * FROM user WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_user, "s", $user_id);
mysqli_stmt_execute($stmt_user);

$query_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

// Fungsi keamanan output
function e($value) {
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}

// Foto profil
$foto_user = (!empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto'])) 
    ? 'uploads/' . $_SESSION['foto'] . '?v=' . time() 
    : 'uploads/default.jpeg';

// Mengambil data booking dan foto kos terkait
$q_booking = mysqli_query($db, "
    SELECT 
        b.booking_id, 
        b.tanggal_survey, 
        COALESCE(b.status_booking, 'Menunggu') AS status_booking, 
        k.nama_kos, 
        k.alamat, 
        km.harga,
        (
            SELECT fk.path_foto
            FROM foto_kos fk
            WHERE fk.kos_id = k.kos_id
            ORDER BY fk.foto_id ASC
            LIMIT 1
        ) AS foto_kos
    FROM booking b
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN kos k ON km.kos_id = k.kos_id
    WHERE b.user_id = '$user_id'
    ORDER BY b.booking_id DESC
");

$default_image = 'uploads/tampilankos.jpeg';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan / Booking - Hynut KOST</title>
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

/* RESET DAN DASAR */
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
    box-shadow: 0 2px 8px rgba(217, 133, 163, 0.06);
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

.logo-title,
.logo-subtitle {
    color: var(--pink-dark);
}

.logo-title {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.logo-subtitle {
    font-size: 14px;
    font-weight: 700;
}

/* TOMBOL KEMBALI */
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

/* PROFIL NAVBAR */
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
    background: linear-gradient(180deg, #FFFFFF 0%, #FCEAF0 100%);
    font-size: 12px;
    color: var(--pink-dark);
    font-weight: 600;
}

/* =========================================
   MAIN CONTENT
========================================= */

.main-content {
    min-width: 0;
}

/* BANNER */
.welcome-banner {
    min-height: 170px;
    padding: 30px 38px;
    border-radius: 22px;
    background: linear-gradient(120deg, #F6D5E0, #FFF0F5);
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

/* JUDUL DAFTAR PESANAN */
.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    gap: 10px;
}

.section-heading h2 {
    font-size: 22px;
    color: var(--text);
    font-weight: 800;
}

.total-favorit {
    background: var(--pink-light);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    padding: 8px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

/* =========================================
   KARTU BOOKING
========================================= */

.fav-list {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.fav-item {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    padding: 30px;
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr) 210px;
    gap: 25px;
    align-items: center;
    transition: 0.3s;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.fav-item:hover {
    transform: translateY(-3px);
    border-color: var(--pink);
    box-shadow: 0 8px 22px rgba(217, 133, 163, 0.13);
}

/* FOTO KOS */
.fav-image {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    height: 155px;
    background: var(--pink-light);
}

.fav-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.4s;
}

.fav-item:hover .fav-image img {
    transform: scale(1.06);
}

/* LOGO BADGE */
.logo-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--white);
    padding: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
}

.logo-badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* INFORMASI BOOKING */
.fav-info {
    min-width: 0;
}

.fav-info h3 {
    font-size: 20px;
    color: var(--text);
    margin-bottom: 6px;
    font-weight: 800;
}

.fav-address {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 8px;
    line-height: 1.5;
}

.fav-price {
    color: var(--pink-dark);
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 8px;
}

.fav-price small {
    font-size: 12px;
    font-weight: normal;
    color: var(--muted);
}

.booking-date {
    font-size: 12px;
    color: var(--muted);
}

/* =========================================
   STATUS BOOKING
========================================= */

.badge-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-block;
    text-align: center;
}

.badge-pending {
    background: #FFF0CC;
    color: #987329;
    border: 1px solid #F5E2AA;
}

.badge-approved {
    background: #D8EFE0;
    color: #397B53;
    border: 1px solid #BFE2CB;
}

.badge-rejected {
    background: #F8DCE5;
    color: #B65375;
    border: 1px solid #F0C2D1;
}

/* =========================================
   TOMBOL AKSI
========================================= */

.fav-actions {
    border-left: 1px solid var(--pink-border);
    padding-left: 22px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* TOMBOL CETAK */
.btn-cetak {
    display: block;
    text-align: center;
    background: var(--pink-light);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    padding: 11px 15px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    transition: 0.3s;
}

.btn-cetak:hover {
    background: var(--pink);
    color: var(--white);
}

/* CETAK DISABLED */
.btn-cetak-disabled {
    display: block;
    text-align: center;
    background: #F7F1F3;
    color: #B0A3A8;
    border: 1px solid var(--pink-border);
    padding: 11px 15px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    cursor: not-allowed;
}

/* TOMBOL HAPUS */
.btn-hapus {
    display: block;
    text-align: center;
    background: var(--white);
    border: 1px solid var(--pink-border);
    color: var(--pink-dark);
    padding: 11px 15px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}

.btn-hapus:hover {
    background: var(--pink-light);
    color: #B65375;
    border-color: var(--pink);
}

/* =========================================
   JIKA BOOKING KOSONG
========================================= */

.empty-favorite {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    text-align: center;
    padding: 50px 20px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.05);
}

.empty-icon {
    font-size: 60px;
    color: var(--pink);
    margin-bottom: 15px;
}

.empty-favorite h3 {
    color: var(--text);
    font-size: 20px;
    margin-bottom: 8px;
    font-weight: 800;
}

.empty-favorite p {
    color: var(--muted);
    font-size: 14px;
    margin-bottom: 20px;
}

.btn-cari {
    display: inline-block;
    background: var(--pink);
    color: var(--white);
    padding: 12px 24px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    transition: 0.3s;
    box-shadow: 0 6px 16px rgba(217, 133, 163, 0.18);
}

.btn-cari:hover {
    background: var(--pink-dark);
    transform: translateY(-2px);
}

/* =========================================
   RESPONSIVE TABLET
========================================= */

@media (max-width: 1000px) {
    .layout {
        grid-template-columns: 240px 1fr;
        gap: 20px;
    }

    .fav-item {
        grid-template-columns: 160px minmax(0, 1fr);
    }

    .fav-actions {
        grid-column: 2;
        border-left: none;
        border-top: 1px solid var(--pink-border);
        padding: 15px 0 0;
        flex-direction: row;
    }

    .fav-actions > * {
        flex: 1;
    }
}

/* =========================================
   RESPONSIVE MOBILE
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

    .fav-item {
        grid-template-columns: 1fr;
        padding: 22px;
    }

    .fav-image {
        height: 180px;
    }
}

/* =========================================
   RESPONSIVE HP KECIL
========================================= */

@media (max-width: 480px) {
    .welcome-banner {
        min-height: 120px;
    }

    .welcome-text h1 {
        font-size: 18px;
    }

    .section-heading h2 {
        font-size: 17px;
    }

    .fav-actions {
        flex-direction: column;
    }

    .fav-actions > * {
        width: 100%;
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
                <i class="fa-solid fa-arrow-left"></i> Kembali ke index
            </a>
        </div>

        <a href="profil.php" class="btn-profile">
            <img
                src="<?= e($foto_user); ?>"
                alt="Profil"
                class="navbar-profile-img"
                onerror="this.src='uploads/default.jpeg';"
            >
            <span><?= e($user['nama'] ?? $_SESSION['nama']); ?></span>
        </a>
    </div>
</nav>

<!-- LAYOUT -->
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="avatar-container">
                <img
                    src="<?= e($foto_user); ?>"
                    alt="Foto Profil"
                    onerror="this.src='uploads/default.jpeg';"
                >
            </div>
            <strong><?= e($user['nama']); ?></strong>
            <span><?= e($user['role'] ?? 'pengguna'); ?></span>
        </div>

        <ul class="menu-list">
            <li>
                <a href="profil.php">
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
                <a href="booking.php" class="active">
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
            <p>Temukan Kost Impianmu!</p>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- BANNER PESANAN -->
        <section class="welcome-banner">
            <div class="welcome-circle"></div>
            <div class="welcome-text">
                <h1>Status Pesanan / Booking</h1>
                <p>Pantau riwayat dan status pengajuan booking kost kamu di sini.</p>
            </div>
            <div class="welcome-icon">
                <img src="uploads/logohynut.png" alt="Logo Hynut Kost">
            </div>
        </section>

        <!-- JUDUL DAFTAR PESANAN -->
        <div class="section-heading">
            <h2>Daftar Pengajuan Booking</h2>
            <span class="total-favorit">
                <?php 
                    // Hitung jumlah baris data booking secara aman
                    $total_booking = mysqli_num_rows($q_booking);
                    echo $total_booking . " Pesanan";
                ?>
            </span>
        </div>

        <!-- DAFTAR BOOKING -->
        <?php if ($total_booking > 0): ?>

            <div class="fav-list">
                <?php while ($b = mysqli_fetch_assoc($q_booking)): ?>
                    <?php 
                        $foto = !empty($b['foto_kos'])
                            ? 'uploads/' . $b['foto_kos']
                            : $default_image;

                        $st = strtolower(trim($b['status_booking'])); 
                        $is_approved = ($st === 'disetujui' || $st === 'approved' || $st === 'selesai');
                    ?>

                    <article class="fav-item">
                        <!-- FOTO KOS -->
                        <div class="fav-image">
                            <img
                                src="<?= e($foto); ?>"
                                alt="<?= e($b['nama_kos']); ?>"
                                onerror="this.onerror=null; this.src='<?= e($default_image); ?>';"
                            >
                            <div class="logo-badge">
                                <img src="uploads/logohynut.png" alt="Logo Hynut Kost">
                            </div>
                        </div>

                        <!-- INFORMASI BOOKING -->
                        <div class="fav-info">
                            <h3><?= e($b['nama_kos']); ?></h3>
                            <p class="fav-address">
                                <i class="fa-solid fa-location-dot" style="color: var(--pink);"></i> <?= e($b['alamat']); ?>
                            </p>

                            <div class="fav-price">
                                Rp <?= number_format((float)($b['harga'] ?? 0), 0, ',', '.'); ?>
                                <small>/bulan</small>
                            </div>

                            <div class="booking-date">
                                <i class="fa-regular fa-calendar-days"></i> Tgl Survey / Masuk: <strong><?= date('d M Y', strtotime($b['tanggal_survey'])); ?></strong>
                            </div>

                            <div style="margin-top: 10px;">
                                <?php if ($is_approved): ?>
                                    <span class="badge-status badge-approved"><i class="fa-solid fa-check"></i> Disetujui</span>
                                <?php elseif ($st === 'ditolak'): ?>
                                    <span class="badge-status badge-rejected"><i class="fa-solid fa-xmark"></i> Ditolak</span>
                                <?php else: ?>
                                    <span class="badge-status badge-pending"><i class="fa-solid fa-clock"></i> Menunggu Konfirmasi</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="fav-actions">
                            <?php if ($is_approved): ?>
                                <a href="cetak_booking.php?id=<?= $b['booking_id']; ?>" target="_blank" class="btn-cetak">
                                    <i class="fa-solid fa-print"></i> Cetak Laporan
                                </a>
                            <?php else: ?>
                                <div class="btn-cetak-disabled" title="Hanya booking yang disetujui yang dapat dicetak">
                                    <i class="fa-solid fa-print"></i> Cetak Laporan
                                </div>
                            <?php endif; ?>

                            <a href="booking.php?action=hapus&id=<?= $b['booking_id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?');" class="btn-hapus">
                                <i class="fa-solid fa-trash-can"></i> Hapus Pesanan
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

        <?php else: ?>

            <!-- JIKA BELUM ADA BOOKING -->
            <div class="empty-favorite">
                <div class="empty-icon">
                    <i class="fa-regular fa-clipboard"></i>
                </div>
                <h3>Belum Ada Pengajuan Booking</h3>
                <p>Kamu belum pernah mengajukan booking kost. Yuk, cari kost impianmu sekarang!</p>
                <a href="daftarkos.php" class="btn-cari">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari Kost Sekarang
                </a>
            </div>

        <?php endif; ?>

    </main>
</div>

</body>
</html>