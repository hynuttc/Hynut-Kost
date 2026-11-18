<?php
session_start();

// Menghapus semua data session yang aktif
session_unset();
session_destroy();

// Mengarahkan kembali ke halaman Beranda utama
header("Location: index.php");
exit;