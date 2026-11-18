<?php

$host = "mysql.railway.internal";
$user = "root";
$password = "rXsuCHFAYLDlgtmSnYfBQlOWiAowdyvA";
$database = "railway";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


