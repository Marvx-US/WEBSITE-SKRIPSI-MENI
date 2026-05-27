<?php
session_start();
include '../config/koneksi.php';

header('Content-Type: application/json');

// 1. Simpan Descriptor (Hanya untuk Admin yang sedang login)
if (isset($_POST['action']) && $_POST['action'] === 'save_face') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
        exit;
    }
    
    $descriptor = $_POST['descriptor'] ?? '';
    if (empty($descriptor)) {
        echo json_encode(['status' => 'error', 'message' => 'Data wajah kosong']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users_admin SET face_descriptor = ? WHERE id = ?");
    if ($stmt->execute([$descriptor, $_SESSION['user_id']])) {
        echo json_encode(['status' => 'success', 'message' => 'Data wajah berhasil disimpan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database']);
    }
    exit;
}

// 2. Login dengan Wajah (Menghitung Jarak Euclidean di Backend mencari ke SEMUA admin)
if (isset($_POST['action']) && $_POST['action'] === 'login_face') {
    $input_descriptor = json_decode($_POST['descriptor'] ?? '[]');
    
    if (empty($input_descriptor) || count($input_descriptor) !== 128) {
        echo json_encode(['status' => 'error', 'message' => 'Data wajah tidak valid atau tidak lengkap']);
        exit;
    }
    
    // Ambil semua admin yang sudah punya face_descriptor
    $stmt = $pdo->query("SELECT * FROM users_admin WHERE face_descriptor IS NOT NULL AND face_descriptor != ''");
    $admins = $stmt->fetchAll();
    
    if (!$admins) {
        echo json_encode(['status' => 'error', 'message' => 'Belum ada satupun admin yang merekam wajah di sistem.']);
        exit;
    }
    
    $best_match_user = null;
    $best_distance = 1.0; // Inisialisasi jarak terjauh
    
    // Loop semua admin untuk mencari jarak Euclidean terdekat (paling mirip)
    foreach ($admins as $user) {
        $stored_descriptor = json_decode($user['face_descriptor']);
        if (!$stored_descriptor || count($stored_descriptor) !== 128) continue;
        
        $sum = 0;
        for ($i = 0; $i < 128; $i++) {
            $sum += pow((float)$input_descriptor[$i] - (float)$stored_descriptor[$i], 2);
        }
        $distance = sqrt($sum);
        
        if ($distance < $best_distance) {
            $best_distance = $distance;
            $best_match_user = $user;
        }
    }
    
    // Batas Toleransi Jarak (Makin kecil makin mirip. < 0.45 sangat ketat)
    if ($best_match_user && $best_distance < 0.45) {
        // LULUS OTORISASI
        $_SESSION['user_id'] = $best_match_user['id'];
        $_SESSION['nama'] = $best_match_user['nama_lengkap'] ?? $best_match_user['username'];
        $_SESSION['role'] = 'admin';
        $_SESSION['role_admin'] = $best_match_user['role'];
        
        echo json_encode(['status' => 'success', 'message' => 'Otorisasi Berhasil! Selamat datang, ' . $_SESSION['nama']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Wajah tidak dikenali dalam sistem.']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
