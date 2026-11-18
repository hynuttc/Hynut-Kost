
<?php
require_once '../database.php';

// =================================================
// AMBIL DATA KOS DARI DATABASE
// =================================================
$query = "SELECT * FROM kos ORDER BY kos_id ASC";
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
    <title>Data Kost - Admin</title>

    <!-- FONT ADMINLTE -->
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
    padding: 0;
    background: var(--pink-bg);
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
    color: var(--pink-dark);
    margin-bottom: 20px;
}

/* NAVBAR */
.main-header.navbar {
    background: #FFFFFF;
    border-bottom: 1px solid var(--pink-border);
}

.main-header .nav-link {
    color: var(--pink-dark) !important;
}

.main-header .nav-link:hover {
    color: #B96F8A !important;
}

/* SIDEBAR */
.main-sidebar {
    background: #FFF0F5;
}

.brand-link {
    background: #F8DCE6;
    border-bottom: 1px solid var(--pink-border) !important;
}

.brand-link .brand-text {
    color: #B96F8A !important;
    font-weight: 600 !important;
}

.sidebar {
    background: #FFF0F5;
}

/* USER PANEL */
.user-panel {
    border-bottom: 1px solid var(--pink-border) !important;
}

.user-panel .info a {
    color: #765E68 !important;
    font-weight: 600;
}

.user-panel img {
    border: 2px solid var(--pink);
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

/* MENU SIDEBAR */
.nav-sidebar .nav-header {
    color: var(--pink-dark);
    font-weight: 600;
}

.nav-sidebar .nav-link {
    color: #765E68 !important;
    border-radius: 8px;
    margin-bottom: 4px;
}

.nav-sidebar .nav-link .nav-icon {
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

/* CARD */
.card {
    background: #FFFFFF;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(217, 133, 163, 0.08);
}

.card-body {
    padding: 20px;
}

/* TOMBOL TAMBAH KOST */
.btn-tambah-kost {
    display: inline-block !important;
    background-color: #E8A1B8 !important;
    color: #FFFFFF !important;
    border: 1px solid #E8A1B8 !important;
    border-radius: 30px !important;
    padding: 8px 18px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    box-shadow: none !important;
}

.btn-tambah-kost:hover {
    background-color: #D985A3 !important;
    border-color: #D985A3 !important;
    color: #FFFFFF !important;
}

/* TOMBOL EDIT DAN HAPUS */
.kolom-aksi .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 30px !important;
    padding: 7px 16px !important;
    margin: 3px !important;
    border: 1px solid #E8A1B8 !important;
    color: #FFFFFF !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    box-shadow: none !important;
}

/* EDIT */
.kolom-aksi .btn-success {
    background-color: #E8A1B8 !important;
}

.kolom-aksi .btn-success:hover {
    background-color: #D985A3 !important;
}

/* HAPUS */
.kolom-aksi .btn-danger {
    background-color: #E8A1B8 !important;
}

.kolom-aksi .btn-danger:hover {
    background-color: #D985A3 !important;
}
/* TABEL */
.table {
    color: var(--text);
    margin-bottom: 0;
}

.table thead th {
    background: var(--pink-light);
    color: #B96F8A;
    border-color: var(--pink-border);
    font-weight: 600;
    vertical-align: middle;
    white-space: nowrap;
}

.table td,
.table th {
    vertical-align: middle;
    border-color: var(--pink-border);
}

.table-hover tbody tr:hover {
    background: #FFF5F8;
}

.table-bordered {
    border-color: var(--pink-border);
}

/* STATUS BADGE */
.badge-success {
    background: #E8A1B8 !important;
    color: #FFFFFF !important;
}

.badge-warning {
    background: #F6D6A8 !important;
    color: #765E68 !important;
}

.badge-danger {
    background: #D985A3 !important;
    color: #FFFFFF !important;
}

.badge-secondary {
    background: #C9B8C0 !important;
    color: #FFFFFF !important;
}

/* KOLOM AKSI */
.kolom-aksi {
    width: 200px !important;
    min-width: 200px !important;
    text-align: center !important;
    white-space: nowrap !important;
}

.kolom-aksi .btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    margin: 2px !important;
    border-radius: 6px;
}


/* RESPONSIVE TABEL */
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
                <a href="index.php" class="nav-link">Home</a>
            </li>
        </ul>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light">
                <strong>Admin</strong>
            </span>
        </a>

        <div class="sidebar">

            <!-- USER PANEL -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">

                <div class="image">
                    <img src="../uploads/admin.jpeg"
                         class="img-circle elevation-2"
                         alt="User Image">
                </div>

                <div class="info">
                    <a href="#" class="d-block">VINA</a>
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
                        <a href="user.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>USER</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="kos.php" class="nav-link active">
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
                        <a href="laporan.php" class="nav-link">
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
                <h1 class="judul-halaman">Data Kost</h1>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">

            <div class="container-fluid">

                <!-- TOMBOL TAMBAH KOST -->
                

                <!-- CARD TABLE -->
                <div class="card">

                    <div class="card-body">
<div class="mb-3 text-left">
    <a href="tambahkos.php" class="btn btn-tambah-kost">
        <i class="fas fa-plus"></i> Tambah Kost
    </a>
</div>
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover">

                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Nama Kost</th>
                                        <th>Alamat</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Tipe Kos</th>
                                        <th>Status Verifikasi</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php
                                $no = 1;

                                if (mysqli_num_rows($result) > 0) {

                                    while ($data = mysqli_fetch_assoc($result)) {

                                        $status = $data['status_verifikasi'];

                                        $badgeClass = 'secondary';

                                        if ($status == 'Aktif') {
                                            $badgeClass = 'success';
                                        } elseif ($status == 'Menunggu') {
                                            $badgeClass = 'warning';
                                        } elseif ($status == 'Nonaktif') {
                                            $badgeClass = 'danger';
                                        }
                                ?>

                                    <tr>

                                        <td>
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_kos']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['alamat']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['latitude']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['longitude']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tipe_kos']); ?>
                                        </td>

                                        <td>
                                            <span class="badge badge-<?= $badgeClass; ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>

                                        <td class="kolom-aksi">

                                            <a href="editkos.php?id=<?= (int)$data['kos_id']; ?>"
                                               class="btn btn-success btn-sm">

                                                <i class="fas fa-edit"></i> Edit

                                            </a>

                                            <a href="hapuskos.php?id=<?= (int)$data['kos_id']; ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus kost ini?');">

                                                <i class="fas fa-trash"></i> Hapus

                                            </a>

                                        </td>

                                    </tr>

                                <?php
                                    }

                                } else {
                                ?>

                                    <tr>
                                        <td colspan="8" class="text-center">
                                            Belum ada data kost.
                                        </td>
                                    </tr>

                                <?php
                                }
                                ?>

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
