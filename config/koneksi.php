<?php
require_once __DIR__ . '/database.php';

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