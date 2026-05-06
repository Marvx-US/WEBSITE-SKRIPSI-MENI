<?php




session_start();
include 'config/helpers.php';
include 'config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
    exit;
}

if (!csrf_check_ajax()) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid. Silakan muat ulang halaman.']);
    exit;
}

$nisn = trim($_POST['nisn'] ?? '');


if (!preg_match('/^\d{10}$/', $nisn)) {
    echo json_encode(['success' => false, 'message' => 'NISN harus terdiri dari 10 digit angka']);
    exit;
}

$stmt = $pdo->prepare("SELECT nama_lengkap, nisn, jenis_kelamin, nama_sd, status, pesan_revisi, tgl_buat FROM users_siswa WHERE nisn = ? LIMIT 1");
$stmt->execute([$nisn]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Data dengan NISN tersebut tidak ditemukan dalam sistem PPDB']);
    exit;
}


echo json_encode([
    'success'     => true,
    'data'        => [
        'nama'          => $row['nama_lengkap'] ?? '-',
        'nisn'          => $row['nisn'],
        'jenis_kelamin' => $row['jenis_kelamin'] ?? '-',
        'asal_sekolah'  => $row['nama_sd'] ?? '-',
        'status'        => $row['status'] ?? 'pending',
        'pesan_revisi'  => ($row['status'] === 'revisi') ? ($row['pesan_revisi'] ?? '') : '',
        'tgl_daftar'    => $row['tgl_buat'] ?? '-',
    ]
]);
