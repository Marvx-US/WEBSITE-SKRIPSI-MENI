<?php
require_once __DIR__ . '/database.php';

// Set zona waktu ke WITA (Waktu Indonesia Tengah) agar sinkron dengan waktu lokal user
date_default_timezone_set('Asia/Makassar');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ppdb";


$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}


$pdo = Database::getInstance();
?>