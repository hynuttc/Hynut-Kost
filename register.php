
<?php
session_name('HYNUTKOST_SESSION');
session_start();
require_once __DIR__ . '/database.php';

// ========================================
// KONEKSI DATABASE
// ========================================
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
} elseif (!isset($conn) && isset($db)) {
    $conn = $db;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}

$error = '';

$nama = '';
$email = '';
$no_wa = '';


// ========================================
// PROSES REGISTER
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_wa = trim($_POST['no_wa'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $email === '' || $password === '') {
        $error = "Harap isi semua kolom yang wajib!";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";

    } else {

        // PERIKSA EMAIL
        $stmt = mysqli_prepare(
            $conn,
            "SELECT email FROM user WHERE email = ? LIMIT 1"
        );

        if (!$stmt) {
            $error = "Terjadi kesalahan database.";

        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $hasil = mysqli_stmt_get_result($stmt);
            $email_ada = mysqli_num_rows($hasil) > 0;

            mysqli_stmt_close($stmt);

            if ($email_ada) {
                $error = "Email sudah terdaftar!";

            } else {

                // HASH PASSWORD
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // SIMPAN DATA USER
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO user
                    (nama, email, password, role, no_wa, status_verifikasi)
                    VALUES (?, ?, ?, 'User', ?, 'Terverifikasi')"
                );

                if (!$stmt) {
                    $error = "Gagal menyiapkan pendaftaran.";

                } else {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssss",
                        $nama,
                        $email,
                        $password_hash,
                        $no_wa
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        $user_id = mysqli_insert_id($conn);
                        mysqli_stmt_close($stmt);

                        // =================================
                        // FOTO PROFIL DEFAULT
                        // =================================
                        $nama_foto = 'default.jpeg';

                        // =================================
                        // UPLOAD FOTO PROFIL
                        // =================================
                        if (
                            isset($_FILES['foto']) &&
                            $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
                        ) {

                            if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {

                                $file_tmp = $_FILES['foto']['tmp_name'];

                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mime = finfo_file($finfo, $file_tmp);
                                finfo_close($finfo);

                                $allowed_types = [
                                    'image/jpeg' => 'jpg',
                                    'image/png' => 'png',
                                    'image/gif' => 'gif'
                                ];

                                if (isset($allowed_types[$mime])) {

                                    $folder_upload = __DIR__ . '/uploads/';

                                    if (!is_dir($folder_upload)) {
                                        mkdir($folder_upload, 0755, true);
                                    }

                                    $ekstensi = $allowed_types[$mime];

                                    $nama_foto = 'user_' .
                                        $user_id . '.' . $ekstensi;

                                    if (!move_uploaded_file(
                                        $file_tmp,
                                        $folder_upload . $nama_foto
                                    )) {
                                        $nama_foto = 'default.jpeg';
                                    }

                                } else {
                                    $error = "Foto harus berformat JPG, PNG, atau GIF.";
                                }

                            } else {
                                $error = "Foto gagal diunggah.";
                            }
                        }

                        // =================================
                        // SIMPAN FOTO KE SESSION
                        // =================================
                        if ($error === '') {

                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['nama'] = $nama;
                            $_SESSION['role'] = 'User';
                            $_SESSION['foto'] = $nama_foto;
                            $_SESSION['is_login'] = true;

                            header("Location: profil.php");
                            exit;
                        }

                    } else {
                        $error = "Gagal mendaftar: " .
                            mysqli_stmt_error($stmt);

                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar - Nara KOST</title>

    <link rel="icon" href="uploads/logonara.png" type="image/png">

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

    /* NAVBAR */
    .navbar {
        background: var(--white);
        border-bottom: 1px solid var(--pink-border);
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(217, 133, 163, 0.08);
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

    .logo-title {
        color: var(--pink-dark);
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .logo-subtitle {
        color: var(--pink-dark);
        font-size: 14px;
        font-weight: 700;
    }

    .nav-menu {
        display: flex;
        gap: 35px;
        align-items: center;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    .nav-menu a.nav-item {
        color: var(--muted);
        font-size: 15px;
        font-weight: 700;
        transition: 0.3s;
    }

    .nav-menu a.nav-item:hover,
    .nav-menu a.nav-item.active-link {
        color: var(--pink-dark);
    }

    .nav-spacer {
        width: 100px;
    }

    /* AREA REGISTER */
    .register-page {
        width: 100%;
        padding: 35px 25px 50px;
    }

    .register-wrapper {
        max-width: 1150px;
        width: 100%;
        margin: auto;
        display: grid;
        grid-template-columns: 0.95fr 1.05fr;
        background: var(--white);
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--pink-border);
        box-shadow: 0 5px 20px rgba(217, 133, 163, 0.10);
    }

    /* BANNER */
    .register-banner {
        background: linear-gradient(145deg, var(--pink-light), #F8DCE6);
        padding: 48px 42px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .register-banner::before {
        content: "";
        position: absolute;
        width: 230px;
        height: 230px;
        background: rgba(232, 161, 184, 0.18);
        border-radius: 50%;
        top: -100px;
        left: -100px;
    }

    .register-banner-content {
        position: relative;
        z-index: 2;
    }

    .banner-title {
        font-size: 35px;
        line-height: 1.25;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 12px;
    }

    .banner-title span {
        color: var(--pink-dark);
    }

    .banner-description {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
        margin-bottom: 28px;
    }

    .banner-features {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .feature {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
    }

    .feature-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--pink);
        color: var(--white);
        font-size: 14px;
    }

    /* FORM REGISTER */
    .register-form-container {
        padding: 45px 55px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--white);
    }

    .form-logo {
        display: flex;
        justify-content: center;
        margin-bottom: 16px;
    }

    .form-logo-icon {
        width: 62px;
        height: 62px;
    }

    .form-logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .form-title {
        text-align: center;
        font-size: 30px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 7px;
    }

    .form-title span {
        color: var(--pink-dark);
    }

    .form-subtitle {
        text-align: center;
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 25px;
    }

    /* ERROR */
    .alert-error {
        background: #FCEAF0;
        color: #B65375;
        border: 1px solid var(--pink-border);
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 18px;
        font-size: 12px;
    }

    .alert-error i {
        margin-right: 6px;
    }

    /* INPUT */
    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        color: var(--pink-dark);
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .input-wrapper {
        position: relative;
    }

    .input-wrapper > i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--pink-dark);
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        height: 46px;
        padding: 0 15px 0 43px;
        border: 1px solid var(--pink-border);
        border-radius: 10px;
        background: var(--white);
        color: var(--text);
        outline: none;
        font-family: inherit;
        font-size: 12px;
        transition: 0.3s;
    }

    .form-group input[type="file"] {
        padding: 10px 15px;
        height: auto;
        background: var(--pink-bg);
        cursor: pointer;
    }

    .form-group input::placeholder {
        color: #B7A6AE;
    }

    .form-group input:focus {
        border-color: var(--pink);
        box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--muted);
        font-size: 15px;
        cursor: pointer;
    }

    /* TOMBOL */
    .btn-submit {
        width: 100%;
        height: 49px;
        border: none;
        border-radius: 30px;
        background: var(--pink);
        color: var(--white);
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 8px;
        box-shadow: 0 4px 12px rgba(217, 133, 163, 0.18);
    }

    .btn-submit:hover {
        background: var(--pink-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(217, 133, 163, 0.22);
    }

    .footer-link {
        margin-top: 20px;
        text-align: center;
        color: var(--muted);
        font-size: 12px;
    }

    .footer-link a {
        color: var(--pink-dark);
        font-weight: 700;
    }

    .footer-link a:hover {
        color: #C67594;
        text-decoration: underline;
    }

    /* RESPONSIVE */
    @media (max-width: 950px) {
        .nav-menu {
            position: static;
            transform: none;
        }

        .nav-spacer {
            display: none;
        }

        .register-wrapper {
            grid-template-columns: 0.9fr 1.1fr;
        }

        .register-banner {
            padding: 30px;
        }

        .register-form-container {
            padding: 35px 30px;
        }

        .banner-title {
            font-size: 29px;
        }
    }

    @media (max-width: 650px) {
        .navbar-container {
            padding: 12px 15px;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
        }

        .nav-menu {
            width: 100%;
            justify-content: center;
            gap: 20px;
        }

        .register-page {
            padding: 20px 12px 30px;
        }

        .register-wrapper {
            display: flex;
            flex-direction: column;
            border-radius: 17px;
        }

        .register-banner {
            padding: 27px 22px;
        }

        .banner-title {
            font-size: 28px;
        }

        .register-form-container {
            padding: 30px 22px;
        }

        .form-title {
            font-size: 25px;
        }
    }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-container">

        <a href="index.php" class="logo">
            <img src="uploads/logonara.png"
                 alt="Logo Narakost"
                 class="logo-icon">

            <div class="logo-text-group">
                <span class="logo-title">Nara</span>
                <span class="logo-subtitle">KOST</span>
            </div>
        </a>

        <div class="nav-menu">
            <a href="index.php" class="nav-item">Beranda</a>
            <a href="index.php#daftar-kos" class="nav-item">Kost</a>
            <a href="index.php#peta-lokasi" class="nav-item">Peta</a>
            <a href="index.php#tentang" class="nav-item">Tentang</a>
        </div>

        <div class="nav-spacer"></div>

    </div>
</nav>


<!-- HALAMAN REGISTER -->
<main class="register-page">

    <div class="register-wrapper">

        <!-- BAGIAN KIRI -->
        <section class="register-banner">

            <div class="register-banner-content">

                <h1 class="banner-title">
                    Bergabung dengan
                    <span>Nara KOST</span>
                </h1>

                <p class="banner-description">
                    Daftarkan akunmu dan temukan hunian kost impian
                    dengan mudah, cepat, dan aman bersama kami.
                </p>

                <div class="banner-features">

                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span>Banyak Pilihan Kost</span>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span>Aman & Terpercaya</span>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <span>Lokasi Strategis</span>
                    </div>

                </div>

            </div>

        </section>


        <!-- BAGIAN KANAN -->
        <section class="register-form-container">

            <div class="form-logo">
                <div class="form-logo-icon">
                    <img src="uploads/logonara.png"
                         alt="Logo Narakost">
                </div>
            </div>

            <h1 class="form-title">
                Daftar <span>Akun Baru</span>
            </h1>

            <p class="form-subtitle">
                Buat akun baru untuk mulai mencari kost
            </p>


            <!-- PESAN ERROR -->
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>


            <!-- FORM PENDAFTARAN -->
            <form action="" method="POST" enctype="multipart/form-data">

                <!-- NAMA -->
                <div class="form-group">
                    <label>Nama Lengkap</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            name="nama"
                            placeholder="Masukkan nama lengkap"
                            value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>
                </div>


                <!-- EMAIL -->
                <div class="form-group">
                    <label>Email</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>

                        <input
                            type="email"
                            name="email"
                            placeholder="Masukkan email"
                            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>
                </div>


                <!-- WHATSAPP -->
                <div class="form-group">
                    <label>No WhatsApp</label>

                    <div class="input-wrapper">
                        <i class="fa-brands fa-whatsapp"></i>

                        <input
                            type="text"
                            name="no_wa"
                            placeholder="Masukkan nomor WhatsApp"
                            value="<?= htmlspecialchars($no_wa, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                </div>


                <!-- PASSWORD -->
                <div class="form-group">
                    <label>Password</label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password"
                            required
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword()"
                            aria-label="Tampilkan password"
                        >
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>


                <!-- FOTO PROFIL -->
                <div class="form-group">
                    <label>Foto Profil (Opsional)</label>

                    <div class="input-wrapper">
                        <input
                            type="file"
                            name="foto"
                            accept=".jpg,.jpeg,.png,.gif"
                        >
                    </div>
                </div>


                <!-- TOMBOL DAFTAR -->
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i>
                    &nbsp; Daftar Sekarang
                </button>

            </form>


            <div class="footer-link">
                Sudah punya akun?
                <a href="login.php">Login di sini</a>
            </div>

        </section>

    </div>

</main>


<!-- JAVASCRIPT PASSWORD -->
<script>
function togglePassword() {

    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (password.type === 'password') {

        password.type = 'text';

        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');

    } else {

        password.type = 'password';

        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');

    }
}
</script>

</body>
</html>
