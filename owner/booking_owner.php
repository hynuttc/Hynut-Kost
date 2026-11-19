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

// AMBIL DATA BOOKING DARI DATABASE
$query = "SELECT b.*, k.no_kamar, u.nama AS nama_user 
          FROM booking b 
          LEFT JOIN kamar k ON b.kamar_id = k.kamar_id 
          LEFT JOIN user u ON b.user_id = u.user_id 
          ORDER BY b.booking_id ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Booking - Owner</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>

/* =========================================
   TEMA SOFT BABY PINK PASTEL
   HALAMAN DATA BOOKING OWNER
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
    background: var(--pink-bg);
}

.content-header,
.content {
    padding-left: 20px;
    padding-right: 20px;
}

.judul-halaman {
    font-size: 30px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--pink-dark);
}

/* NAVBAR */
.main-header.navbar {
    background: #FFFFFF !important;
    border-bottom: 1px solid var(--pink-border);
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
    background: #FFF0F5 !important;
    box-shadow: 2px 0 12px rgba(217, 133, 163, 0.08);
}

.brand-link {
    background: var(--pink-light) !important;
    border-bottom: 1px solid var(--pink-border) !important;
    color: var(--pink-dark) !important;
}

.brand-text {
    color: var(--pink-dark) !important;
    font-weight: 700 !important;
}

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

/* JUDUL DAN KONTEN */
.content-header {
    padding-top: 25px;
}

/* CARD TABEL */
.card {
    background: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    box-shadow: 0 5px 18px rgba(217, 133, 163, 0.08);
    overflow: hidden;
}

.card-body {
    padding: 22px;
}

/* TABEL */
.table {
    color: var(--text);
    margin-bottom: 0;
    background: #FFFFFF;
}

.table thead th {
    background: var(--pink-light) !important;
    color: var(--pink-dark) !important;
    border-color: var(--pink-border) !important;
    font-weight: 700;
    padding: 13px;
    vertical-align: middle;
}

.table td,
.table th {
    vertical-align: middle;
    border-color: var(--pink-border) !important;
    padding: 12px;
}

.table tbody tr {
    background: #FFFFFF;
    transition: 0.2s;
}

.table tbody tr:nth-child(even) {
    background: var(--pink-bg);
}

.table tbody tr:hover {
    background: var(--pink-light) !important;
}

/* BADGE STATUS */
.badge {
    padding: 7px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #D8EFE0 !important;
    color: #397B53 !important;
}

.badge-warning {
    background: #FFF0CC !important;
    color: #987329 !important;
}

.badge-danger {
    background: #F8DCE5 !important;
    color: #B65375 !important;
}

/* =========================================
   TOMBOL TAMBAH BOOKING
========================================= */

.btn-tambah-booking {
    display: inline-block !important;
    background: #E8A1B8 !important;
    color: #FFFFFF !important;
    border: 1px solid #E8A1B8 !important;
    border-radius: 30px !important;
    padding: 8px 18px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    box-shadow: none !important;
    transition: 0.3s;
}

.btn-tambah-booking:hover {
    background: #D985A3 !important;
    border-color: #D985A3 !important;
    color: #FFFFFF !important;
}

/* =========================================
   KOLOM AKSI
========================================= */

.kolom-aksi {
    width: 200px !important;
    min-width: 200px !important;
    text-align: center !important;
    white-space: nowrap !important;
}

/* TOMBOL EDIT DAN HAPUS */

.kolom-aksi .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 3px !important;
    border-radius: 30px !important;
    padding: 7px 16px !important;
    border: 1px solid #E8A1B8 !important;
    color: #FFFFFF !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    box-shadow: none !important;
    transition: 0.3s;
}

/* TOMBOL EDIT */

.kolom-aksi .btn-success {
    background: #E8A1B8 !important;
}

.kolom-aksi .btn-success:hover {
    background: #D985A3 !important;
}

/* TOMBOL HAPUS */

.kolom-aksi .btn-danger {
    background: #E8A1B8 !important;
}

.kolom-aksi .btn-danger:hover {
    background: #D985A3 !important;
}

/* PESAN DATA KOSONG */
.table tbody td.text-center {
    color: var(--muted);
}

/* RESPONSIVE */
.table-responsive {
    overflow-x: auto;
}

/* SCROLLBAR SIDEBAR */
.sidebar {
    scrollbar-color: var(--pink) var(--pink-light);
}

/* RESPONSIVE MOBILE */
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
        padding: 12px;
    }

    .table td,
    .table th {
        padding: 9px;
        font-size: 13px;
    }
}

    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="index2.php" class="nav-link">Home</a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index2.php" class="brand-link">
            <span class="brand-text font-weight-light"><strong>Owner</strong></span>
        </a>

        <div class="sidebar">
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
                        <a href="kamar_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>KAMAR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="booking_owner.php" class="nav-link active">
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


    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="judul-halaman">Data Booking</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                

                <div class="card">
                    <div class="card-body">
 <!-- TOMBOL TAMBAH BOOKING -->
<div class="mb-3 text-left">
    <a href="tambahbooking.php" class="btn btn-tambah-booking">
        <i class="fas fa-plus"></i> Tambah Booking
    </a>
</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>No Kamar</th>
                                        <th>Nama User</th>
                                        <th>Tanggal Survey</th>
                                        <th>Status Booking</th>
                                        <th>Dibuat Pada</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result) > 0) {
                                    while ($data = mysqli_fetch_assoc($result)) {
                                        $st = $data['status_booking'] ?? 'Menunggu';
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= !empty($data['no_kamar']) ? 'Kamar ' . htmlspecialchars($data['no_kamar']) : 'Kamar ID: ' . $data['kamar_id']; ?></td>
                                        <td><?= !empty($data['nama_user']) ? htmlspecialchars($data['nama_user']) : 'User ID: ' . $data['user_id']; ?></td>
                                        <td><?= date('d-m-Y', strtotime($data['tanggal_survey'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?= ($st == 'Disetujui' || $st == 'Diterima') ? 'success' : ($st == 'Ditolak' ? 'danger' : 'warning'); ?>">
                                                <?= htmlspecialchars($st); ?>
                                            </span>
                                        </td>
                                        <td><?= date('d-m-Y H:i', strtotime($data['dibuat_pada'])); ?></td>
                                        <td class="kolom-aksi">

    <!-- TOMBOL EDIT -->
    <a href="editbooking_owner.php?id=<?= (int)$data['booking_id']; ?>"
       class="btn btn-success btn-sm">
        <i class="fas fa-edit"></i> Edit
    </a>

    <!-- TOMBOL HAPUS -->
    <a href="hapusbooking_owner.php?id=<?= (int)$data['booking_id']; ?>"
       class="btn btn-danger btn-sm"
       onclick="return confirm('Yakin ingin menghapus data booking ini?');">
        <i class="fas fa-trash"></i> Hapus
    </a>

</td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data booking.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>