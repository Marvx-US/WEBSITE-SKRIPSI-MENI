<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!csrf_check_ajax()) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid. Silakan muat ulang halaman.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : [];
    $status = $_POST['status'] ?? '';

    if (!empty($ids) && in_array($status, ['diterima', 'ditolak', 'pending', 'revisi'])) {
        $sanitized_ids = array_map('intval', $ids);
        $placeholders = rtrim(str_repeat('?,', count($sanitized_ids)), ',');
        
        $query = "UPDATE users_siswa SET status = ? WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($query);
        
     
        $params = array_merge([$status], $sanitized_ids);
        
        if ($stmt->execute($params)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}
?>
