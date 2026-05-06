<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!csrf_check_ajax()) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid. Silakan muat ulang halaman.']);
    exit;
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];

    
    if (!in_array($status, ['pending', 'diterima', 'ditolak', 'revisi'])) {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        exit;
    }

    
    if ($status === 'revisi') {
        $pesan = $_POST['pesan_revisi'] ?? '';
        $stmt = $pdo->prepare("UPDATE users_siswa SET status = ?, pesan_revisi = ? WHERE id = ?");
        $update = $stmt->execute([$status, $pesan, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users_siswa SET status = ?, pesan_revisi = NULL WHERE id = ?");
        $update = $stmt->execute([$status, $id]);
    }

    if ($update) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
}
?>