
<?php
session_name('HYNUTKOST_SESSION');
session_start();
require_once __DIR__ . '/database.php';

/* =========================================
   KONEKSI DATABASE
========================================= */
if (!isset($db) && isset($conn)) {
    $db = $conn;
}
if (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}

if (!isset($db) || !($db instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}

/* =========================================
   FUNGSI RUPIAH
========================================= */
function rupiah($angka) {
    return ($angka > 0)
        ? 'Rp ' . number_format((int)$angka, 0, ',', '.')
        : 'Harga belum tersedia';
}

$default_image = 'uploads/tampilankos.jpeg';

/* =========================================
   DATA PENGGUNA
========================================= */
$user_id = $_SESSION['user_id'] ?? null;
$user = [];

if ($user_id) {
    $stmt = mysqli_prepare($db, "SELECT * FROM user WHERE user_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result_user = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result_user) ?: [];
        mysqli_stmt_close($stmt);
    }
}

// Ambil nama foto terbaru dari database ($user), jika kosong baru cek session
$nama_foto = !empty($user['foto']) ? $user['foto'] : ($_SESSION['foto'] ?? '');
$foto_session = 'uploads/default.jpeg';

if (!empty($nama_foto) && file_exists(__DIR__ . '/uploads/' . $nama_foto)) {
    // Tambahkan ?v=time() agar browser tidak menampilkan cache gambar lama
    $foto_session = 'uploads/' . $nama_foto . '?v=' . time();
    $_SESSION['foto'] = $nama_foto; // Perbarui session otomatis
}

/* =========================================
   QUERY KOS POPULER
========================================= */
$sql_populer = "
    SELECT k.*,

    (
        SELECT fk.path_foto
        FROM foto_kos fk
        WHERE fk.kos_id = k.kos_id
        ORDER BY fk.foto_id ASC
        LIMIT 1
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

$res_populer = mysqli_query($db, $sql_populer);

if (!$res_populer) {
    die("Query kos gagal: " . mysqli_error($db));
}

$mock_ratings = [5.0, 4.9, 4.8, 4.6, 4.5, 4.3, 4.1, 4.0];

$list_kos = [];
$i = 0;

while ($k = mysqli_fetch_assoc($res_populer)) {

    $db_rating = !empty($k['avg_rating'])
        ? (float)$k['avg_rating']
        : 0;

    $final_rating = ($db_rating >= 4.0)
        ? $db_rating
        : $mock_ratings[$i % count($mock_ratings)];

    $k['calculated_rating'] = $final_rating;

    $list_kos[] = $k;
    $i++;
}

/* URUTKAN RATING TERTINGGI */
usort($list_kos, function($a, $b) {
    return $b['calculated_rating'] <=> $a['calculated_rating'];
});

/* =========================================
   DATA CAROUSEL BEST SELLER
========================================= */
$best_seller = array_values(array_filter($list_kos, function($kos) {
    return $kos['calculated_rating'] >= 4.5;
}));

if (empty($best_seller) && !empty($list_kos)) {
    $best_seller = array_slice($list_kos, 0, 4);
}

/* =========================================
   DATA PETA
========================================= */
$locations = [];

foreach ($list_kos as $row) {

    if (!empty($row['latitude']) && !empty($row['longitude'])) {

        $lat = (float)$row['latitude'];
        $lng = (float)$row['longitude'];

        if ($lat > 0) {
            $lat = -$lat;
        }

        $foto = !empty($row['foto_kos'])
            ? 'uploads/' . $row['foto_kos']
            : $default_image;

        $kos_id = (int)$row['kos_id'];

        $query_kamar = "SELECT status_kamar FROM kamar WHERE kos_id = $kos_id";
        $res_kamar = mysqli_query($db, $query_kamar);

        $status_kamar = 'Terisi';

        if ($res_kamar) {
            while ($kamar = mysqli_fetch_assoc($res_kamar)) {
                if (strtolower(trim($kamar['status_kamar'])) == 'kosong') {
                    $status_kamar = 'Kosong';
                    break;
                }
            }
        }

        $locations[] = [
            'id' => $row['kos_id'],
            'nama' => $row['nama_kos'],
            'alamat' => $row['alamat'],
            'harga' => rupiah($row['harga_mulai']),
            'lat' => $lat,
            'lng' => $lng,
            'foto' => $foto,
            'status' => $status_kamar
        ];
    }
}

$json_locations = json_encode(
    $locations,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);

if ($json_locations === false) {
    $json_locations = '[]';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Beranda - Hynut KOST</title>

<link rel="icon" href="uploads/logohynut.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>

/* =========================================
   DASAR
========================================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --pink: #E8A1B8;
    --pink-dark: #D985A3;
    --pink-light: #FCEAF0;
    --pink-bg: #FFF7FA;
    --pink-border: #F5DCE5;
    --text: #51424A;
    --muted: #927F87;
    --white: #FFFFFF;
    --shadow: 0 10px 35px rgba(217,133,163,.10);
}

html {
    scroll-behavior: smooth;
    scroll-padding-top: 90px;
}

body {
    font-family: 'Poppins', sans-serif;
    background: var(--pink-bg);
    color: var(--text);
    overflow-x: hidden;
}

a {
    text-decoration: none;
}

/* =========================================
   NAVBAR
========================================= */
.navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: rgba(255,255,255,.97);
    backdrop-filter: blur(15px);
    border-bottom: 1px solid var(--pink-border);
}

.navbar-container {
    max-width: 1300px;
    margin: auto;
    padding: 12px 25px;
    min-height: 72px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.logo {
    display: flex;
    align-items: center;
    gap: 9px;
}

.logo-icon {
    width: 36px;
    height: 36px;
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
}

.logo-subtitle {
    font-size: 14px;
    font-weight: 700;
}

.nav-menu {
    display: flex;
    align-items: center;
    gap: 35px;
}

.nav-menu a {
    color: var(--muted);
    font-size: 15px;
    font-weight: 800;
    transition: .3s;
}

.nav-menu a:hover,
.nav-menu a.active-link {
    color: var(--pink-dark);
}

.nav-auth {
    display: flex;
    align-items: center;
}

.btn-daftar {
    background: var(--pink);
    color: white;
    padding: 11px 25px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    transition: .3s;
}

.btn-daftar:hover {
    background: var(--pink-dark);
    transform: translateY(-2px);
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

/* =========================================
   HERO BANNER
========================================= */
.hero {
    max-width: 1300px;
    margin: 38px auto 55px;
    padding: 0 25px;
}

.hero-banner {
    position: relative;
    overflow: hidden;
    min-height: 380px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 35px;
    padding: 45px;
    border-radius: 28px;
    background:
        radial-gradient(circle at 90% 10%, #FFFFFF 0, transparent 30%),
        linear-gradient(120deg, #FCEAF0, #F8DCE7);
    border: 1px solid var(--pink-border);
    box-shadow: var(--shadow);
}

.welcome-circle {
    position: absolute;
    width: 280px;
    height: 280px;
    background: rgba(255,255,255,.30);
    border-radius: 50%;
    right: 35%;
    top: -150px;
}

.hero-text {
    flex: 1;
    position: relative;
    z-index: 2;
    min-width: 0;
}

.hero h1 {
    font-size: 34px;
    line-height: 1.35;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 14px;
    max-width: 580px;
}

.hero p {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 24px;
    max-width: 570px;
}

/* =========================================
   HERO CAROUSEL BEST SELLER
========================================= */
.hero-banner-img-wrapper {
    width: 52%;
    height: 320px;
    flex-shrink: 0;
    position: relative;
    z-index: 2;
    overflow: hidden;
    border-radius: 22px;
    background: var(--pink-light);
    box-shadow: 0 12px 30px rgba(217,133,163,.15);
}

.hero-carousel-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform .8s ease-in-out;
    will-change: transform;
}

.hero-slide {
    flex: 0 0 100%;
    min-width: 100%;
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.hero-slide img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transform: none;
    transition: none;
}

.hero-slide-info {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 65px 20px 18px;
    color: white;
    background: linear-gradient(
        to top,
        rgba(0,0,0,.88),
        rgba(0,0,0,.48),
        transparent
    );
}

.hero-best-seller {
    display: inline-block;
    background: #E8A1B8;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 8px;
}

.hero-slide-info h3 {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 6px;
    color: white;
}

.hero-slide-info .hero-alamat {
    font-size: 12px;
    line-height: 1.6;
    margin-bottom: 7px;
    color: white;
}

.hero-slide-info .hero-harga {
    font-size: 15px;
    font-weight: 800;
    color: #FFD36A;
    margin-bottom: 0;
}

.hero-slide-info .hero-rating {
    position: absolute;
    right: 20px;
    bottom: 18px;
    font-size: 14px;
    font-weight: 800;
    color: #FFD36A;
}

/* =========================================
   SEARCH
========================================= */
.search-container {
    max-width: 590px;
}

.search-bar {
    display: flex;
    align-items: center;
    padding: 6px;
    padding-left: 18px;
    background: white;
    border: 1px solid var(--pink-border);
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(217,133,163,.07);
    margin-bottom: 15px;
}

.search-bar input {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 13px;
    color: var(--text);
}

.search-bar button {
    border: none;
    background: var(--pink);
    color: white;
    padding: 12px 23px;
    border-radius: 11px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: .3s;
}

.search-bar button:hover {
    background: var(--pink-dark);
}

.filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

.pill-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 19px;
    border-radius: 30px;
    background: white;
    border: 1px solid var(--pink-border);
    color: var(--pink-dark);
    font-size: 12px;
    font-weight: 700;
    transition: .3s;
}

.pill-btn:hover,
.pill-btn.active {
    background: var(--pink);
    color: white;
    border-color: var(--pink);
}

/* =========================================
   CONTAINER
========================================= */
.container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 25px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 23px;
}

.section-header h2 {
    font-size: 23px;
    font-weight: 800;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header a {
    font-size: 13px;
    color: var(--pink-dark);
    font-weight: 700;
}

.icon-box-circle {
    width: 34px;
    height: 34px;
    background: var(--pink);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    box-shadow: 0 3px 10px rgba(217,133,163,.25);
}

/* =========================================
   KARTU KOS POPULER
========================================= */
.kos-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 22px;
}

.kos-card {
    background: white;
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 7px 25px rgba(217,133,163,.06);
    transition: .35s;
}

.kos-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 15px 35px rgba(217,133,163,.16);
    border-color: var(--pink);
}

.kos-img-wrapper {
    width: 100%;
    height: 185px;
    position: relative;
    overflow: hidden;
    background: var(--pink-light);
}

.kos-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .5s;
}

.kos-card:hover .kos-img-wrapper img {
    transform: scale(1.07);
}

.badge-bestseller {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    background: white;
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    border-radius: 30px;
    padding: 6px 11px;
    font-size: 11px;
    font-weight: 700;
}

.kos-card-body {
    padding: 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 12px;
}

.kos-card-body h3 {
    font-size: 15px;
    color: var(--text);
    font-weight: 800;
    margin-bottom: 8px;
    line-height: 1.5;
    display: flex;
    align-items: center;
    gap: 8px;
}

.kos-card-body .lokasi {
    font-size: 11px;
    color: var(--muted);
    line-height: 1.7;
    margin-bottom: 10px;
}

.kos-card-body .lokasi i {
    color: var(--pink-dark);
    margin-right: 3px;
}

.kos-card-body .harga {
    color: var(--pink-dark);
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 7px;
}

.kos-card-body .rating {
    font-size: 12px;
    color: #D9A64A;
    font-weight: 700;
}

.btn-detail {
    width: 100%;
    display: block;
    text-align: center;
    background: var(--pink-light);
    border: 1px solid var(--pink-border);
    color: var(--pink-dark);
    padding: 11px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    transition: .3s;
}

.btn-detail:hover {
    background: var(--pink);
    color: white;
    border-color: var(--pink);
}

/* =========================================
   PETA
========================================= */
.map-section {
    margin-top: 75px;
}

.map-heading {
    margin-bottom: 20px;
}

.map-heading p {
    color: var(--muted);
    font-size: 13px;
    margin-top: 5px;
}

#map {
    width: 100%;
    height: 430px;
    border-radius: 24px;
    border: 1px solid var(--pink-border);
    box-shadow: var(--shadow);
    z-index: 1;
}

.custom-pin {
    background: transparent !important;
    border: none !important;
}

/* =========================================
   TENTANG HYNUT KOST
========================================= */
.about-card-wrapper {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 5% 5%, #FFFFFF 0, transparent 30%),
        linear-gradient(135deg,var(--pink-light),#F8DCE7);
    border: 1px solid var(--pink-border);
    border-radius: 28px;
    padding: 55px 40px;
    margin-top: 75px;
    text-align: center;
    box-shadow: var(--shadow);
}

.about-card-wrapper::before {
    content: "";
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.35);
    top: -100px;
    right: -60px;
}

.about-header,
.about-grid {
    position: relative;
    z-index: 1;
}

.about-icon-main {
    width: 70px;
    height: 70px;
    object-fit: contain;
    margin-bottom: 15px;
}

.about-header h2 {
    font-size: 28px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 13px;
}

.about-header p {
    max-width: 800px;
    margin: 0 auto 38px;
    font-size: 13px;
    color: var(--muted);
    line-height: 1.9;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 22px;
}

.about-box {
    background: rgba(255,255,255,.95);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    padding: 32px 22px;
    min-height: 195px;
    box-shadow: 0 8px 25px rgba(217,133,163,.06);
    transition: .35s;
}

.about-box:hover {
    transform: translateY(-7px);
    box-shadow: 0 12px 30px rgba(217,133,163,.13);
}

.about-box-icon {
    width: 55px;
    height: 55px;
    background: var(--pink-light);
    border-radius: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    color: var(--pink-dark);
    font-size: 23px;
}

.about-box h3 {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 10px;
}

.about-box p {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.8;
}

/* =========================================
   FOOTER
========================================= */
.footer {
    margin-top: 75px;
    background: white;
    border-top: 1px solid var(--pink-border);
    padding: 55px 25px 20px;
}

.footer-container {
    max-width: 1250px;
    margin: auto;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 55px;
    padding-bottom: 35px;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 17px;
}

.footer-logo img {
    width: 55px;
    height: 55px;
    object-fit: contain;
}

.footer-logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.footer-logo-text strong,
.footer-logo-text span {
    color: var(--pink-dark);
}

.footer-logo-text strong {
    font-size: 20px;
    font-weight: 800;
}

.footer-logo-text span {
    font-weight: 700;
    font-size: 14px;
}

.footer-col h4 {
    color: var(--text);
    font-size: 16px;
    font-weight: 800;
    margin-bottom: 20px;
}

.footer-col p {
    color: var(--muted);
    font-size: 12px;
    line-height: 1.9;
    margin-bottom: 12px;
}

.footer-contact i {
    color: var(--pink-dark);
    margin-right: 8px;
}

.footer-col ul {
    list-style: none;
}

.footer-col ul li {
    margin-bottom: 13px;
}

.footer-col ul li a {
    color: var(--muted);
    font-size: 12px;
    transition: .3s;
}

.footer-col ul li a:hover {
    color: var(--pink-dark);
    padding-left: 5px;
}

.footer-bottom {
    border-top: 1px solid var(--pink-border);
    padding-top: 20px;
    text-align: center;
}

.footer-bottom p {
    color: var(--muted);
    font-size: 11px;
}

/* =========================================
   RESPONSIVE TABLET
========================================= */
@media (max-width: 1100px) {

    .kos-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
    }

    .hero h1 {
        font-size: 29px;
    }

    .hero-banner {
        padding: 30px;
    }

    .hero-banner-img-wrapper {
        width: 48%;
        height: 290px;
    }

    .footer-container {
        gap: 30px;
    }
}

/* =========================================
   RESPONSIVE HP
========================================= */
@media (max-width: 768px) {

    .navbar-container {
        padding: 12px 15px;
    }

    .nav-menu {
        display: none;
    }

    .hero {
        padding: 0 15px;
        margin-top: 20px;
    }

    .hero-banner {
        flex-direction: column-reverse;
        align-items: stretch;
        padding: 23px;
        gap: 22px;
    }

    .hero-banner-img-wrapper {
        width: 100%;
        height: 240px;
        border-radius: 18px;
    }

    .hero h1 {
        font-size: 25px;
    }

    .hero p {
        font-size: 12px;
    }

    .hero-slide-info h3 {
        font-size: 15px;
    }

    .hero-slide-info {
        padding: 50px 13px 13px;
    }

    .hero-slide-info .hero-rating {
        right: 13px;
        bottom: 13px;
    }

    .container {
        padding: 0 15px;
    }

    .section-header h2 {
        font-size: 18px;
    }

    .kos-grid {
        grid-template-columns: repeat(2,minmax(0,1fr));
        gap: 12px;
    }

    .kos-img-wrapper {
        height: 145px;
    }

    .kos-card {
        border-radius: 16px;
    }

    .kos-card-body {
        padding: 12px;
    }

    .kos-card-body h3 {
        font-size: 13px;
    }

    .kos-card-body .lokasi {
        font-size: 10px;
    }

    .kos-card-body .harga {
        font-size: 12px;
    }

    .btn-detail {
        font-size: 10px;
        padding: 9px 5px;
    }

    .badge-bestseller {
        font-size: 9px;
        padding: 5px 8px;
    }

    .about-card-wrapper {
        padding: 35px 18px;
        margin-top: 45px;
    }

    .about-header h2 {
        font-size: 23px;
    }

    .about-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .about-box {
        min-height: auto;
        padding: 25px 18px;
    }

    #map {
        height: 350px;
    }

    .footer-container {
        grid-template-columns: 1fr;
        gap: 25px;
    }

    .footer {
        padding: 35px 20px 20px;
    }
}

/* =========================================
   RESPONSIVE HP KECIL
========================================= */
@media (max-width: 380px) {

    .kos-grid {
        grid-template-columns: 1fr;
    }

    .kos-img-wrapper {
        height: 190px;
    }

    .hero h1 {
        font-size: 22px;
    }

    .search-bar button {
        padding: 11px 13px;
    }

    .pill-btn {
        padding: 7px 12px;
        font-size: 11px;
    }
}

</style>
</head>

<body>

<!-- =========================================
     NAVBAR
========================================= -->
<nav class="navbar">
    <div class="navbar-container">

        <a href="index.php" class="logo">
            <img src="uploads/logohynut.png"
                 alt="Logo Hynut Kost"
                 class="logo-icon">

            <div class="logo-text-group">
                <span class="logo-title">Hynut</span>
                <span class="logo-subtitle">KOST</span>
            </div>
        </a>

        <div class="nav-menu" id="navMenu">
            <a href="index.php" class="active-link">Beranda</a>
            <a href="#daftar-kos">Kost</a>
            <a href="#peta-lokasi">Peta</a>
            <a href="#tentang">Tentang</a>
        </div>

        <div class="nav-auth">

            <?php if ($user_id): ?>

                <a href="profil.php" class="btn-profile">

                    <img src="<?= htmlspecialchars($foto_session); ?>"
                         alt="Foto Profil"
                         class="navbar-profile-img"
                         onerror="this.src='uploads/default.jpeg';">

                    <span>
                        <?= htmlspecialchars($user['nama'] ?? $_SESSION['nama'] ?? 'User'); ?>
                    </span>

                </a>

            <?php else: ?>

                <a href="login.php" class="btn-daftar">Masuk</a>

            <?php endif; ?>

        </div>
    </div>
</nav>

<!-- =========================================
     HERO BERANDA
========================================= -->
<section class="hero">
    <div class="hero-banner">

        <div class="welcome-circle"></div>

        <div class="hero-text">

            <h1>Temukan Kost Impianmu dengan Mudah!</h1>

            <p>
                Cari dan temukan kost yang sesuai kebutuhanmu
                di lokasi favoritmu, cepat, aman, dan terpercaya.
            </p>

            <div class="search-container">

                <form action="daftarkos.php"
                      method="GET"
                      class="search-bar">

                    <input type="text"
                           name="cari"
                           placeholder="Cari lokasi, nama kos, atau fasilitas...">

                    <button type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Cari
                    </button>

                </form>

                <div class="filter-pills">

                    <a href="daftarkos.php" class="pill-btn active">Semua</a>
                    <a href="daftarkos.php?tipe=Putri" class="pill-btn">Putri</a>
                    <a href="daftarkos.php?tipe=Putra" class="pill-btn">Putra</a>
                    <a href="daftarkos.php?tipe=Campur" class="pill-btn">Campur</a>

                </div>
            </div>
        </div>

        <!-- CAROUSEL BEST SELLER -->
        <div class="hero-banner-img-wrapper">

            <div class="hero-carousel-track" id="heroCarouselTrack">

                <?php if (!empty($best_seller)): ?>

                    <?php foreach ($best_seller as $kos): ?>

                        <?php
                        $foto_best = !empty($kos['foto_kos'])
                            ? 'uploads/' . $kos['foto_kos']
                            : $default_image;

                        $rating_best = $kos['calculated_rating'];
                        ?>

                        <div class="hero-slide">

                            <img
                                src="<?= htmlspecialchars($foto_best); ?>"
                                alt="<?= htmlspecialchars($kos['nama_kos']); ?>"
                                onerror="this.src='uploads/tampilankos.jpeg';"
                            >

                            <div class="hero-slide-info">

                                <span class="hero-best-seller">
                                    <i class="fa-solid fa-star"></i>
                                    Best Seller
                                </span>

                                <h3>
                                    <?= htmlspecialchars($kos['nama_kos']); ?>
                                </h3>

                                <p class="hero-alamat">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <?= htmlspecialchars($kos['alamat']); ?>
                                </p>

                                <p class="hero-harga">
                                    <?= rupiah($kos['harga_mulai']); ?>/bln
                                </p>

                                <div class="hero-rating">
                                    <i class="fa-solid fa-star"></i>
                                    <?= number_format($rating_best, 1); ?>
                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="hero-slide">
                        <img src="uploads/tampilankos.jpeg"
                             alt="Hynut Kost">
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<!-- =========================================
     DAFTAR KOS POPULER
========================================= -->
<div class="container">

    <div class="section-header" id="daftar-kos">

        <h2>
            <span class="icon-box-circle">
                <i class="fa-solid fa-house"></i>
            </span>
            Daftar Kost Populer
        </h2>

        <a href="daftarkos.php">
            Lihat Semua
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="kos-grid">

        <?php foreach ($list_kos as $k): ?>

            <?php
            $foto = !empty($k['foto_kos'])
                ? 'uploads/' . $k['foto_kos']
                : $default_image;

            $rating = $k['calculated_rating'];
            ?>

            <div class="kos-card">

                <div class="kos-img-wrapper">

                    <?php if ($rating >= 4.5): ?>
                        <div class="badge-bestseller">
                            <i class="fa-solid fa-star"></i>
                            Best Seller
                        </div>
                    <?php endif; ?>

                    <img
                        src="<?= htmlspecialchars($foto); ?>"
                        alt="<?= htmlspecialchars($k['nama_kos']); ?>"
                        onerror="this.src='uploads/tampilankos.jpeg';"
                    >

                </div>

                <div class="kos-card-body">

                    <div>

                        <h3>
                            <span class="icon-box-circle"
                                  style="width:28px;height:28px;font-size:12px;">
                                <i class="fa-solid fa-house"></i>
                            </span>

                            <?= htmlspecialchars($k['nama_kos']); ?>
                        </h3>

                        <p class="lokasi">
                            <i class="fa-solid fa-location-dot"></i>
                            <?= htmlspecialchars($k['alamat']); ?>
                        </p>

                        <p class="harga">
                            <?= rupiah($k['harga_mulai']); ?>

                            <small style="font-weight:400;color:#777687;font-size:11px;">
                                /bulan
                            </small>
                        </p>

                        <p class="rating">
                            <i class="fa-solid fa-star"></i>
                            <?= number_format($rating, 1); ?>
                        </p>

                    </div>

                    <a href="detail.php?id=<?= (int)$k['kos_id']; ?>"
                       class="btn-detail">

                        <i class="fa-solid fa-eye"></i>
                        Lihat Detail

                    </a>

                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <!-- =========================================
         PETA PERSEBARAN KOS
    ========================================= -->
    <section class="map-section" id="peta-lokasi">

        <div class="section-header map-heading">

            <div>

                <h2>
                    <span class="icon-box-circle">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </span>
                    Peta Persebaran Kost
                </h2>

                <p>
                    Temukan lokasi kost dengan mudah melalui peta.
                </p>

            </div>
        </div>

        <div id="map"></div>

    </section>

    <!-- =========================================
         TENTANG HYNUT KOST
    ========================================= -->
    <section class="about-card-wrapper" id="tentang">

        <div class="about-header">

            <img src="uploads/logohynut.png"
                 alt="Logo Hynut Kost"
                 class="about-icon-main">

            <h2>Tentang Hynut KOST</h2>

            <p>
                Hynut KOST merupakan platform pencarian hunian kost
                secara online yang dirancang untuk memudahkan pengguna
                menemukan tempat tinggal sesuai kebutuhan. Dengan
                informasi yang lengkap dan terpercaya, proses mencari
                kost menjadi lebih praktis, nyaman, dan efisien.
            </p>

        </div>

        <div class="about-grid">

            <div class="about-box">

                <div class="about-box-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <h3>Mudah dan Praktis</h3>

                <p>
                    Temukan berbagai pilihan kost berdasarkan lokasi,
                    kisaran harga, serta fasilitas yang diinginkan
                    dengan fitur pencarian yang sederhana dan mudah digunakan.
                </p>

            </div>

            <div class="about-box">

                <div class="about-box-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <h3>Terjamin dan Terpercaya</h3>

                <p>
                    Kami berupaya menghadirkan informasi kost yang telah
                    melalui proses verifikasi agar pengguna mendapatkan
                    informasi yang jelas dan dapat dipercaya.
                </p>

            </div>

            <div class="about-box">

                <div class="about-box-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>

                <h3>Layanan Siap Membantu</h3>

                <p>
                    Kami menyediakan layanan bantuan untuk menjawab
                    pertanyaan dan membantu pengguna selama proses
                    pencarian kost, sehingga pengalaman mencari hunian
                    terasa lebih nyaman.
                </p>

            </div>

        </div>
    </section>

</div>

<!-- =========================================
     FOOTER
========================================= -->
<footer class="footer">

    <div class="footer-container">

        <div class="footer-col">

            <div class="footer-logo">

                <img src="uploads/logohynut.png"
                     alt="Logo Hynut Kost">

                <div class="footer-logo-text">
                    <strong>Hynut</strong>
                    <span>Kost</span>
                </div>

            </div>

            <p>
                Platform terpercaya untuk menemukan hunian impian
                Anda dengan mudah, aman, dan cepat di berbagai wilayah.
            </p>

            <p class="footer-contact">
                <i class="fa-solid fa-envelope"></i>
                hynut@gmail.com
            </p>

            <p class="footer-contact">
                <i class="fa-solid fa-phone"></i>
                083862514460
            </p>

        </div>

        <div class="footer-col">

            <h4>Menu Utama</h4>

            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="daftarkos.php">Daftar Kost</a></li>
                <li><a href="index.php#peta-lokasi">Peta Lokasi</a></li>
                <li><a href="index.php#tentang">Tentang Kami</a></li>
            </ul>

        </div>

        <div class="footer-col">

            <h4>Kebijakan & Privasi</h4>

            <ul>
                <li><a href="#">Syarat & Ketentuan</a></li>
                <li><a href="#">Kebijakan Privasi</a></li>
                <li><a href="mailto:hynut@gmail.com">Bantuan & FAQ</a></li>
            </ul>

        </div>

    </div>

    <div class="footer-bottom">

        <p>
            Copyright &copy; Vina PermataSari 2026 Hynut KOST.
            Semua hak dilindungi.
        </p>

    </div>

</footer>

<!-- =========================================
     JAVASCRIPT NAVBAR
========================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const navLinks = document.querySelectorAll('#navMenu a');

    navLinks.forEach(link => {

        link.addEventListener('click', function() {

            navLinks.forEach(item => {
                item.classList.remove('active-link');
            });

            this.classList.add('active-link');

        });

    });

});
</script>

<!-- =========================================
     JAVASCRIPT CAROUSEL BEST SELLER
     INFINITE LOOP TANPA LOMPAT
========================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const track = document.getElementById('heroCarouselTrack');

    if (!track) return;

    const originalSlides = Array.from(
        track.querySelectorAll('.hero-slide')
    );

    const totalSlides = originalSlides.length;

    // Jika foto hanya satu, carousel tidak perlu berjalan
    if (totalSlides <= 1) return;

    // Gandakan foto pertama di bagian paling akhir
    const firstClone = originalSlides[0].cloneNode(true);
    track.appendChild(firstClone);

    let currentSlide = 0;
    let isMoving = false;

    function nextSlide() {

        if (isMoving) return;

        isMoving = true;
        currentSlide++;

        track.style.transition = 'transform .8s ease-in-out';

        track.style.transform =
            `translateX(-${currentSlide * 100}%)`;
    }

    track.addEventListener('transitionend', function(event) {

        // Pastikan yang selesai bergerak adalah track carousel
        if (event.target !== track || event.propertyName !== 'transform') {
            return;
        }

        // Setelah foto duplikat tampil,
        // pindahkan posisi diam-diam ke foto pertama
        if (currentSlide === totalSlides) {

            track.style.transition = 'none';

            currentSlide = 0;

            track.style.transform = 'translateX(0)';

            // Paksa browser menerapkan posisi baru
            track.offsetHeight;
        }

        isMoving = false;

    });

    // Bergulir otomatis setiap 4 detik
    setInterval(nextSlide, 4000);

});
</script>

<!-- =========================================
     LEAFLET MAP
========================================= -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
var locations = <?= $json_locations; ?>;

var defaultLat = locations.length > 0
    ? locations[0].lat
    : -6.8852;

var defaultLng = locations.length > 0
    ? locations[0].lng
    : 107.6135;

var map = L.map('map').setView(
    [defaultLat, defaultLng],
    12
);

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: '&copy; OpenStreetMap contributors'
    }
).addTo(map);

/* PIN PETA */
function createCustomPin(color) {

    return L.divIcon({

        className: 'custom-pin',

        html: `
            <div style="
                background-color:${color};
                width:28px;
                height:28px;
                border-radius:50% 50% 50% 0;
                transform:rotate(-45deg);
                border:2px solid white;
                box-shadow:0 3px 6px rgba(0,0,0,.4);
                position:relative;
            ">

                <div style="
                    width:8px;
                    height:8px;
                    background:white;
                    border-radius:50%;
                    position:absolute;
                    top:8px;
                    left:8px;
                "></div>

            </div>
        `,

        iconSize: [28,28],
        iconAnchor: [14,28],
        popupAnchor: [0,-28]

    });

}

var greenPin = createCustomPin('#28a745');
var greyPin = createCustomPin('#6c757d');

/* TAMPILKAN SEMUA LOKASI KOS */
locations.forEach(function(item) {

    if (item.lat && item.lng) {

        var selectedIcon = (item.status === 'Kosong')
            ? greenPin
            : greyPin;

        var marker = L.marker(
            [item.lat,item.lng],
            {icon:selectedIcon}
        ).addTo(map);

        var statusBadge = (item.status === 'Kosong')

            ? '<span style="background:#28a745;color:white;padding:3px 9px;border-radius:5px;font-size:10px;font-weight:bold;">Kosong</span>'

            : '<span style="background:#6c757d;color:white;padding:3px 9px;border-radius:5px;font-size:10px;font-weight:bold;">Terisi</span>';

        var popupHTML = `

            <div style="
                text-align:center;
                padding:5px;
                width:185px;
            ">

                <img
                    src="${item.foto}"
                    style="
                        width:100%;
                        height:120px;
                        object-fit:cover;
                        border-radius:10px;
                        margin-bottom:7px;
                    "
                >

                <h4 style="
                    font-size:13px;
                    margin-bottom:5px;
                    color:#20243a;
                    font-weight:700;
                ">

                    <i class="fa-solid fa-house"
                       style="color:#c72f78;margin-right:4px;">
                    </i>

                    ${item.nama}

                </h4>

                <p style="
                    font-size:11px;
                    color:#777687;
                    margin-bottom:5px;
                ">

                    📍 ${item.alamat}

                </p>

                <p style="
                    font-size:11px;
                    margin-bottom:7px;
                ">

                    Status: ${statusBadge}

                </p>

                <div style="
                    font-size:12px;
                    font-weight:800;
                    color:#c72f78;
                    margin-bottom:9px;
                ">

                    ${item.harga}/bln

                </div>

                <a
                    href="detail.php?id=${encodeURIComponent(item.id)}"
                    style="
                        display:inline-block;
                        background:#c72f78;
                        color:white;
                        padding:7px 15px;
                        border-radius:20px;
                        font-size:11px;
                        text-decoration:none;
                        font-weight:700;
                    "
                >

                    Lihat Detail

                </a>

            </div>

        `;

        marker.bindPopup(popupHTML);

    }

});
</script>

</body>
</html>
