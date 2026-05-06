<?php
session_start();
include '../config/helpers.php';
require '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_users.php');
    exit;
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);


if ($id <= 0 || $id == 1 || $id == $_SESSION['user_id']) {
    die("Tidak boleh menghapus Super Admin atau akun yang sedang aktif.");
}


$stmt = $pdo->prepare("DELETE FROM users_admin WHERE id = ?");
$stmt->execute([$id]);

header("Location: kelola_users.php");
exit;
?>
