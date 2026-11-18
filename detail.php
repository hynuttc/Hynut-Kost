<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Disamakan menggunakan session_start() standar agar sinkron dengan index.php
require_once 'database.php';

// ========================================
// MENYESUAIKAN KONEKSI DATABASE SECARA FLEKSIBEL
// ========================================
if (!isset($db) || !($db instanceof mysqli)) {
    if (isset($conn) && ($conn instanceof mysqli)) {
        $db = $conn;
    } elseif (isset($koneksi) && ($koneksi instanceof mysqli)) {
        $db = $koneksi;
    }
}

// Periksa koneksi database
if (!isset($db) || !($db instanceof mysqli)) {
    die("Koneksi database gagal. Periksa kembali file database.php.");
}

// Tangkap ID kos
$kos_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Jika ID kos tidak valid, kembali ke Beranda
if ($kos_id <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| QUERY DETAIL KOS
|--------------------------------------------------------------------------
*/
$query_kos = "
    SELECT k.*, u.nama AS nama_owner, u.no_wa
    FROM kos k
    LEFT JOIN user u ON u.user_id = k.owner_id
    WHERE k.kos_id = '$kos_id'
";
$res_kos = mysqli_query($db, $query_kos);
$kos = $res_kos ? mysqli_fetch_assoc($res_kos) : null;

if (!$kos) {
    die("<h2 style='text-align:center; margin-top:50px; font-family:Poppins, sans-serif; color:#17213c;'>Data Kos tidak ditemukan.</h2>");
}

/*
|--------------------------------------------------------------------------
| QUERY FOTO, HARGA, FASILITAS & RATING
|--------------------------------------------------------------------------
*/
$res_foto = mysqli_query($db, "SELECT * FROM foto_kos WHERE kos_id = '$kos_id' ORDER BY foto_id ASC");
$fotos = [];
if ($res_foto) {
    while ($f = mysqli_fetch_assoc($res_foto)) {
        $fotos[] = $f['path_foto'];
    }
}

$res_harga = mysqli_query($db, "SELECT MIN(harga) AS harga_mulai FROM kamar WHERE kos_id = '$kos_id'");
$harga_data = $res_harga ? mysqli_fetch_assoc($res_harga) : null;
$harga_mulai = $harga_data['harga_mulai'] ?? 0;

$res_fas = mysqli_query($db, "
    SELECT f.nama_fasilitas
    FROM kos_fasilitas kf
    JOIN fasilitas f ON kf.fasilitas_id = f.fasilitas_id
    WHERE kf.kos_id = '$kos_id'
");

// Stat Rating & Total Ulasan
$res_review = mysqli_query($db, "
    SELECT AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_review
    FROM review r
    JOIN booking b ON r.booking_id = b.booking_id
    JOIN kamar km ON b.kamar_id = km.kamar_id
    WHERE km.kos_id = '$kos_id'
");
$review_data = $res_review ? mysqli_fetch_assoc($res_review) : null;
$rating = ($review_data && $review_data['avg_rating']) ? round($review_data['avg_rating'], 1) : '0';
$total_review = $review_data['total_review'] ?? 0;

/*
|--------------------------------------------------------------------------
| QUERY AMBIL DAFTAR ULASAN DARI USER
|--------------------------------------------------------------------------
*/
$res_list_review = mysqli_query($db, "
    SELECT r.*, u.nama AS nama_user
    FROM review r
    JOIN booking b ON r.booking_id = b.booking_id
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN user u ON b.user_id = u.user_id
    WHERE km.kos_id = '$kos_id'
    ORDER BY r.dibuat_pada DESC
");

/*
|--------------------------------------------------------------------------
| CEK ID BOOKING USER (JIKA ADA)
|--------------------------------------------------------------------------
*/
$user_booking_id = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $q_booking = mysqli_query($db, "
        SELECT b.booking_id 
        FROM booking b
        JOIN kamar km ON b.kamar_id = km.kamar_id
        WHERE km.kos_id = '$kos_id' AND b.user_id = '$user_id'
        ORDER BY b.booking_id DESC LIMIT 1
    ");
    if ($q_booking && $row_b = mysqli_fetch_assoc($q_booking)) {
        $user_booking_id = $row_b['booking_id'];
    }
}

$no_wa = $kos['no_wa'] ?? '';
if (!empty($no_wa) && substr($no_wa, 0, 1) === '0') {
    $no_wa = '6283862514460' . substr($no_wa, 1);
}

$default_image = 'uploads/tampilankos.jpeg';

// Data pengguna untuk navbar
$user_login_id = $_SESSION['user_id'] ?? null;
$user_data_session = [];
if ($user_login_id) {
    $q_user = mysqli_query($db, "SELECT * FROM user WHERE user_id = '$user_login_id'");
    if ($q_user) {
        $user_data_session = mysqli_fetch_assoc($q_user);
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
    <title><?= htmlspecialchars($kos['nama_kos']); ?> - Detail Kos - Hynut KOST</title>
    <link rel="icon" href="uploads/logohynut.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
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

- {
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
    font-size: 15px;
    font-weight: 800;
    transition: 0.3s;
}

.nav-menu a:hover,
.nav-menu a.active-link {
    color: var(--pink-dark);
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

.container {
    max-width: 1100px;
    margin: 35px auto;
    background: var(--white);
    padding: 35px;
    border-radius: 22px;
    border: 1px solid var(--pink-border);
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.detail-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 35px;
    align-items: start;
}

.main-img {
    width: 100%;
    height: 320px;
    object-fit: cover;
    border-radius: 16px;
    background: var(--pink-light);
    box-shadow: 0 4px 12px rgba(217, 133, 163, 0.08);
}

.thumbs {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.thumbs img {
    width: 85px;
    height: 70px;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    border: 2px solid var(--pink-border);
    transition: 0.3s;
}

.thumbs img:hover {
    border-color: var(--pink);
    transform: scale(1.03);
}

.title {
    font-size: 24px;
    color: var(--text);
    margin-bottom: 6px;
    font-weight: 800;
}

.lokasi {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 14px;
    line-height: 1.5;
}

.harga {
    font-size: 24px;
    font-weight: 800;
    color: var(--pink-dark);
    margin-bottom: 6px;
}

.harga span {
    font-size: 13px;
    color: var(--muted);
    font-weight: normal;
}

.rating {
    font-size: 13px;
    color: #D9A13B;
    font-weight: 700;
    margin-bottom: 20px;
}

.fasilitas-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 12px;
    border-top: 1px solid var(--pink-border);
    padding-top: 18px;
}

.fasilitas-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    font-size: 13px;
    color: var(--muted);
    font-weight: 500;
}

.booking-box {
    margin-top: 25px;
    background: var(--pink-light);
    padding: 22px;
    border-radius: 18px;
    border: 1px solid var(--pink-border);
}

.booking-box label {
    font-size: 13px;
    font-weight: 700;
    color: var(--pink-dark);
    display: block;
    margin-bottom: 8px;
}

.booking-box input[type="date"] {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--pink-border);
    border-radius: 12px;
    outline: none;
    margin-bottom: 14px;
    font-size: 13px;
    background: var(--white);
    font-family: 'Poppins', sans-serif;
    color: var(--text);
}

.booking-box input[type="date"]:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

.btn-booking {
    width: 100%;
    padding: 12px;
    background: var(--pink);
    color: var(--white);
    border: none;
    border-radius: 30px;
    font-weight: 700;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s;
    box-shadow: 0 4px 14px rgba(217, 133, 163, 0.18);
}

.btn-booking:hover {
    background: var(--pink-dark);
    transform: translateY(-1px);
}

.deskripsi {
    grid-column: 1 / -1;
    margin-top: 30px;
    border-top: 1px solid var(--pink-border);
    padding-top: 22px;
}

.deskripsi h3 {
    font-size: 17px;
    margin-bottom: 10px;
    color: var(--text);
    font-weight: 800;
}

.deskripsi p {
    font-size: 14px;
    color: var(--muted);
    line-height: 1.8;
}

.actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.btn-action {
    flex: 1;
    padding: 12px 20px;
    border-radius: 30px;
    font-weight: 700;
    text-align: center;
    font-size: 13px;
    border: none;
    cursor: pointer;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-hubungi {
    background: #55B987;
    color: var(--white);
    box-shadow: 0 4px 14px rgba(85, 185, 135, 0.16);
}

.btn-hubungi:hover {
    background: #419F70;
    transform: translateY(-1px);
}

.btn-tandai {
    background: var(--pink-light);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
}

.btn-tandai:hover {
    background: var(--pink);
    color: var(--white);
}

.review-section {
    grid-column: 1 / -1;
    margin-top: 35px;
    border-top: 1px solid var(--pink-border);
    padding-top: 25px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.review-header h3 {
    font-size: 18px;
    color: var(--text);
    font-weight: 800;
}

.review-box {
    background: var(--pink-bg);
    border: 1px solid var(--pink-border);
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 12px;
}

.review-user {
    font-weight: 700;
    font-size: 13px;
    color: var(--text);
    display: flex;
    justify-content: space-between;
}

.review-stars {
    color: #D9A13B;
    font-size: 12px;
    margin: 6px 0;
}

.review-text {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.6;
}

.review-date {
    font-size: 11px;
    color: var(--muted);
    font-weight: normal;
}

.form-review-box {
    background: var(--pink-light);
    padding: 20px;
    border-radius: 18px;
    border: 1px solid var(--pink-border);
    margin-bottom: 22px;
}

.form-review-box label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}

.form-review-box select,
.form-review-box textarea {
    width: 100%;
    padding: 10px 14px;
    margin-top: 6px;
    margin-bottom: 12px;
    border: 1px solid var(--pink-border);
    border-radius: 12px;
    font-size: 13px;
    outline: none;
    background: var(--white);
    font-family: 'Poppins', sans-serif;
    color: var(--text);
}

.form-review-box select:focus,
.form-review-box textarea:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

.btn-submit-review {
    background: var(--pink);
    color: var(--white);
    border: none;
    padding: 10px 22px;
    border-radius: 30px;
    font-weight: 700;
    cursor: pointer;
    font-size: 13px;
    transition: 0.3s;
}

.btn-submit-review:hover {
    background: var(--pink-dark);
}

@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .navbar-container {
        padding: 12px 18px;
    }
    .nav-menu {
        display: none;
    }
    .container {
        width: 95%;
        padding: 20px 15px;
        margin: 20px auto;
    }
    .actions {
        flex-direction: column;
    }
    .main-img {
        height: 260px;
    }
}
    </style>
</head>
<body>

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
            <a href="daftarkos.php">Daftar Kost</a>
            <a href="tentang.php">Tentang</a>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profil.php" class="btn-profile">
                    <img src="<?= htmlspecialchars($foto_session); ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
                    <span><?= htmlspecialchars($user_data_session['nama'] ?? $_SESSION['nama'] ?? 'User'); ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <div class="detail-grid">
        
        <div>
            <?php $foto_utama = !empty($fotos) ? 'uploads/' . $fotos[0] : $default_image; ?>
            <img id="mainImage" src="<?= htmlspecialchars($foto_utama); ?>" class="main-img" onerror="this.src='<?= $default_image; ?>';">
            
            <?php if (!empty($fotos)): ?>
                <div class="thumbs">
                    <?php foreach ($fotos as $f): ?>
                        <img src="uploads/<?= htmlspecialchars($f); ?>" onclick="document.getElementById('mainImage').src=this.src;" onerror="this.src='<?= $default_image; ?>';">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <h1 class="title"><?= htmlspecialchars($kos['nama_kos']); ?></h1>
            <p class="lokasi"><i class="fa-solid fa-location-dot" style="color: var(--pink);"></i> <?= htmlspecialchars($kos['alamat']); ?></p>

            <div class="harga">
                <?= $harga_mulai > 0 ? 'Rp ' . number_format($harga_mulai, 0, ',', '.') : 'Harga belum tersedia'; ?>
                <?php if ($harga_mulai > 0): ?><span>/ bulan</span><?php endif; ?>
            </div>

            <div class="rating">
                ★ <?= $rating; ?> <span style="color:var(--muted); font-weight:normal;">(<?= $total_review; ?> ulasan)</span>
            </div>

            <div class="fasilitas-title">Fasilitas Tersedia</div>
            <div class="fasilitas-grid">
                <?php if ($res_fas && mysqli_num_rows($res_fas) > 0): ?>
                    <?php while ($f = mysqli_fetch_assoc($res_fas)): ?>
                        <div><i class="fa-solid fa-check" style="color: var(--pink);"></i> <?= htmlspecialchars($f['nama_fasilitas']); ?></div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div><i class="fa-solid fa-check" style="color: var(--pink);"></i> WiFi</div>
                    <div><i class="fa-solid fa-check" style="color: var(--pink);"></i> AC</div>
                    <div><i class="fa-solid fa-check" style="color: var(--pink);"></i> Kamar Mandi Dalam</div>
                    <div><i class="fa-solid fa-check" style="color: var(--pink);"></i> Kasur & Lemari</div>
                <?php endif; ?>
            </div>

            <form action="proses_booking.php" method="POST" class="booking-box">
                <input type="hidden" name="kos_id" value="<?= $kos['kos_id']; ?>">
                
                <label><i class="fa-regular fa-calendar-days"></i> Pilih Tanggal Survey / Masuk:</label>
                <input type="date" name="tanggal_survey" required min="<?= date('Y-m-d'); ?>">

                <button type="submit" class="btn-booking">
                    <i class="fa-solid fa-clipboard-list"></i> Ajukan Booking Kost
                </button>
            </form>
        </div>

        <div class="deskripsi">
            <h3>Tentang Kost Ini</h3>
            <p>
                <?= htmlspecialchars($kos['nama_kos']); ?> merupakan kost nyaman dan bersih dengan lokasi strategis di <?= htmlspecialchars($kos['alamat']); ?>. 
                Sangat cocok untuk mahasiswa maupun pekerja. Pemilik kost: <strong><?= htmlspecialchars($kos['nama_owner'] ?? '-'); ?></strong>.
            </p>
        </div>

        <div class="actions">
            <?php if (!empty($no_wa)): ?>
                <a href="https://wa.me/<?= $no_wa; ?>?text=<?= urlencode('Halo, saya tertarik dengan ' . $kos['nama_kos'] . '. Apakah kost ini masih tersedia?'); ?>"
   target="_blank"
   rel="noopener noreferrer"
   class="btn-action btn-hubungi">
                    <i class="fa-brands fa-whatsapp"></i> Hubungi Pemilik via WA
                </a>
            <?php else: ?>
                <button class="btn-action btn-hubungi" style="background:#888;" onclick="alert('Nomor WA Pemilik belum tersedia.')"><i class="fa-brands fa-whatsapp"></i> Hubungi Pemilik</button>
            <?php endif; ?>
            <button class="btn-action btn-tandai" onclick="alert('Berhasil ditandai!')"><i class="fa-solid fa-heart"></i> Simpan Favorit</button>
        </div>

        <div class="review-section">
            <div class="review-header">
                <h3>💬 Ulasan & Review Penghuni</h3>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="proses_review.php" method="POST" class="form-review-box">
                    <input type="hidden" name="kos_id" value="<?= $kos['kos_id']; ?>">
                    <input type="hidden" name="booking_id" value="<?= $user_booking_id; ?>">
                    
                    <label><strong>Beri Ulasan Kamu:</strong></label>
                    <select name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                        <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                        <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                        <option value="2">⭐⭐ (2 - Buruk)</option>
                        <option value="1">⭐ (1 - Sangat Buruk)</option>
                    </select>

                    <textarea name="komentar" rows="3" placeholder="Tuliskan ulasan kamu tentang kost ini..." required></textarea>
                    
                    <button type="submit" name="submit_review" class="btn-submit-review">Kirim Ulasan</button>
                </form>
            <?php else: ?>
                <p style="font-size:13px; color:var(--muted); margin-bottom:15px; background:#fff0f7; padding:12px; border-radius:12px; border:1px solid #f5c7dc;">
                    Ingin memberikan ulasan? Silakan <a href="login.php" style="color:var(--pink); font-weight:700;">Login</a> terlebih dahulu.
                </p>
            <?php endif; ?>

            <?php if ($res_list_review && mysqli_num_rows($res_list_review) > 0): ?>
                <?php while ($rev = mysqli_fetch_assoc($res_list_review)): ?>
                    <div class="review-box">
                        <div class="review-user">
                            <span><i class="fa-solid fa-user-circle" style="color: var(--pink);"></i> <?= htmlspecialchars($rev['nama_user']); ?></span>
                            <span class="review-date"><?= date('d M Y', strtotime($rev['dibuat_pada'])); ?></span>
                        </div>
                        <div class="review-stars">
                            <?= str_repeat('★', (int)$rev['rating']); ?><?= str_repeat('☆', 5 - (int)$rev['rating']); ?>
                        </div>
                        <div class="review-text">
                            "<?= htmlspecialchars($rev['komentar']); ?>"
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="font-size:13px; color:var(--muted); font-style:italic;">Belum ada ulasan untuk kost ini.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>