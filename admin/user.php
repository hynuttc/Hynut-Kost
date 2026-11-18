<?php
require_once '../database.php';

// =================================================
// AMBIL DATA USER DARI DATABASE
// =================================================
$query = "SELECT * FROM user ORDER BY user_id ASC";
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
    <title>Data User</title>

    <!-- FONT BAWAAN ADMINLTE (Menjadikan huruf rapi seperti Gambar 1) -->
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
        font-size: 32px;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--pink-dark);
    }

    /* NAVBAR */
    .main-header.navbar {
        background: #FFFFFF !important;
        border-bottom: 1px solid var(--pink-border);
        box-shadow: 0 2px 8px rgba(217, 133, 163, 0.06);
    }

    .main-header .nav-link {
        color: var(--pink-dark) !important;
    }

    .main-header .nav-link:hover {
        color: #C87594 !important;
    }

    /* SIDEBAR */
    .main-sidebar {
        background: #FFF0F5 !important;
        box-shadow: 2px 0 12px rgba(217, 133, 163, 0.08);
    }

    .brand-link {
        background: #F8DCE6 !important;
        border-bottom: 1px solid var(--pink-border) !important;
        color: #B96F8A !important;
    }

    .brand-text {
        color: #B96F8A !important;
        font-weight: 700 !important;
    }

    .user-panel {
        border-bottom: 1px solid var(--pink-border) !important;
    }

    .user-panel .info a {
        color: #B96F8A !important;
        font-weight: 600;
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
        background: #E8A1B8 !important;
        color: white !important;
    }

    /* MENU SIDEBAR */
    .nav-header {
        color: #C58BA0 !important;
        font-weight: 700;
    }

    .nav-sidebar .nav-link {
        color: #765E68 !important;
        border-radius: 8px;
        margin-bottom: 4px;
        transition: 0.3s;
    }

    .nav-sidebar .nav-link .nav-icon {
        color: #D985A3 !important;
    }

    .nav-sidebar .nav-link:hover {
        background: #FBE3EC !important;
        color: #C87594 !important;
    }

    .nav-sidebar .nav-link.active {
        background: #E8A1B8 !important;
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
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
        overflow: hidden;
    }

    .card-body {
        padding: 22px;
    }

    /* TABEL */
    .table {
        color: var(--text);
        margin-bottom: 0;
    }

    .table thead th {
        background: #FCEAF0 !important;
        color: #B96F8A;
        border-color: var(--pink-border) !important;
        font-weight: 700;
        padding: 13px;
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

    .table tbody tr:hover {
        background: #FFF5F8 !important;
    }

    /* TOMBOL TAMBAH USER */
.btn-tambah-user {
    display: inline-block !important;
    background: #E8A1B8 !important;
    color: #FFFFFF !important;
    border: 1px solid #E8A1B8 !important;
    border-radius: 30px !important;
    padding: 5px 12px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    box-shadow: none !important;
    transition: 0.3s;
}

.btn-tambah-user:hover {
    background: #D985A3 !important;
    border-color: #D985A3 !important;
    color: #FFFFFF !important;
}

/* TOMBOL EDIT */
.btn-success {
    background: var(--pink) !important;
    border-color: var(--pink) !important;
    color: #FFFFFF !important;
    border-radius: 20px;
    padding: 5px 13px;
    transition: 0.3s;
}

.btn-success:hover {
    background: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
    transform: translateY(-1px);
}

/* TOMBOL HAPUS */
.btn-danger {
    background: var(--pink-dark) !important;
    border-color: var(--pink-dark) !important;
    color: #FFFFFF !important;
    border-radius: 20px;
    padding: 5px 13px;
    transition: 0.3s;
}

.btn-danger:hover {
    background: #C67594 !important;
    border-color: #C67594 !important;
    transform: translateY(-1px);
}
    /* BADGE */
    .badge-info {
        background: #E8A1B8 !important;
        color: #FFFFFF !important;
    }

    .badge-success {
        background: #A8CDB5 !important;
        color: #FFFFFF !important;
    }

    .badge-warning {
        background: #F3D59B !important;
        color: #765E45 !important;
    }

    
    .btn-success {
        background: #E8A1B8 !important;
        border-color: #E8A1B8 !important;
        color: #FFFFFF !important;
        border-radius: 20px;
        transition: 0.3s;
    }

    /* TOMBOL */
    /* =========================================
   TOMBOL TAMBAH, EDIT, DAN HAPUS
========================================= */


 /* KOLOM AKSI */
.kolom-aksi {
    width: 150px !important;
    min-width: 150px !important;
    text-align: center !important;
    white-space: nowrap !important;
}

.kolom-aksi .btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    margin: 2px !important;
}

    /* RESPONSIVE */
    .table-responsive {
        overflow-x: auto;
    }

    .sidebar {
        scrollbar-color: #E8A1B8 #FFF0F5;
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
                        <a href="user.php" class="nav-link active">
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
                            <i class="nav-icon fas fas fa-star"></i>
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
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="judul-halaman">Data User</h1>
            </div>
        </section>

        <section class="content">
    <div class="container-fluid">

                <!-- CARD TABLE -->
                <div class="card">
                    <div class="card-body">
                                        <div class="d-flex justify-content-start mb-3">
    <a href="tambahuser.php" class="btn btn-tambah-user">
        <i class="fas fa-plus"></i> Tambah User
    </a>
</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Role</th>
                                        <th>No WA</th>
                                        <th>Status Verifikasi</th>
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
                                        <td><?= htmlspecialchars($data['nama']); ?></td>
                                        <td><?= htmlspecialchars($data['email']); ?></td>
                                        <td><?= htmlspecialchars($data['password']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?= htmlspecialchars($data['role']); ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($data['no_wa']); ?></td>
                                        <td>
                                            <?php if ($data['status_verifikasi'] == 'Terverifikasi'): ?>
                                                <span class="badge badge-success">Terverifikasi</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Menunggu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="kolom-aksi">
                                            <a href="edituser.php?id=<?= (int)$data['user_id']; ?>" class="btn btn-success btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="hapususer.php?id=<?= (int)$data['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data user ini?');">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada data user.</td>
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