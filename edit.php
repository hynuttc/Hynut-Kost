<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

// Menyesuaikan koneksi database
if (!isset($db) && isset($conn)) {
    $db = $conn;
} elseif (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}
if (!isset($db) || !($db instanceof mysqli)) {
    die("Koneksi database gagal. Periksa kembali file database.php.");
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$query = mysqli_query($db, "SELECT * FROM user WHERE user_id = '$user_id'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = mysqli_real_escape_string($db, trim($_POST['nama'] ?? ''));
    $email = mysqli_real_escape_string($db, trim($_POST['email'] ?? ''));
    $no_wa = mysqli_real_escape_string($db, trim($_POST['no_wa'] ?? ''));

    // Ambil foto lama dari database atau session
    $nama_foto = $user['foto'] ?? ($_SESSION['foto'] ?? 'default.jpeg');
    $foto_lama = $nama_foto;
    $upload_berhasil = false;

    if ($nama === '' || $email === '' || $no_wa === '') {
        $error = "Semua kolom wajib diisi!";
    }

    // UPLOAD FOTO
    if (
        empty($error) &&
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $error = "Terjadi kesalahan saat mengunggah foto!";
        } else {

            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_ext = strtolower(pathinfo(
                $_FILES['foto']['name'],
                PATHINFO_EXTENSION
            ));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed_ext, true)) {
                $error = "Format foto harus JPG, JPEG, PNG, atau GIF!";
            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $error = "Ukuran foto maksimal 2 MB!";
            } elseif (getimagesize($file_tmp) === false) {
                $error = "File yang diunggah bukan gambar yang valid!";
            } else {

                $nama_foto = 'user_' . $user_id . '.' . $file_ext;
                $tujuan = 'uploads/' . $nama_foto;

                if (move_uploaded_file($file_tmp, $tujuan)) {
                    $upload_berhasil = true;
                } else {
                    $error = "Gagal mengunggah foto baru!";
                }
            }
        }
    }

    // UPDATE DATABASE (Menyertakan kolom foto)
    if (empty($error)) {

        $update = mysqli_query(
            $db,
            "UPDATE user SET
                nama = '$nama',
                email = '$email',
                no_wa = '$no_wa',
                foto = '$nama_foto'
             WHERE user_id = '$user_id'"
        );

        if ($update) {

            if ($upload_berhasil) {

                // Hapus foto lama jika berbeda
                if (
                    !empty($foto_lama) &&
                    $foto_lama !== 'default.jpeg' &&
                    $foto_lama !== $nama_foto
                ) {
                    $old_file = 'uploads/' . basename($foto_lama);

                    if (file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }

                // Bersihkan file sisa dengan ekstensi lain
                foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                    $old_file = 'uploads/user_' . $user_id . '.' . $ext;

                    if (
                        file_exists($old_file) &&
                        basename($old_file) !== $nama_foto
                    ) {
                        @unlink($old_file);
                    }
                }
            }

            // PERBARUI SESSION AGAR LANGSUNG BERUBAH DI BERANDA & PROFIL
            $_SESSION['foto'] = $nama_foto;
            $_SESSION['nama'] = $nama;

            header("Location: profil.php?update=" . time());
            exit;

        } else {
            $error = "Gagal memperbarui profil!";
        }
    }
}

// FOTO PROFIL
$foto_user = (
    !empty($_SESSION['foto']) &&
    file_exists('uploads/' . basename($_SESSION['foto']))
)
    ? 'uploads/' . basename($_SESSION['foto']) . '?v=' . time()
    : 'uploads/default.jpeg';

$nama_user = htmlspecialchars($user['nama'] ?? '');
$email_user = htmlspecialchars($user['email'] ?? '');
$no_wa_user = htmlspecialchars($user['no_wa'] ?? '');
$role_user = htmlspecialchars($user['role'] ?? 'User');

$foto_html = htmlspecialchars($foto_user);
$nama_html = htmlspecialchars($_SESSION['nama'] ?? $user['nama']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Hynut KOST</title>
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
    position: relative;
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

.nav-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.btn-back-home {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--pink-dark);
    background: var(--pink-light);
    border: 1px solid var(--pink-border);
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    transition: 0.3s;
}

.btn-back-home:hover {
    background: var(--pink);
    color: var(--white);
    transform: translateY(-1px);
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
    margin-left: auto;
}

.navbar-profile-img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--pink);
}

.layout {
    max-width: 1300px;
    margin: 35px auto;
    padding: 0 25px;
    display: grid;
    grid-template-columns: 285px 1fr;
    gap: 28px;
    align-items: start;
}

.sidebar {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    padding: 28px 20px 0;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.sidebar-user {
    text-align: center;
    padding-bottom: 25px;
    border-bottom: 1px solid var(--pink-border);
}

.avatar-container {
    width: 110px;
    height: 110px;
    margin: 0 auto 13px;
    border-radius: 50%;
    border: 4px solid var(--pink);
    padding: 3px;
    overflow: hidden;
}

.avatar-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.sidebar-user strong {
    display: block;
    font-size: 18px;
    color: var(--text);
    margin-bottom: 4px;
}

.sidebar-user span {
    color: var(--muted);
    font-size: 13px;
    text-transform: capitalize;
}

.menu-list {
    list-style: none;
    margin-top: 20px;
}

.menu-list li {
    margin-bottom: 8px;
}

.menu-list a {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 13px 17px;
    color: var(--text);
    font-size: 14px;
    font-weight: 600;
    border-radius: 12px;
    transition: 0.3s;
}

.menu-list a:hover,
.menu-list a.active {
    background: var(--pink-light);
    color: var(--pink-dark);
    font-weight: 700;
}

.menu-icon {
    font-size: 18px;
    width: 25px;
    text-align: center;
    color: var(--pink-dark);
}

.sidebar-decoration {
    margin: 30px -20px 0;
    padding: 25px 10px 15px;
    text-align: center;
    background: linear-gradient(180deg, var(--white) 0%, var(--pink-light) 100%);
    font-size: 12px;
    color: var(--pink-dark);
    font-weight: 600;
}

.main-content {
    min-width: 0;
}

.welcome-banner {
    min-height: 170px;
    padding: 30px 38px;
    border-radius: 22px;
    background: linear-gradient(120deg, #F6D5E0, #FFF0F5);
    border: 1px solid var(--pink-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
    margin-bottom: 22px;
}

.welcome-text {
    position: relative;
    z-index: 2;
}

.welcome-text h1 {
    font-size: 28px;
    color: var(--text);
    margin-bottom: 8px;
    font-weight: 800;
}

.welcome-text p {
    color: var(--muted);
    font-size: 14px;
}

.welcome-icon {
    position: relative;
    z-index: 2;
    margin-right: 20px;
}

.welcome-icon img {
    width: 100px;
    height: 100px;
    object-fit: contain;
    opacity: 0.9;
}

.welcome-circle {
    position: absolute;
    width: 240px;
    height: 240px;
    background: rgba(255, 255, 255, 0.35);
    border-radius: 50%;
    right: 100px;
    top: 35px;
}

.main-card {
    background: var(--white);
    border: 1px solid var(--pink-border);
    border-radius: 22px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(217, 133, 163, 0.07);
}

.form-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 30px;
    align-items: start;
}

.photo-section {
    text-align: center;
}

.photo-section h3 {
    font-size: 18px;
    margin-bottom: 18px;
    color: var(--text);
    font-weight: 800;
}

.preview-container {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    border: 4px solid var(--pink);
    padding: 3px;
    overflow: hidden;
    margin: 0 auto 15px;
    background: var(--pink-bg);
}

.preview-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.btn-choose {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--pink-light);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
}

.btn-choose:hover {
    background: var(--pink);
    color: var(--white);
}

.photo-note {
    font-size: 11px;
    color: var(--muted);
    margin-top: 10px;
}

.form-section {
    min-width: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 14px;
    color: var(--text);
    margin-bottom: 8px;
    font-weight: 700;
}

.input-wrapper {
    display: flex;
    align-items: center;
    width: 100%;
    border: 1px solid var(--pink-border);
    border-radius: 14px;
    overflow: hidden;
    background: var(--white);
    transition: 0.3s;
}

.input-wrapper:focus-within {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

.input-icon {
    width: 50px;
    min-width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--pink-light);
    color: var(--pink-dark);
    font-size: 16px;
}

.form-control {
    width: 100%;
    height: 50px;
    padding: 0 16px;
    border: none;
    outline: none;
    background: transparent;
    color: var(--text);
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.form-control[readonly] {
    background: var(--pink-bg);
    color: var(--muted);
    cursor: not-allowed;
}

.role-wrapper {
    background: var(--pink-bg);
}

.alert-error {
    background: #FCE8ED;
    color: #B65375;
    padding: 12px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid var(--pink-border);
    font-size: 13px;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 25px;
    flex-wrap: wrap;
}

.btn-save {
    padding: 12px 26px;
    background: var(--pink);
    color: var(--white);
    border: none;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 4px 14px rgba(217, 133, 163, 0.18);
}

.btn-save:hover {
    background: var(--pink-dark);
    transform: translateY(-2px);
}

.btn-cancel {
    padding: 12px 26px;
    background: var(--white);
    color: var(--pink-dark);
    border: 1px solid var(--pink-border);
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: 0.3s;
}

.btn-cancel:hover {
    background: var(--pink-light);
}

@media (max-width: 1000px) {
    .layout {
        grid-template-columns: 240px 1fr;
        gap: 20px;
    }
    .form-layout {
        grid-template-columns: 200px 1fr;
        gap: 20px;
    }
    .preview-container {
        width: 140px;
        height: 140px;
    }
}

@media (max-width: 768px) {
    .navbar-container {
        padding: 12px 18px;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
    }
    .nav-center {
        position: static;
        transform: none;
        order: 3;
        width: 100%;
        text-align: center;
    }
    .btn-profile {
        margin-left: 0;
        order: 2;
    }
    .layout {
        grid-template-columns: 1fr;
        padding: 0 15px;
        margin: 20px auto;
    }
    .sidebar-decoration {
        display: none;
    }
    .menu-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .menu-list li {
        margin: 0;
    }
    .welcome-banner {
        padding: 25px 20px;
        min-height: 140px;
    }
    .welcome-text h1 {
        font-size: 22px;
    }
    .welcome-icon {
        display: none;
    }
    .form-layout {
        grid-template-columns: 1fr;
    }
    .photo-section {
        border-bottom: 1px solid var(--pink-border);
        padding-bottom: 20px;
        margin-bottom: 10px;
    }
    .preview-container {
        width: 150px;
        height: 150px;
    }
}

@media (max-width: 450px) {
    .main-card {
        padding: 20px 15px;
    }
    .action-buttons {
        flex-direction: column;
    }
    .btn-save,
    .btn-cancel {
        width: 100%;
        text-align: center;
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
        <div class="nav-center">
            <a href="index.php" class="btn-back-home">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <a href="profil.php" class="btn-profile">
            <img src="<?= $foto_html ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
            <span><?= $nama_html ?></span>
        </a>
    </div>
</nav>

<div class="layout">

    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="avatar-container">
                <img src="<?= $foto_html ?>" alt="Foto Profil" onerror="this.src='uploads/default.jpeg';">
            </div>
            <strong><?= $nama_html ?></strong>
            <span><?= $role_user ?></span>
        </div>

        <ul class="menu-list">
            <li>
                <a href="profil.php" class="active">
                    <span class="menu-icon"><i class="fa-solid fa-house"></i></span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="favorit.php">
                    <span class="menu-icon"><i class="fa-solid fa-heart"></i></span>
                    Favorit
                </a>
            </li>
            <li>
                <a href="booking.php">
                    <span class="menu-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    Pesanan
                </a>
            </li>
            <li>
                <a href="tentang.php">
                    <span class="menu-icon"><i class="fa-solid fa-circle-info"></i></span>
                    Tentang
                </a>
            </li>
        </ul>

        <div class="sidebar-decoration">
            <p>Temukan Kost Impianmu!</p>
        </div>
    </aside>

    <main class="main-content">

        <section class="welcome-banner">
            <div class="welcome-circle"></div>
            <div class="welcome-text">
                <h1>✎ Edit Profil</h1>
                <p>Perbarui informasi akun dan foto profil kamu di sini.</p>
            </div>
            <div class="welcome-icon">
                <img src="uploads/logohynut.png" alt="Logo Hynut Kost">
            </div>
        </section>

        <section class="main-card">

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-layout">

                    <div class="photo-section">
                        <h3>Foto Profil</h3>
                        <div class="preview-container">
                            <img
                                src="<?= $foto_html ?>"
                                id="previewFoto"
                                alt="Preview Foto Profil"
                                onerror="this.src='uploads/default.jpeg';"
                            >
                        </div>

                        <label for="foto" class="btn-choose">
                            <i class="fa-solid fa-camera"></i> Pilih Foto
                        </label>
                        <input
                            type="file"
                            id="foto"
                            name="foto"
                            accept=".jpg,.jpeg,.png,.gif"
                            hidden
                        >
                        <p class="photo-note"></p>
                    </div>

                    <div class="form-section">

                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <div class="input-icon"><i class="fa-solid fa-user"></i></div>
                                <input
                                    type="text"
                                    id="nama"
                                    name="nama"
                                    class="form-control"
                                    value="<?= $nama_user ?>"
                                    placeholder="Masukkan nama lengkap"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <div class="input-icon"><i class="fa-solid fa-envelope"></i></div>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= $email_user ?>"
                                    placeholder="Masukkan email"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="no_wa">No. HP / WA</label>
                            <div class="input-wrapper">
                                <div class="input-icon"><i class="fa-solid fa-phone"></i></div>
                                <input
                                    type="text"
                                    id="no_wa"
                                    name="no_wa"
                                    class="form-control"
                                    value="<?= $no_wa_user ?>"
                                    placeholder="Masukkan nomor HP / WA"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <div class="input-wrapper role-wrapper">
                                <div class="input-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= $role_user ?>"
                                    readonly
                                >
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn-save">
                                Simpan Perubahan
                            </button>
                            <a href="profil.php" class="btn-cancel">
                                Batal
                            </a>
                        </div>

                    </div>

                </div>
            </form>

        </section>

    </main>

</div>

<script>
document.getElementById('foto').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2 MB!');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>