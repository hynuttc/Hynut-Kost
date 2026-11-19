<?php
session_start();
require_once '../database.php';

// ========================================
// LOGIKA AMBIL NAMA & FOTO BERDASARKAN ID UNIK
// ========================================
$current_user_id = $_SESSION['user_id'] ?? 0;
$nama_owner_aktif = $_SESSION['nama'] ?? 'Hynut';

if ($current_user_id) {
    $q_user_aktif = mysqli_query($conn, "SELECT nama FROM user WHERE user_id = '$current_user_id' LIMIT 1");
    if ($q_user_aktif && $row_ua = mysqli_fetch_assoc($q_user_aktif)) {
        $nama_owner_aktif = $row_ua['nama'];
    }
}

$found_foto = 'default.jpeg';
$extensions = ['jpg', 'jpeg', 'png', 'gif'];

if ($current_user_id) {
    foreach ($extensions as $ext) {
        $filename = 'user_' . $current_user_id . '.' . $ext;
        if (file_exists(__DIR__ . '/../uploads/' . $filename)) {
            $found_foto = $filename;
            break;
        }
    }
}
$foto_path_owner = '../uploads/' . $found_foto . '?v=' . time();

$nama_bulan_indo = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$tahun_ini = date('Y');
$bulan_ini = date('m');
$bulan_lalu = date('m', strtotime('-1 month'));
$tahun_bulan_lalu = date('Y', strtotime('-1 month'));

// 1. TOTAL PENDAPATAN & BOOKING DISETUJUI
$q_total = mysqli_query($conn, "SELECT SUM(k.harga) AS total_pendapatan, COUNT(b.booking_id) AS total_booking 
                              FROM booking b
                              JOIN kamar k ON b.kamar_id = k.kamar_id
                              WHERE b.status_booking IN ('Disetujui', 'Terisi')");
$data_total = mysqli_fetch_assoc($q_total);
$total_pendapatan = $data_total['total_pendapatan'] ?? 0;
$booking_disetujui = $data_total['total_booking'] ?? 0;

// 2. OKUPANSI KAMAR
$q_kamar_total = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kamar");
$total_kamar = mysqli_fetch_assoc($q_kamar_total)['total'] ?? 0;

$q_kamar_terisi = mysqli_query($conn, "SELECT COUNT(*) AS terisi FROM kamar WHERE status_kamar = 'Terisi'");
$kamar_terisi = mysqli_fetch_assoc($q_kamar_terisi)['terisi'] ?? 0;

if ($kamar_terisi == 0) {
    $q_fallback = mysqli_query($conn, "SELECT COUNT(DISTINCT kamar_id) AS terisi FROM booking WHERE status_booking IN ('Disetujui', 'Terisi')");
    $kamar_terisi = mysqli_fetch_assoc($q_fallback)['terisi'] ?? 0;
}

// 3. PENDAPATAN BULAN INI VS BULAN LALU
$q_bln_ini = mysqli_query($conn, "SELECT SUM(k.harga) AS total 
                                  FROM booking b 
                                  JOIN kamar k ON b.kamar_id = k.kamar_id 
                                  WHERE b.status_booking IN ('Disetujui', 'Terisi') 
                                  AND MONTH(b.dibuat_pada) = '$bulan_ini' 
                                  AND YEAR(b.dibuat_pada) = '$tahun_ini'");
$tot_ini = mysqli_fetch_assoc($q_bln_ini)['total'] ?? 0;

$q_bln_lalu = mysqli_query($conn, "SELECT SUM(k.harga) AS total 
                                   FROM booking b 
                                   JOIN kamar k ON b.kamar_id = k.kamar_id 
                                   WHERE b.status_booking IN ('Disetujui', 'Terisi') 
                                   AND MONTH(b.dibuat_pada) = '$bulan_lalu' 
                                   AND YEAR(b.dibuat_pada) = '$tahun_bulan_lalu'");
$tot_lalu = mysqli_fetch_assoc($q_bln_lalu)['total'] ?? 0;

if ($tot_lalu > 0) {
    $persen_perubahan = (($tot_ini - $tot_lalu) / $tot_lalu) * 100;
} else {
    $persen_perubahan = $tot_ini > 0 ? 100 : 0;
}

// 4. DATA PENDAPATAN BULANAN
$nama_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$pendapatan_bulanan = array_fill(1, 12, 0);

$q_bulanan = mysqli_query($conn, "SELECT MONTH(b.dibuat_pada) AS bulan, SUM(k.harga) AS total 
                                  FROM booking b 
                                  JOIN kamar k ON b.kamar_id = k.kamar_id 
                                  WHERE b.status_booking IN ('Disetujui', 'Terisi') 
                                  AND YEAR(b.dibuat_pada) = '$tahun_ini' 
                                  GROUP BY MONTH(b.dibuat_pada)");

while ($r = mysqli_fetch_assoc($q_bulanan)) {
    $pendapatan_bulanan[(int)$r['bulan']] = (float)$r['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner</title>

    <!-- FONT BAWAAN ADMINLTE -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- ADMINLTE & BOOTSTRAP CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>

/* =========================================
   TEMA SOFT BABY PINK PASTEL
   DASHBOARD OWNER
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
    background: var(--pink-bg);
    color: var(--text);
    font-family: 'Source Sans Pro', sans-serif;
}

.content-wrapper {
    padding: 0;
    min-height: 100vh !important;
    background: var(--pink-bg) !important;
}

.content-header,
.content {
    padding-left: 20px;
    padding-right: 20px;
}

/* JUDUL HALAMAN */
.judul-halaman {
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--pink-dark);
}

/* NAVBAR */
.main-header.navbar {
    background: #FFFFFF !important;
    border-bottom: 1px solid var(--pink-border) !important;
    box-shadow: 0 2px 8px rgba(217, 133, 163, 0.08);
}

.main-header .nav-link {
    color: var(--pink-dark) !important;
}

.main-header .nav-link:hover {
    color: #C67594 !important;
}

/* SIDEBAR */
.main-sidebar {
    background: #FFF7FA !important;
    box-shadow: 2px 0 12px rgba(217, 133, 163, 0.08);
}

/* LOGO OWNER */
.brand-link {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
    border-bottom: 1px solid var(--pink-border) !important;
}

.brand-link span {
    color: var(--pink-dark) !important;
    font-weight: 700 !important;
}

/* PROFIL OWNER */
.user-panel {
    border-bottom: 1px solid var(--pink-border) !important;
}

.user-panel .info a {
    color: var(--pink-dark) !important;
    font-weight: 600;
}

.user-panel .image img {
    border: 2px solid var(--pink-border);
    width: 35px;
    height: 35px;
    object-fit: cover;
}

/* MENU SIDEBAR */
.nav-header {
    color: var(--pink-dark) !important;
    font-weight: 700;
}

.nav-sidebar .nav-link {
    color: var(--text) !important;
    border-radius: 8px;
    margin-bottom: 4px;
    transition: 0.3s;
}

.nav-sidebar .nav-link .nav-icon {
    color: var(--pink-dark) !important;
}

.nav-sidebar .nav-link:hover {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
}

.nav-sidebar .nav-link.active {
    background: var(--pink) !important;
    color: #FFFFFF !important;
    box-shadow: 0 3px 8px rgba(217, 133, 163, 0.15);
}

.nav-sidebar .nav-link.active .nav-icon {
    color: #FFFFFF !important;
}

/* SEARCH SIDEBAR */
.form-control-sidebar {
    background: #FFFFFF !important;
    border: 1px solid var(--pink-border) !important;
    color: var(--text) !important;
}

.form-control-sidebar::placeholder {
    color: var(--muted) !important;
}

.btn-sidebar {
    background: var(--pink) !important;
    color: #FFFFFF !important;
    border-color: var(--pink) !important;
}

/* KARTU STATISTIK */
.info-box {
    background: #FFFFFF !important;
    border-radius: 12px !important;
    border: 1px solid var(--pink-border) !important;
    box-shadow: 0 4px 14px rgba(217, 133, 163, 0.09) !important;
    transition: 0.3s;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(217, 133, 163, 0.14) !important;
}

/* ICON STATISTIK */
.info-box-icon {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
    border-radius: 10px !important;
}

/* TEKS STATISTIK */
.info-box-text {
    color: var(--muted) !important;
    font-weight: 500;
}

.info-box-number {
    color: var(--pink-dark) !important;
    font-weight: 700;
}

/* CARD GRAFIK */
.card {
    background: #FFFFFF !important;
    border-radius: 14px !important;
    border: 1px solid var(--pink-border) !important;
    box-shadow: 0 4px 15px rgba(217, 133, 163, 0.08) !important;
    overflow: hidden;
}

/* HEADER GRAFIK */
.card-header {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
    border-bottom: 1px solid var(--pink-border) !important;
    padding: 14px 18px;
}

.card-title {
    color: var(--pink-dark) !important;
    font-weight: 600;
}

/* GARIS ATAS CARD */
.card-primary.card-outline,
.card-success.card-outline {
    border-top: 3px solid var(--pink) !important;
}

/* ISI GRAFIK */
.card-body {
    background: #FFFFFF !important;
    padding: 20px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .judul-halaman {
        font-size: 25px;
    }

    .content-header,
    .content {
        padding-left: 12px;
        padding-right: 12px;
    }

    .card-body {
        padding: 14px;
    }

    .info-box {
        margin-bottom: 15px;
    }
}

    </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- NAVBAR -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="../index2.php" class="nav-link">Home</a>
            </li>
        </ul>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light"><strong>Owner</strong></span>
        </a>

        <div class="sidebar">
            <!-- USER PANEL -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img src="<?= htmlspecialchars($foto_path_owner); ?>" class="img-circle elevation-2" alt="User Image" onerror="this.src='../uploads/default.jpeg';">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($nama_owner_aktif); ?></a>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar"><i class="fas fa-search fa-fw"></i></button>
                    </div>
                </div>
            </div>

            <!-- MENU SIDEBAR -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-header">Daftar Menu</li>
                    
                    <li class="nav-item">
                        <a href="kosowner.php" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="fotokos_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-images"></i>
                            <p>FOTO KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kosfasilitas_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>KOST FASILITAS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../kamar_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>KAMAR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="booking_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>BOOKING</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="review_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-star"></i>
                            <p>REVIEW</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="laporan_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>LAPORAN</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="judul-halaman">Dashboard Owner</h1>
            </div>
        </section>

        <section class="content mb-4">
            <div class="container-fluid">
                
                <!-- 1. KARTU RINGKASAN INFORMASI (INFO-BOXES) -->
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pendapatan</span>
                                <span class="info-box-number">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Booking Disetujui</span>
                                <span class="info-box-number"><?= $booking_disetujui; ?> Disetujui</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-bed"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kamar Terisi</span>
                                <span class="info-box-number"><?= $kamar_terisi; ?> / <?= $total_kamar; ?> Terisi</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon <?= $persen_perubahan >= 0 ? 'bg-primary' : 'bg-danger'; ?> elevation-1">
                                <i class="fas <?= $persen_perubahan >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Bulan Ini VS Lalu</span>
                                <span class="info-box-number"><?= ($persen_perubahan >= 0 ? '+' : '') . number_format($persen_perubahan, 1); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. GRAFIK CHART.JS -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Tren Pendapatan Bulanan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Realisasi Pendapatan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels = <?= json_encode(array_values($nama_bulan)); ?>;
    const dataPendapatan = <?= json_encode(array_values($pendapatan_bulanan)); ?>;

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataPendapatan,
                borderColor: '#F48FB1',
                backgroundColor: 'rgba(244, 143, 177, 0.18)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataPendapatan,
                backgroundColor: '#F8BBD0'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
</body>
</html>