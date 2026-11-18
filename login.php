<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

// ========================================
// KONEKSI DATABASE
// ========================================
if (!isset($conn) || !($conn instanceof mysqli)) {
    if (isset($db) && ($db instanceof mysqli)) {
        $conn = $db;
    } elseif (isset($koneksi) && ($koneksi instanceof mysqli)) {
        $conn = $koneksi;
    }
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}

$error = '';
$username_input = '';
$role_input = '';

// ========================================
// PROSES LOGIN
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    $username_input = $username;
    $role_input = $role;

    if ($username === '' || $password === '' || $role === '') {
        $error = "Harap isi semua kolom!";
    } else {

        // MENCARI USER BERDASARKAN NAMA
        $query = "SELECT * FROM user WHERE nama = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) {
            $error = "Terjadi kesalahan pada database.";
        } else {

            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {

                $user = mysqli_fetch_assoc($result);

                $db_password = $user['password'] ?? '';
                $db_role = strtolower(trim($user['role'] ?? ''));
                $input_role = strtolower(trim($role));
                $status_verifikasi = $user['status_verifikasi'] ?? 'Terverifikasi';

                // CEK STATUS VERIFIKASI (Jika masih Menunggu, tolak login)
                if ($status_verifikasi === 'Menunggu') {
                    $error = "Akun Anda masih <b>Menunggu Konfirmasi</b> dari Admin. Silakan tunggu hingga disetujui.";
                } else {

                    // CEK PASSWORD HASH ATAU BIASA
                    $password_info = password_get_info($db_password);

                    if (($password_info['algoName'] ?? 'unknown') !== 'unknown') {
                        $password_valid = password_verify($password, $db_password);
                    } else {
                        $password_valid = hash_equals($db_password, $password);
                    }

                    // CEK KESESUAIAN ROLE
                    if ($db_role !== $input_role) {

                        $error = "Role yang dipilih tidak sesuai dengan akun Anda!";

                    } elseif (!$password_valid) {

                        $error = "Password salah!";

                    } else {

                        // LOGIN BERHASIL
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['nama'] = $user['nama'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['is_login'] = true;

                        // ========================================
                        // MENCARI FOTO PROFIL
                        // ========================================
                        $found_foto = 'default.jpeg';
                        $extensions = ['jpg', 'jpeg', 'png', 'gif'];

                        foreach ($extensions as $ext) {
                            $filename = 'user_' . $user['user_id'] . '.' . $ext;
                            if (file_exists(__DIR__ . '/uploads/' . $filename)) {
                                $found_foto = $filename;
                                break;
                            }
                        }

                        $_SESSION['foto'] = $found_foto;

                        mysqli_stmt_close($stmt);

                        // ========================================
                        // REDIRECT BERDASARKAN ROLE
                        // ========================================
                        if ($db_role === 'admin') {
                            header("Location: index.php");
                            exit;
                        } elseif ($db_role === 'owner') {
                            header("Location: index2.php");
                            exit;
                        } elseif ($db_role === 'user') {
                            header("Location: index.php");
                            exit;
                        } else {
                            $error = "Role akun tidak dikenali.";
                        }
                    }
                }

            } else {
                $error = "Username tidak ditemukan!";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Hynut Kost</title>

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

.navbar {
    background: var(--white);
    border-bottom: 1px solid var(--pink-border);
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(217, 133, 163, 0.07);
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
    text-decoration: none;
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
    font-weight: 800;
    text-decoration: none;
    transition: color 0.2s;
}

.nav-menu a.nav-item:hover,
.nav-menu a.nav-item.active-link {
    color: var(--pink-dark);
}

.nav-spacer {
    width: 100px;
}

.login-page {
    width: 100%;
    padding: 35px 25px 50px;
}

.login-wrapper {
    max-width: 1150px;
    width: 100%;
    min-height: 620px;
    margin: auto;
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    background: var(--white);
    border-radius: 22px;
    overflow: hidden;
    border: 1px solid var(--pink-border);
    box-shadow: 0 15px 45px rgba(217, 133, 163, 0.12);
}

.login-banner {
    background: linear-gradient(145deg, var(--pink-light), #F5D2DF);
    padding: 48px 42px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.login-banner::before {
    content: "";
    position: absolute;
    width: 230px;
    height: 230px;
    background: rgba(232, 161, 184, 0.18);
    border-radius: 50%;
    top: -100px;
    left: -100px;
}

.login-banner-content {
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

.login-form-container {
    padding: 45px 55px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.form-logo {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.form-logo-icon {
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
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
    margin-bottom: 28px;
}

.alert-error {
    background: #F8DCE5;
    color: #B65375;
    border: 1px solid var(--pink-border);
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 18px;
    font-size: 12px;
    line-height: 1.5;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    color: var(--text);
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
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

.form-group input,
.form-group select {
    width: 100%;
    height: 49px;
    padding: 0 43px;
    border: 1px solid var(--pink-border);
    border-radius: 10px;
    background: var(--white);
    color: var(--text);
    outline: none;
    font-family: inherit;
    font-size: 12px;
    transition: 0.3s;
}

.form-group input::placeholder {
    color: #B5A5AC;
}

.form-group input:focus,
.form-group select:focus {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(232, 161, 184, 0.15);
}

.form-group select {
    cursor: pointer;
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
    transition: 0.2s;
}

.toggle-password:hover {
    color: var(--pink-dark);
}

.btn-submit {
    width: 100%;
    height: 51px;
    border: none;
    border-radius: 30px;
    background: linear-gradient(90deg, var(--pink), var(--pink-dark));
    color: var(--white);
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 5px;
    box-shadow: 0 6px 16px rgba(217, 133, 163, 0.2);
}

.btn-submit:hover {
    background: var(--pink-dark);
    transform: translateY(-2px);
}

.or-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 19px 0;
    color: var(--muted);
    font-size: 12px;
}

.or-divider::before,
.or-divider::after {
    content: "";
    height: 1px;
    flex: 1;
    background: var(--pink-border);
}

.btn-register {
    width: 100%;
    height: 49px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border: 1.5px solid var(--pink);
    border-radius: 30px;
    text-decoration: none;
    color: var(--pink-dark);
    font-size: 13px;
    font-weight: 700;
    transition: 0.3s;
}

.btn-register:hover {
    background: var(--pink-light);
    border-color: var(--pink-dark);
    color: var(--pink-dark);
}

@media (max-width: 950px) {
    .nav-menu {
        position: static;
        transform: none;
    }
    .nav-spacer {
        display: none;
    }
    .login-wrapper {
        grid-template-columns: 0.9fr 1.1fr;
    }
    .login-banner {
        padding: 30px;
    }
    .login-form-container {
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
        flex-wrap: wrap;
    }
    .nav-menu a.nav-item {
        font-size: 13px;
    }
    .login-page {
        padding: 20px 12px 30px;
    }
    .login-wrapper {
        display: flex;
        flex-direction: column;
        border-radius: 17px;
    }
    .login-banner {
        padding: 27px 22px;
    }
    .banner-title {
        font-size: 28px;
    }
    .login-form-container {
        padding: 30px 22px;
    }
    .form-title {
        font-size: 25px;
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
            <a href="index.php" class="nav-item">Beranda</a>
            <a href="index.php#daftar-kos" class="nav-item">Kost</a>
            <a href="index.php#peta-lokasi" class="nav-item">Peta</a>
            <a href="index.php#tentang" class="nav-item">Tentang</a>
        </div>

        <div class="nav-spacer"></div>
    </div>
</nav>

<main class="login-page">
    <div class="login-wrapper">

        <section class="login-banner">
            <div class="login-banner-content">
                <h1 class="banner-title">
                    Selamat Datang di
                    <span>Hynut KOST</span>
                </h1>
                <p class="banner-description">
                    Temukan kost terbaik, nyaman, dan sesuai
                    dengan kebutuhanmu. Cari tempat tinggal
                    impianmu dengan mudah, cepat, dan aman.
                </p>
                <div class="banner-features">
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-house"></i></div>
                        <span>Banyak Pilihan Kost</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <span>Aman & Terpercaya</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <span>Lokasi Strategis</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="login-form-container">
            <div class="form-logo">
                <div class="form-logo-icon">
                    <img src="uploads/logohynut.png" alt="Logo Hynut Kost">
                </div>
            </div>

            <h1 class="form-title">
                Login ke <span>Hynut KOST</span>
            </h1>
            <p class="form-subtitle">
                Masuk untuk melanjutkan ke akun Anda
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input
                            type="text"
                            name="username"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($username_input); ?>"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
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

                <div class="form-group">
                    <label>Role</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-users"></i>
                        <select name="role" required>
                            <option value="" disabled <?= $role_input === '' ? 'selected' : ''; ?>>
                                Pilih Role
                            </option>
                            <option value="Admin" <?= $role_input === 'Admin' ? 'selected' : ''; ?>>
                                Admin
                            </option>
                            <option value="Owner" <?= $role_input === 'Owner' ? 'selected' : ''; ?>>
                                Owner
                            </option>
                            <option value="User" <?= $role_input === 'User' ? 'selected' : ''; ?>>
                                User
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> &nbsp; Login
                </button>
            </form>

            <div class="or-divider">atau</div>

            <a href="register.php" class="btn-register">
                <i class="fa-solid fa-user-plus"></i> Daftar Akun Baru
            </a>
        </section>

    </div>
</main>

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