<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: login.php");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    $db_password = $user['password'];

    $password_info = password_get_info($db_password);

    if (($password_info['algoName'] ?? 'unknown') !== 'unknown') {
        $valid = password_verify($password, $db_password);
    } else {
        $valid = hash_equals($db_password, $password);
    }

    if ($valid) {
        session_regenerate_id(true);

        $_SESSION['is_login'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        $role = strtolower(trim($user['role']));

        if ($role === 'admin') {
            header("Location: index.php");
        } elseif ($role === 'owner') {
            header("Location: layout/headerowner.php");
        } else {
            header("Location: index.php");
        }
        exit;
    }
}

echo "<script>
alert('Email atau password salah!');
window.location='login.php';
</script>";

mysqli_stmt_close($stmt);
exit;
?>