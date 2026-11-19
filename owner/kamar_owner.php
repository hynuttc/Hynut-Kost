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

// =================================================
// AMBIL DATA KAMAR DARI DATABASE (JOIN KE KOS)
// =================================================
$query = "SELECT k.*, kos.nama_kos
          FROM kamar k
          LEFT JOIN kos ON k.kos_id = kos.kos_id
          ORDER BY k.kamar_id ASC";

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
    <title>Data Kamar - Owner</title>

    <!-- FONT ADMINLTE -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- ADMINLTE & BOOTSTRAP -->
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
    padding: 0;
    background: var(--pink-bg) !important;
    min-height: 100vh;
}

.content-header,
.content {
    padding-left: 20px;
    padding-right: 20px;
}

/* JUDUL HALAMAN */
.judul-halaman {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--pink-dark);
}

/* NAVBAR */
.main-header {
    background: #FFFFFF !important;
    border-bottom: 1px solid var(--pink-border) !important;
}

.main-header .nav-link {
    color: var(--pink-dark) !important;
}

.main-header .nav-link:hover {
    color: #B96F8A !important;
}

/* SIDEBAR OWNER */
.main-sidebar {
    background: #FFF0F5 !important;
}

.brand-link {
    background: #F8DCE6 !important;
    border-bottom: 1px solid var(--pink-border) !important;
}

.brand-link span {
    color: #B96F8A !important;
    font-weight: 600;
}

/* PROFIL OWNER */
.user-panel {
    border-bottom: 1px solid var(--pink-border) !important;
}

.user-panel .info a {
    color: #765E68 !important;
    font-weight: 600;
}

.user-panel img {
    border: 2px solid var(--pink);
    width: 35px;
    height: 35px;
    object-fit: cover;
}

/* MENU SIDEBAR */
.nav-sidebar .nav-header {
    color: var(--pink-dark) !important;
    font-weight: 600;
}

.nav-sidebar .nav-link {
    color: #765E68 !important;
    border-radius: 8px;
    margin-bottom: 4px;
}

.nav-sidebar .nav-icon {
    color: var(--pink-dark) !important;
}

.nav-sidebar .nav-link:hover {
    background: #FBE3EC !important;
    color: #B96F8A !important;
}

.nav-sidebar .nav-link.active {
    background: var(--pink) !important;
    color: #FFFFFF !important;
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
    border: none !important;
}

/* TOMBOL TAMBAH KAMAR OWNER */
.btn-tambah-kamar-owner {
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

.btn-tambah-kamar-owner:hover {
    background: #D985A3 !important;
    border-color: #D985A3 !important;
    color: #FFFFFF !important;
}
/* CARD TABEL */
.card {
    background: #FFFFFF;
    border: 1px solid var(--pink-border) !important;
    border-radius: 14px !important;
    box-shadow: 0 3px 12px rgba(217, 133, 163, 0.08) !important;
}

.card-body {
    background: #FFFFFF;
    padding: 20px;
}

/* TABEL */
.table {
    color: var(--text);
    margin-bottom: 0;
}

.table thead th {
    background: var(--pink-light) !important;
    color: #B96F8A !important;
    border-color: var(--pink-border) !important;
    font-weight: 600;
    vertical-align: middle;
    white-space: nowrap;
}

.table td,
.table th {
    border-color: var(--pink-border) !important;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background: #FFF5F8 !important;
}

/* BADGE STATUS KAMAR */
.badge-success {
    background: var(--pink) !important;
    color: #FFFFFF !important;
    padding: 6px 10px;
    border-radius: 6px;
}

.badge-danger {
    background: var(--pink-dark) !important;
    color: #FFFFFF !important;
    padding: 6px 10px;
    border-radius: 6px;
}

/* KOLOM AKSI */
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

/* TABEL RESPONSIF */
.table-responsive {
    overflow-x: auto;
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
        padding: 12px;
    }

    .kolom-aksi .btn {
        margin-bottom: 4px !important;
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
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>

            <li class="nav-item d-none d-sm-inline-block">
                <a href="index2.php" class="nav-link">Home</a>
            </li>
        </ul>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <a href="index2.php" class="brand-link">
            <span class="brand-text font-weight-light">
                <strong>Owner</strong>
            </span>
        </a>

        <div class="sidebar">

            <!-- PROFIL OWNER DINAMIS -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img src="<?= htmlspecialchars($foto_path_owner); ?>"
                         class="img-circle elevation-2"
                         alt="User Image"
                         onerror="this.src='../uploads/default.jpeg';">
                </div>

                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($nama_owner_aktif); ?></a>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="form-inline">
                <div class="input-group" data-widget="sidebar-search">

                    <input class="form-control form-control-sidebar"
                           type="search"
                           placeholder="Search"
                           aria-label="Search">

                    <div class="input-group-append">
                        <button class="btn btn-sidebar">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>

                </div>
            </div>

            <!-- MENU SIDEBAR -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false">

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
                        <a href="kamar_owner.php" class="nav-link active">
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

        <!-- JUDUL HALAMAN -->
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="judul-halaman">Data Kamar</h1>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">

                <!-- TOMBOL TAMBAH -->
                

                <!-- CARD TABEL -->
                <div class="card">

                    <div class="card-body">
<div class="mb-3 text-left">
    <a href="tambahkamar.php" class="btn btn-tambah-kamar-owner">
        <i class="fas fa-plus"></i> Tambah
    </a>
</div>
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover">

                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Nama Kost</th>
                                        <th>No Kamar</th>
                                        <th>Tipe Kamar</th>
                                        <th>Harga</th>
                                        <th>Status Kamar</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php
                                $no = 1;

                                if (mysqli_num_rows($result) > 0) {

                                    while ($data = mysqli_fetch_assoc($result)) {
                                ?>

                                    <tr>

                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= !empty($data['nama_kos'])
                                                ? htmlspecialchars($data['nama_kos'])
                                                : 'Kos ID: ' . (int)$data['kos_id']; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['no_kamar']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tipe_kamar']); ?>
                                        </td>

                                        <td>
                                            Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                                        </td>

                                        <td>

                                            <?php if ($data['status_kamar'] == 'Kosong'): ?>

                                                <span class="badge badge-success">
                                                    Kosong
                                                </span>

                                            <?php else: ?>

                                                <span class="badge badge-danger">
                                                    Terisi
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td class="kolom-aksi">

                                            <a href="editkamar.php?id=<?= (int)$data['kamar_id']; ?>"
                                               class="btn btn-success btn-sm">

                                                <i class="fas fa-edit"></i> Edit

                                            </a>

                                            <a href="hapuskamar.php?id=<?= (int)$data['kamar_id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus data kamar ini?');">

                                                <i class="fas fa-trash"></i> Hapus

                                            </a>

                                        </td>

                                    </tr>

                                <?php
                                    }

                                } else {
                                ?>

                                    <tr>
                                        <td colspan="7" class="text-center">
                                            Belum ada data kamar.
                                        </td>
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

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>