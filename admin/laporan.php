<?php
session_start();
require_once '../database.php';

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
$q_total = mysqli_query($conn, "SELECT
    SUM(k.harga) AS total_pendapatan,
    COUNT(b.booking_id) AS total_booking
    FROM booking b
    JOIN kamar k ON b.kamar_id = k.kamar_id
    WHERE b.status_booking = 'Disetujui'");

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
                                  WHERE b.status_booking = 'Disetujui' 
                                  AND MONTH(b.dibuat_pada) = '$bulan_ini' 
                                  AND YEAR(b.dibuat_pada) = '$tahun_ini'");
$tot_ini = mysqli_fetch_assoc($q_bln_ini)['total'] ?? 0;

$q_bln_lalu = mysqli_query($conn, "SELECT SUM(k.harga) AS total 
                                   FROM booking b 
                                   JOIN kamar k ON b.kamar_id = k.kamar_id 
                                   WHERE b.status_booking = 'Disetujui'
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
                                  WHERE b.status_booking = 'Disetujui'
                                  AND YEAR(b.dibuat_pada) = '$tahun_ini' 
                                  GROUP BY MONTH(b.dibuat_pada)");

while ($r = mysqli_fetch_assoc($q_bulanan)) {
    $pendapatan_bulanan[(int)$r['bulan']] = (float)$r['total'];
}
$total_tahunan = array_sum($pendapatan_bulanan);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan</title>

    <!-- FONT BAWAAN ADMINLTE -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- ADMINLTE & BOOTSTRAP CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

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

/* BODY */
body {
    background: var(--pink-bg);
    color: var(--text);
}

/* CONTENT */
.content-wrapper {
    background: var(--pink-bg) !important;
    min-height: 100vh;
}

.content-header {
    padding-top: 25px;
}

.judul-halaman {
    font-size: 32px;
    font-weight: 600;
    color: var(--pink-dark);
    margin-bottom: 20px;
}

/* NAVBAR */
.main-header.navbar {
    background: #FFFFFF !important;
    border-bottom: 1px solid var(--pink-border);
}

.main-header .nav-link {
    color: var(--pink-dark) !important;
}

/* SIDEBAR */
.main-sidebar {
    background: #FFF0F5 !important;
}

.brand-link {
    background: #F8DCE6 !important;
    border-bottom: 1px solid var(--pink-border) !important;
}

.brand-link span {
    color: #B96F8A !important;
}

.user-panel {
    border-bottom: 1px solid var(--pink-border) !important;
}

.user-panel .info a {
    color: #B96F8A !important;
}

.nav-sidebar .nav-link {
    color: #765E68 !important;
    border-radius: 8px;
    margin-bottom: 4px;
    transition: 0.3s;
}

.nav-sidebar .nav-icon {
    color: var(--pink-dark) !important;
}

.nav-sidebar .nav-link:hover {
    background: #FBE3EC !important;
    color: #C87594 !important;
}

.nav-sidebar .nav-link.active {
    background: var(--pink) !important;
    color: #FFFFFF !important;
}

.nav-sidebar .nav-link.active .nav-icon {
    color: #FFFFFF !important;
}

.nav-header {
    color: #C58BA0 !important;
}

/* SEARCH SIDEBAR */
.form-control-sidebar {
    background: #FFFFFF !important;
    border: 1px solid var(--pink-border) !important;
    color: var(--text) !important;
}

.form-control-sidebar::placeholder {
    color: #B99AA6 !important;
}

.btn-sidebar {
    background: var(--pink) !important;
    color: #FFFFFF !important;
}

/* INFO BOX */
.info-box {
    background: #FFFFFF !important;
    border: 1px solid var(--pink-border) !important;
    border-radius: 14px !important;
    box-shadow: 0 5px 18px rgba(217, 133, 163, 0.08) !important;
}

.info-box-icon {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
    border-radius: 10px !important;
}

.info-box-icon.bg-success,
.info-box-icon.bg-info,
.info-box-icon.bg-warning,
.info-box-icon.bg-primary,
.info-box-icon.bg-danger {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
}

.info-box-text,
.info-box-number {
    color: var(--text) !important;
}

/* CARD */
.card {
    background: #FFFFFF !important;
    border: 1px solid var(--pink-border) !important;
    border-radius: 14px !important;
    box-shadow: 0 5px 18px rgba(217, 133, 163, 0.08) !important;
    overflow: hidden;
}

.card-header {
    background: var(--pink-light) !important;
    border-bottom: 1px solid var(--pink-border) !important;
    color: #B96F8A !important;
}

.card-title {
    color: #B96F8A !important;
    font-weight: 600;
}

.card-primary.card-outline,
.card-success.card-outline,
.card-secondary.card-outline {
    border-top: 3px solid var(--pink) !important;
}

.card-body {
    background: #FFFFFF !important;
}

/* BUTTON */
.btn-primary {
    background: var(--pink) !important;
    border-color: var(--pink) !important;
    color: #FFFFFF !important;
    border-radius: 22px;
}

.btn-primary:hover {
    background: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
}

/* TABLE */
.table {
    color: var(--text) !important;
}

.table thead th {
    background: var(--pink-light) !important;
    color: #B96F8A !important;
    border-color: var(--pink-border) !important;
}

.table td,
.table th {
    border-color: var(--pink-border) !important;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #FFF5F8 !important;
}

.table tfoot tr,
.table tfoot tr.bg-light {
    background: var(--pink-light) !important;
    color: #B96F8A !important;
}

/* TEXT */
.text-muted {
    color: var(--muted) !important;
}

/* RESPONSIVE */
.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .judul-halaman {
        font-size: 25px;
    }

    .content-header,
    .content {
        padding-left: 12px;
        padding-right: 12px;
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
                <a href="index.php" class="nav-link">Home</a>
            </li>
        </ul>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light"><strong>Admin</strong></span>
        </a>

        <div class="sidebar">
            <!-- USER PANEL -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="../uploads/admin.jpeg" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">VINA</a>
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
                        <a href="user.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>USER</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kos.php" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="foto_kos.php" class="nav-link">
                            <i class="nav-icon fas fa-images"></i>
                            <p>FOTO KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="fasilitas.php" class="nav-link">
                            <i class="nav-icon fas fa-list-ul"></i>
                            <p>FASILITAS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kos_fasilitas.php" class="nav-link">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>KOST FASILITAS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kamar.php" class="nav-link">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>KAMAR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="booking.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>BOOKING</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="review.php" class="nav-link">
                            <i class="nav-icon fas fa-star"></i>
                            <p>REVIEW</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="laporan.php" class="nav-link active">
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
                <h1 class="judul-halaman mb-0">Laporan Pendapatan</h1>
            </div>
        </section>

        <section class="content mt-3">
            <div class="container-fluid">
                
                <!-- 1. KARTU RINGKASAN INFORMASI -->
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

                <!-- TOMBOL CETAK LAPORAN -->
                <div class="row mb-3">
                    <div class="col-12 text-right">
                        <a href="cetak_laporan.php" target="_blank" class="btn btn-primary">
                            <i class="fas"></i><strong> Cetak Laporan</strong>
                        </a>
                    </div>
                </div>

                <!-- 3. TABEL DETAIL BULANAN (SESUAI DENGAN CETAK LAPORAN) -->
                <div class="card card-outline card-secondary mb-4">
                    <div class="card-body p-4">
                        
                        <!-- HEADER LAPORAN DI ATAS TABEL -->
                        <div class="text-center mb-4">
                            <h5 class="font-weight-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">
                                LAPORAN PENDAPATAN
                            </h5>
                            <div class="text-muted font-weight-bold" style="font-size: 14px;">
                                PENDAPATAN/<?= $tahun_ini; ?>
                            </div>
                        </div>

                        <!-- TABEL DETAIL PENDAPATAN -->
                        <div class="table-responsive">
                            <table class="table table-bordered m-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 10%; text-align: center;">No.</th>
                                        <th style="width: 45%; text-align: center;">Bulan</th>
                                        <th style="width: 45%; text-align: center;">Nominal Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($nama_bulan_indo as $num => $nama): ?>
                                        <tr>
                                            <td class="text-center"><?= $num; ?>.</td>
                                            <td class="text-left pl-4"><?= $nama; ?></td>
                                            <td class="text-right pr-4">
                                                Rp <?= number_format($pendapatan_bulanan[$num], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold bg-light">
                                        <td colspan="2" class="text-center">TOTAL PENDAPATAN:</td>
                                        <td class="text-right pr-4">
                                            Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
 const labels = <?= json_encode(array_values($nama_bulan)); ?>;
const dataPendapatan = <?= json_encode(array_values($pendapatan_bulanan)); ?>;

console.log("Label Grafik:", labels);
console.log("Data Pendapatan:", dataPendapatan);

// FORMAT RUPIAH
function formatRupiah(value) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
}

// PENGATURAN GRAFIK
const pengaturanGrafik = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        tooltip: {
            callbacks: {
                label: function(context) {
                    return 'Pendapatan: ' + formatRupiah(context.raw);
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return formatRupiah(value);
                }
            }
        }
    }
};

// GRAFIK GARIS
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pendapatan',
            data: dataPendapatan,
            borderColor: '#E8A1B8',
            backgroundColor: 'rgba(232, 161, 184, 0.2)',
            borderWidth: 3,
            pointBackgroundColor: '#D985A3',
            pointRadius: 5,
            fill: true,
            tension: 0.3
        }]
    },
    options: pengaturanGrafik
});

// GRAFIK BATANG
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pendapatan',
            data: dataPendapatan,
            backgroundColor: '#E8A1B8',
            borderColor: '#D985A3',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: pengaturanGrafik
});
</script>
</body>
</html>