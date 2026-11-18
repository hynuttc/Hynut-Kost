<?php
session_name('HYNUTKOST_SESSION');
session_start();
require_once __DIR__ . '/database.php';

// ========================================
// MENYESUAIKAN KONEKSI DATABASE
// ========================================
if (!isset($conn) && isset($db)) {
    $conn = $db;
} elseif (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// ========================================
// CEK LOGIN PENGGUNA
// ========================================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ========================================
// PROSES HAPUS PESANAN
// ========================================
if (
    isset($_GET['action'], $_GET['id']) &&
    $_GET['action'] === 'hapus'
) {
    $booking_id = (int) $_GET['id'];

    if ($booking_id > 0) {

        // Pastikan booking milik pengguna yang sedang login
        $stmt_cek = mysqli_prepare(
            $conn,
            "SELECT booking_id FROM booking
             WHERE booking_id = ? AND user_id = ?"
        );

        mysqli_stmt_bind_param($stmt_cek, "ii", $booking_id, $user_id);
        mysqli_stmt_execute($stmt_cek);

        $hasil_cek = mysqli_stmt_get_result($stmt_cek);
        $booking_ada = mysqli_num_rows($hasil_cek) > 0;

        mysqli_stmt_close($stmt_cek);

        if ($booking_ada) {

            mysqli_begin_transaction($conn);

            try {
                // Hapus ulasan
                $stmt_review = mysqli_prepare(
                    $conn,
                    "DELETE FROM review WHERE booking_id = ?"
                );

                mysqli_stmt_bind_param($stmt_review, "i", $booking_id);
                mysqli_stmt_execute($stmt_review);
                mysqli_stmt_close($stmt_review);

                // Hapus booking
                $stmt_delete = mysqli_prepare(
                    $conn,
                    "DELETE FROM booking WHERE booking_id = ? AND user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmt_delete,
                    "ii",
                    $booking_id,
                    $user_id
                );

                mysqli_stmt_execute($stmt_delete);
                mysqli_stmt_close($stmt_delete);

                mysqli_commit($conn);

                echo "<script>
                    alert('Pesanan berhasil dihapus!');
                    window.location.href='pesanan.php';
                </script>";

            } catch (Throwable $e) {
                mysqli_rollback($conn);

                echo "<script>
                    alert('Gagal menghapus pesanan.');
                    window.location.href='pesanan.php';
                </script>";
            }

        } else {
            echo "<script>
                alert('Pesanan tidak ditemukan!');
                window.location.href='pesanan.php';
            </script>";
        }

        exit;
    }
}
// ========================================
// AMBIL DATA USER
// ========================================
$stmt_user = mysqli_prepare(
    $conn,
    "SELECT * FROM user WHERE user_id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);

$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

mysqli_stmt_close($stmt_user);

// ========================================
// MENCARI FOTO PROFIL
// ========================================
$foto_path = 'uploads/default.jpeg';
$ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
$has_foto = false;

foreach ($ekstensi_diizinkan as $ext) {
    $file_cek = 'uploads/user_' . $user_id . '.' . $ext;

    if (file_exists($file_cek)) {
        $foto_path = $file_cek;
        $has_foto = true;
        break;
    }
}

// ========================================
// AMBIL DATA PESANAN PENGGUNA
// ========================================
$stmt_pesanan = mysqli_prepare(
    $conn,
    "SELECT
        b.booking_id,
        b.tanggal_survey,
        COALESCE(b.status_booking, 'Menunggu') AS status,
        k.nama_kos,
        k.alamat,
        km.harga,
        r.review_id,
        r.rating,
        r.komentar
    FROM booking b
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN kos k ON km.kos_id = k.kos_id
    LEFT JOIN review r ON b.booking_id = r.booking_id
    WHERE b.user_id = ?
    ORDER BY b.booking_id DESC"
);

mysqli_stmt_bind_param($stmt_pesanan, "i", $user_id);
mysqli_stmt_execute($stmt_pesanan);

$q_pesanan = mysqli_stmt_get_result($stmt_pesanan);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan Saya - Hynut Kost</title>
    <link rel="icon" href="uploads/logohynut.png" type="image/png">
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

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: var(--pink-bg);
    color: var(--text);
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
    max-width: 1200px;
    margin: auto;
    padding: 16px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo {
    color: var(--pink-dark);
    font-size: 24px;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.logo span {
    color: var(--text);
}

.nav-menu {
    display: flex;
    gap: 25px;
    align-items: center;
}

.nav-menu a {
    color: var(--muted);
    font-size: 14px;
    transition: 0.3s;
}

.nav-menu > a:not(.btn-profile):hover {
    color: var(--pink-dark);
}

/* =========================================
   TOMBOL PROFIL NAVBAR
========================================= */

.btn-profile {
    background: var(--pink-light);
    color: var(--pink-dark) !important;
    border: 1px solid var(--pink-border);
    padding: 5px 14px 5px 8px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}

.btn-profile:hover {
    background: #F5D2DF;
}

.btn-profile img {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--pink);
}

/* =========================================
   LAYOUT UTAMA
========================================= */

.layout {
    max-width: 1100px;
    margin: 30px auto;
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 25px;
    padding: 0 20px;
    align-items: start;
}

/* =========================================
   SIDEBAR
========================================= */

.sidebar {
    background: var(--white);
    padding: 25px 20px;
    border-radius: 15px;
    border: 1px solid var(--pink-border);
    height: fit-content;
    box-shadow: 0 5px 18px rgba(217, 133, 163, 0.06);
}

.sidebar-user {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--pink-border);
    margin-bottom: 20px;
}

.avatar-img {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 10px;
    display: block;
    border: 3px solid var(--pink);
    padding: 2px;
}

.sidebar-user strong {
    display: block;
    font-size: 15px;
    color: var(--text);
}

.sidebar-user span {
    font-size: 12px;
    color: var(--muted);
}

.menu-list {
    list-style: none;
}

.menu-list a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: var(--text);
    font-size: 14px;
    border-radius: 10px;
    margin-bottom: 6px;
    font-weight: 500;
    transition: 0.2s;
}

.menu-list a.active,
.menu-list a:hover {
    background: var(--pink-light);
    color: var(--pink-dark);
    font-weight: bold;
}

/* =========================================
   KARTU UTAMA
========================================= */

.main-card {
    background: var(--white);
    padding: 35px;
    border-radius: 15px;
    border: 1px solid var(--pink-border);
    box-shadow: 0 5px 18px rgba(217, 133, 163, 0.06);
    min-width: 0;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--pink-border);
}

.card-header h2 {
    font-size: 20px;
    color: var(--pink-dark);
}

/* =========================================
   ITEM PESANAN
========================================= */

.booking-item {
    background: var(--pink-bg);
    border: 1px solid var(--pink-border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    transition: 0.2s;
}

.booking-item:hover {
    border-color: var(--pink);
    box-shadow: 0 4px 12px rgba(217, 133, 163, 0.08);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 10px;
}

.booking-title {
    font-size: 16px;
    font-weight: bold;
    color: var(--text);
}

.booking-sub {
    font-size: 13px;
    color: var(--muted);
    margin-top: 5px;
}

.booking-price {
    font-size: 14px;
    font-weight: bold;
    color: var(--pink-dark);
    margin-top: 5px;
}

/* =========================================
   BADGE STATUS
========================================= */

.badge {
    display: inline-block;
    padding: 5px 11px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    margin-top: 8px;
}

.badge-warning {
    background: #FFF0CC;
    color: #987329;
}

.badge-success {
    background: #D8EFE0;
    color: #397B53;
}

.badge-danger {
    background: #F8DCE5;
    color: #B65375;
}

/* =========================================
   TOMBOL AKSI
========================================= */

.action-group {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.btn-cetak {
    background: var(--pink);
    color: var(--white) !important;
    padding: 7px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    transition: 0.2s;
    border: none;
    white-space: nowrap;
}

.btn-cetak:hover {
    background: var(--pink-dark);
}

.btn-cetak-disabled {
    background: #E8DDE2;
    color: #927F87 !important;
    padding: 7px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    opacity: 0.75;
    cursor: not-allowed;
    border: none;
    white-space: nowrap;
}

.btn-hapus {
    background: #D985A3;
    color: var(--white) !important;
    padding: 7px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    transition: 0.2s;
    white-space: nowrap;
}

.btn-hapus:hover {
    background: #C67594;
}

/* =========================================
   FORM ULASAN
========================================= */

.form-review-inline {
    background: var(--pink-light);
    border: 1px solid var(--pink-border);
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
}

.form-review-inline label {
    font-size: 13px;
    font-weight: bold;
    color: var(--pink-dark);
    display: block;
    margin-bottom: 8px;
}

.form-review-inline select,
.form-review-inline textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--pink-border);
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 10px;
    outline: none;
    background: var(--white);
    color: var(--text);
    transition: 0.2s;
}

.form-review-inline select:focus,
.form-review-inline textarea:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

.form-review-inline textarea {
    resize: vertical;
}

.btn-submit-rev {
    background: var(--pink);
    color: var(--white);
    border: none;
    padding: 9px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

.btn-submit-rev:hover {
    background: var(--pink-dark);
}

/* =========================================
   ULASAN SELESAI
========================================= */

.review-done {
    background: #F0F8F2;
    border: 1px solid #D8EFE0;
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
    color: #397B53;
    margin-top: 15px;
}

.review-done em {
    color: var(--text);
    overflow-wrap: anywhere;
}

/* =========================================
   RESPONSIVE TABLET
========================================= */

@media (max-width: 768px) {
    .navbar-container {
        padding: 14px 18px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .nav-menu {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .layout {
        grid-template-columns: 1fr;
        margin: 20px auto;
        padding: 0 15px;
    }

    .sidebar {
        padding: 20px;
    }

    .menu-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 5px;
    }

    .menu-list a {
        margin-bottom: 0;
        padding: 11px 10px;
        font-size: 13px;
    }

    .main-card {
        padding: 22px;
    }

    .booking-header {
        flex-direction: column;
    }

    .action-group {
        justify-content: flex-start;
    }
}

/* =========================================
   RESPONSIVE HP KECIL
========================================= */

@media (max-width: 480px) {
    .navbar-container {
        padding: 12px;
    }

    .logo {
        font-size: 20px;
    }

    .nav-menu {
        gap: 10px;
    }

    .nav-menu a {
        font-size: 12px;
    }

    .layout {
        padding: 0 12px;
    }

    .main-card {
        padding: 17px;
    }

    .card-header h2 {
        font-size: 17px;
    }

    .booking-item {
        padding: 15px;
    }

    .booking-title {
        font-size: 15px;
    }

    .action-group {
        width: 100%;
    }

    .btn-cetak,
    .btn-cetak-disabled,
    .btn-hapus {
        font-size: 11px;
        padding: 7px 9px;
    }

    .form-review-inline {
        padding: 12px;
    }
}

    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">
            <img src="uploads/logo finder.png" alt="Hynut Kost Logo" style="height: 35px; width: auto; vertical-align: middle; margin-right: 8px;">
            KOST <span>Finder</span>
        </a>
        <div class="nav-menu">
            <a href="index.php">Beranda</a>
            <a href="daftarkos.php">Daftar Kos</a>
            <a href="tentang.php">Tentang</a>
            <a href="profil.php" class="btn-profile">
                <?php if ($has_foto): ?>
                    <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil">
                <?php else: ?>
                    👤
                <?php endif; ?>
                <span><?= htmlspecialchars($user['nama'] ?? $_SESSION['nama']); ?></span>
            </a>
        </div>
    </div>
</nav>

<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <!-- Tampilkan foto profil dari folder uploads -->
            <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil" class="avatar-img">
            <strong><?= htmlspecialchars($user['nama']); ?></strong>
            <span><?= htmlspecialchars($user['role'] ?? 'pengguna'); ?></span>
        </div>
        <ul class="menu-list">
            <li><a href="profil.php">🏠 Dashboard</a></li>
            <li><a href="favorit.php">🤍 Favorit</a></li>
            <li><a href="booking.php">📌 Booking</a></li>
            <li><a href="pesanan.php" class="active">📋 Pesanan</a></li>
            <li><a href="tentang.php">⚙️ Tentang</a></li>
        </ul>
    </aside>

    <main class="main-card">
        <div class="card-header">
            <h2>Riwayat Pesanan & Ulasan Kos</h2>
        </div>

        <?php if (mysqli_num_rows($q_pesanan) > 0): ?>
            <?php while ($b = mysqli_fetch_assoc($q_pesanan)): 
                $status_raw = strtolower($b['status']);
                $is_approved = ($status_raw === 'disetujui' || $status_raw === 'approved' || $status_raw === 'selesai');
            ?>
                <div class="booking-item">
                    <div class="booking-header">
                        <div>
                            <div class="booking-title"><?= htmlspecialchars($b['nama_kos']); ?></div>
                            <div class="booking-sub">📍 <?= htmlspecialchars($b['alamat']); ?></div>
                            <div class="booking-price">Rp <?= number_format($b['harga'] ?? 0, 0, ',', '.'); ?>/bulan</div>
                            
                            <!-- Display Status Badge -->
                            <?php if ($is_approved): ?>
                                <span class="badge badge-success">✓ Disetujui</span>
                            <?php elseif ($status_raw === 'ditolak'): ?>
                                <span class="badge badge-danger">✕ Ditolak</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⏳ Menunggu Konfirmasi</span>
                            <?php endif; ?>
                        </div>

                        <div class="action-group">
                            <!-- Tombol Cetak Bukti -->
                            <?php if ($is_approved): ?>
                                <a href="cetak_booking.php?id=<?= $b['booking_id']; ?>" 
                                   target="_blank" 
                                   class="btn-cetak">
                                   🖨️ Cetak Bukti
                                </a>
                            <?php else: ?>
                                <button class="btn-cetak-disabled" disabled title="Cetak bukti hanya dapat dilakukan jika booking telah disetujui">
                                   🖨️ Cetak Bukti
                                </button>
                            <?php endif; ?>

                            <!-- Tombol Hapus Pesanan -->
                            <a href="pesanan.php?action=hapus&id=<?= $b['booking_id']; ?>" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?');" 
                               class="btn-hapus">
                               🗑️ Hapus
                            </a>
                        </div>
                    </div>

                    <!-- Form / Hasil Ulasan (Hanya tampil jika status Disetujui / Approved) -->
                    <?php if ($is_approved): ?>
                        <?php if (empty($b['review_id'])): ?>
                            <form action="proses_review.php" method="POST" class="form-review-inline">
                                <input type="hidden" name="booking_id" value="<?= $b['booking_id']; ?>">
                                
                                <label>Beri Ulasan & Rating Kos Ini:</label>
                                <select name="rating" required>
                                    <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                                    <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                                    <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                    <option value="2">⭐⭐ (2 - Buruk)</option>
                                    <option value="1">⭐ (1 - Sangat Buruk)</option>
                                </select>

                                <textarea name="komentar" rows="2" placeholder="Bagaimana pengalaman kamu ngekos di sini?" required></textarea>
                                
                                <button type="submit" name="submit_review" class="btn-submit-rev">Kirim Ulasan</button>
                            </form>
                        <?php else: ?>
                            <div class="review-done">
                                <strong>✔ Ulasan Terkirim:</strong>
                                <div style="color:#f59e0b; margin:2px 0;"><?= str_repeat('★', (int)$b['rating']); ?> (<?= $b['rating']; ?>/5)</div>
                                <em>"<?= htmlspecialchars($b['komentar']); ?>"</em>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="font-size: 13px; color: #888;">Belum ada pesanan kos.</p>
        <?php endif; ?>
    </main>
</div>

</body>
</html>