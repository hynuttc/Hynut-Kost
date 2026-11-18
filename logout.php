<?php
session_name('HYNUTKOST_SESSION');
session_start();

// Menghapus semua session pengguna yang sedang login
session_unset();
session_destroy();

// Mengarahkan ke halaman Beranda utama
header("Location: index.php");
exit;