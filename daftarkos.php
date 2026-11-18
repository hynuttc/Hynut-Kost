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

$filter_tipe = $_GET['tipe'] ?? '';
$cari        = trim($_GET['cari'] ?? '');

$sql = "
    SELECT k.*, 
    (
        SELECT fk.path_foto 
        FROM foto_kos fk 
        WHERE fk.kos_id = k.kos_id 
        ORDER BY fk.foto_id ASC LIMIT 1
    ) AS foto_kos,
    (
        SELECT MIN(km.harga) 
        FROM kamar km 
        WHERE km.kos_id = k.kos_id
    ) AS harga_mulai,
    (
        SELECT ROUND(AVG(r.rating), 1)
        FROM review r
        JOIN booking b ON r.booking_id = b.booking_id
        JOIN kamar km ON b.kamar_id = km.kamar_id
        WHERE km.kos_id = k.kos_id
    ) AS avg_rating
    FROM kos k
";

$where = [];
if ($filter_tipe !== '') {
    $where[] = "k.tipe_kos = '" . mysqli_real_escape_string($db, $filter_tipe) . "'";
}
if ($cari !== '') {
    $where[] = "(k.nama_kos LIKE '%" . mysqli_real_escape_string($db, $cari) . "%' OR k.alamat LIKE '%" . mysqli_real_escape_string($db, $cari) . "%')";
}
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY k.kos_id DESC";

$result = mysqli_query($db, $sql);

function rupiah($angka) {
    return ($angka > 0) ? 'Rp ' . number_format((int)$angka, 0, ',', '.') : 'Harga belum tersedia';
}
$default_image = 'uploads/tampilankos.jpeg';

// Data pengguna untuk navbar jika login
$user_id = $_SESSION['user_id'] ?? null;
$user = [];
if ($user_id) {
    $q_user = mysqli_query($db, "SELECT * FROM user WHERE user_id = '$user_id'");
    if ($q_user) {
        $user = mysqli_fetch_assoc($q_user);
    }
}

$foto_session = (!empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto']))
    ? 'uploads/' . $_SESSION['foto'] . '?v=' . time()
    : 'uploads/default.jpeg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kos - Hynut KOST</title>
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

/* NAVBAR */
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
}

.logo {
    display: inline-flex;
    align-items: center;
    gap: 10px;
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

.nav-menu {
    display: flex;
    gap: 30px;
    align-items: center;
}

.nav-menu a {
    color: var(--muted);
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}

.nav-menu a:hover,
.nav-menu a.active-link {
    color: var(--pink-dark);
    font-weight: 700;
}

.btn-login {
    background: var(--pink);
    color: var(--white) !important;
    padding: 8px 22px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 13px;
    transition: 0.3s;
}

.btn-login:hover {
    background: var(--pink-dark);
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
}

.navbar-profile-img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--pink);
}

/* LAYOUT UTAMA */
.container {
    max-width: 1300px;
    margin: 35px auto;
    padding: 0 25px;
}

/* WELCOME BANNER */
.welcome-banner {
    min-height: 150px;
    padding: 30px 38px;
    border-radius: 22px;
    background: linear-gradient(120deg, #F6D5E0, #FFF0F5);
    border: 1px solid var(--pink-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
    margin-bottom: 25px;
}

.welcome-text h1 {
    font-size: 28px;
    color: var(--text);
    margin-bottom: 6px;
    font-weight: 800;
}

.welcome-text p {
    color: var(--muted);
    font-size: 14px;
}

.welcome-circle {
    position: absolute;
    width: 220px;
    height: 220px;
    background: rgba(255, 255, 255, 0.35);
    border-radius: 50%;
    right: 80px;
    top: 20px;
}

/* FILTER BAR */
.filter-header {
    background: var(--white);
    padding: 20px 25px;
    border-radius: 22px;
    border: 1px solid var(--pink-border);
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.filter-header input,
.filter-header select {
    padding: 12px 18px;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    outline: none;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: var(--text);
    background: var(--white);
    transition: 0.3s;
}

.filter-header input:focus,
.filter-header select:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

.filter-header input[type="text"] {
    flex: 1;
    min-width: 220px;
}

.filter-header button {
    background: var(--pink);
    color: var(--white);
    border: none;
    padding: 12px 24px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 4px 14px rgba(217, 133, 163, 0.18);
}

.filter-header button:hover {
    background: var(--pink-dark);
    transform: translateY(-1px);
}

/* GRID DAFTAR KOS */
.kos-grid-2col {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px;
}

.kos-card-horizontal {
    background: var(--white);
    border-radius: 22px;
    border: 1px solid var(--pink-border);
    padding: 20px;
    display: flex;
    gap: 20px;
    align-items: center;
    transition: 0.3s;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.kos-card-horizontal:hover {
    transform: translateY(-3px);
    border-color: var(--pink);
    box-shadow: 0 8px 22px rgba(217, 133, 163, 0.13);
}

.kos-card-horizontal img {
    width: 160px;
    height: 130px;
    object-fit: cover;
    border-radius: 16px;
    background: var(--pink-light);
}

.kos-info-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
}

.kos-info-box h3 {
    font-size: 17px;
    color: var(--text);
    font-weight: 800;
    margin-bottom: 6px;
}

.kos-info-box .lokasi {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
    line-height: 1.4;
}

.kos-info-box .harga {
    font-size: 15px;
    font-weight: 800;
    color: var(--pink-dark);
    margin-bottom: 4px;
}

.kos-info-box .rating {
    font-size: 12px;
    color: #D9A13B;
    font-weight: 700;
    margin-bottom: 10px;
}

/* TOMBOL DETAIL */
.btn-detail {
    align-self: flex-start;
    background: var(--pink-light);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    transition: 0.3s;
}

.btn-detail:hover {
    background: var(--pink);
    color: var(--white);
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .kos-grid-2col {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .navbar-container {
        padding: 12px 18px;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
    }

    .nav-menu {
        display: none;
    }

    .container {
        padding: 0 15px;
        margin: 20px auto;
    }

    .welcome-banner {
        padding: 25px 20px;
    }

    .welcome-text h1 {
        font-size: 22px;
    }

    .filter-header {
        padding: 18px;
    }

    .filter-header input[type="text"],
    .filter-header select,
    .filter-header button {
        width: 100%;
    }

    .kos-card-horizontal {
        flex-direction: column;
        align-items: stretch;
    }

    .kos-card-horizontal img {
        width: 100%;
        height: 180px;
    }

    .btn-detail {
        align-self: stretch;
        text-align: center;
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

        <div class="nav-menu">
            <a href="index.php">Beranda</a>
            <a href="daftarkos.php"> Kost</a>
            <a href="tentang.php">Tentang</a>
        </div>

        <div>
            <?php if ($user_id): ?>
                <a href="profil.php" class="btn-profile">
                    <img src="<?= htmlspecialchars($foto_session); ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
                    <span><?= htmlspecialchars($user['nama'] ?? $_SESSION['nama'] ?? 'User'); ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- CONTAINER UTAMA -->
<div class="container">

    <!-- WELCOME BANNER -->
    <section class="welcome-banner">
        <div class="welcome-circle"></div>
        <div class="welcome-text">
            <h1>Daftar Seluruh Kost</h1>
            <p>Temukan berbagai pilihan kost terbaik yang sesuai dengan kebutuhan dan anggaranmu.</p>
        </div>
    </section>

    <!-- FILTER BAR -->
    <form action="" method="GET" class="filter-header">
        <input type="text" name="cari" placeholder="Cari nama atau lokasi kos..." value="<?= htmlspecialchars($cari); ?>">
        <select name="tipe">
            <option value="">Semua Tipe Kost</option>
            <option value="Putra" <?= $filter_tipe === 'Putra' ? 'selected' : ''; ?>>Putra</option>
            <option value="Putri" <?= $filter_tipe === 'Putri' ? 'selected' : ''; ?>>Putri</option>
            <option value="Campur" <?= $filter_tipe === 'Campur' ? 'selected' : ''; ?>>Campur</option>
        </select>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari Kost</button>
    </form>

    <!-- GRID KOS -->
    <div class="kos-grid-2col">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($k = mysqli_fetch_assoc($result)): ?>
                <?php 
                    $foto = !empty($k['foto_kos']) ? 'uploads/'.$k['foto_kos'] : $default_image; 
                    $rating_val = !empty($k['avg_rating']) ? $k['avg_rating'] : 4.5;
                ?>
                <div class="kos-card-horizontal">
                    <img src="<?= htmlspecialchars($foto); ?>" alt="<?= htmlspecialchars($k['nama_kos']); ?>" onerror="this.src='<?= $default_image; ?>';">
                    <div class="kos-info-box">
                        <div>
                            <h3><?= htmlspecialchars($k['nama_kos']); ?></h3>
                            <p class="lokasi"><i class="fa-solid fa-location-dot" style="color: var(--pink);"></i> <?= htmlspecialchars($k['alamat']); ?></p>
                            <p class="harga"><?= rupiah($k['harga_mulai']); ?><small style="font-weight:normal; color:var(--muted); font-size:11px;">/bulan</small></p>
                            <p class="rating">★ <?= number_format((float)$rating_val, 1); ?></p>
                        </div>
                        <a href="detail.php?id=<?= $k['kos_id']; ?>" class="btn-detail">
                            <i class="fa-solid fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; background:#fff;  border:1px solid var(--pink-border); border-radius:22px; text-align:center; padding:50px 20px;">
                <div style="font-size:50px; color:var(--pink); margin-bottom:12px;"><i class="fa-regular fa-folder-open"></i></div>
                <h3 style="color:var(--text); font-size:18px; margin-bottom:6px; font-weight:800;">Kost Tidak Ditemukan</h3>
                <p style="color:var(--muted); font-size:13px;">Maaf, kost yang kamu cari tidak tersedia atau kata kunci kurang tepat.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>