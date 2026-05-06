<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!csrf_check_ajax()) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid. Silakan muat ulang halaman.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID Siswa tidak valid']);
        exit;
    }

    $new_password = password_hash('123456', PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users_siswa SET password = ? WHERE id = ?");
    if ($stmt->execute([$new_password, $id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mereset password di database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
}
?>
