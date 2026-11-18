<?php
$host = "hayabusa.proxy.rlwy.net"; 
$port = "27848";                  
$user = "root";
$password = "rXsUCHFAYLdIgTmSnYfBQ1OWiAowdyVA";
$database = "railway";

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}