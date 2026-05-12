<?php
ob_start(); // Memulai output buffering untuk mencegah spasi tak terlihat merusak PDF
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    die("Akses ditolak.");
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users_siswa WHERE id = ?");
$stmt->execute([$user_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data tidak ditemukan.");
}

// GUARD: Hanya siswa berstatus 'diterima' yang boleh mencetak bukti pendaftaran.
// Ini adalah lapisan keamanan backend — tidak bisa dibypass dari URL langsung.
if ($data['status'] !== 'diterima') {
    http_response_code(403);
    die("
        <!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'>
        <title>Akses Ditolak | PPDB MTs Al-Barakah</title>
        <style>
            body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; 
                   min-height: 100vh; margin: 0; background: #f8faf9; }
            .box { text-align: center; padding: 48px 40px; background: #fff; border-radius: 20px; 
                   border: 1px solid #f1f5f9; max-width: 420px; }
            .icon { font-size: 56px; margin-bottom: 16px; }
            h2 { color: #1e293b; font-size: 22px; margin: 0 0 12px; }
            p { color: #64748b; font-size: 14px; line-height: 1.7; margin: 0 0 24px; }
            a { display: inline-block; background: #10b27c; color: #fff; padding: 12px 28px; 
                border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; }
        </style></head><body>
        <div class='box'>
            <div class='icon'>🔒</div>
            <h2>Cetak Belum Tersedia</h2>
            <p>Bukti pendaftaran hanya dapat dicetak setelah status Anda dinyatakan <strong>Diterima</strong> oleh panitia PPDB. Mohon tunggu pengumuman resmi.</p>
            <a href='dashboard.php'>Kembali ke Dashboard</a>
        </div>
        </body></html>
    ");
}


$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);


$foto_path = __DIR__ . '/../uploads/' . $data['foto'];
$foto_base64 = '';
if (file_exists($foto_path) && is_file($foto_path)) {
    $foto_data = file_get_contents($foto_path);
    $foto_base64 = 'data:image/jpeg;base64,' . base64_encode($foto_data);
} else {
    $foto_base64 = 'https://ui-avatars.com/api/?name=' . urlencode($data['nama_lengkap']) . '&background=EBF4FF&color=4A90E2&size=150';
}

$logo_path = __DIR__ . '/../assets/img/logo.png';
$logo_base64 = '';
if (file_exists($logo_path) && is_file($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Tanda Peserta PPDB</title>
    <style>
        @page { margin: 30px; }
        body { font-family: "Times New Roman", Times, serif; font-size: 13px; color: #000; line-height: 1.3; }
        
        .container { width: 100%; border: 2px solid #000; padding: 5px; box-sizing: border-box; }
        
        /* HEADER */
        .header { border: 2px solid #000; padding: 15px; text-align: center; margin-bottom: 5px; position: relative; }
        .header-logo { position: absolute; left: 15px; top: 10px; width: 60px; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { margin: 5px 0 0 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        
        /* MAIN CONTENT */
        .main-content { border: 2px solid #000; margin-bottom: 5px; display: table; width: 100%; }
        
        .photo-box { display: table-cell; width: 150px; padding: 10px; border-right: 2px solid #000; vertical-align: top; }
        .photo-area { width: 130px; height: 170px; border: 1px solid #000; padding: 3px; }
        .photo-area img { width: 100%; height: 100%; object-fit: cover; }
        
        .data-box { display: table-cell; padding: 10px 15px; vertical-align: top; }
        .data-label { font-size: 11px; margin-bottom: 2px; }
        .data-value { font-size: 14px; font-weight: bold; margin-bottom: 8px; }
        .data-value.large { font-size: 16px; }
        
        /* PILIHAN */
        .pilihan-title { font-weight: bold; font-size: 14px; margin: 5px 0; }
        .pilihan-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .pilihan-table th, .pilihan-table td { border: 2px solid #000; padding: 8px 10px; text-align: left; vertical-align: top; }
        .pilihan-table th { font-size: 11px; text-transform: uppercase; background-color: #fff; }
        .pilihan-table td { font-size: 11px; }
        .pilihan-list { margin: 0; padding-left: 15px; }
        .pilihan-list li { margin-bottom: 3px; }
        
        /* PERNYATAAN */
        .pernyataan-box { border: 2px solid #000; padding: 15px; }
        .pernyataan-title { font-weight: bold; font-size: 16px; margin: 0 0 10px 0; }
        .pernyataan-text { text-align: justify; font-size: 13px; margin-bottom: 40px; line-height: 1.5; }
        
        /* TTD */
        .ttd-area { width: 100%; height: 100px; position: relative; }
        .ttd-box { width: 250px; position: absolute; right: 0; top: 0; text-align: center; }
        .ttd-line { border-bottom: 1px solid #000; margin: 60px 0 5px 0; }
        .ttd-name { font-size: 13px; }
        
        /* FOOTER INFO */
        .footer-info { clear: both; color: #888; font-size: 10px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- HEADER -->
        <div class="header">
            <img src="' . $logo_base64 . '" class="header-logo" alt="Logo">
            <h1>KARTU TANDA PESERTA</h1>
            <h2>PPDB ' . htmlspecialchars($data['tahun_ajaran'] ?? '2026/2027') . '</h2>
        </div>
        
        <!-- MAIN CONTENT (FOTO & DATA) -->
        <div class="main-content">
            <div class="photo-box">
                <div class="photo-area">
                    <img src="' . $foto_base64 . '" alt="Foto Siswa">
                </div>
            </div>
            <div class="data-box">
                <div class="data-label">Nomor Pendaftaran</div>
                <div class="data-value large">' . str_pad($data['id'], 10, '0', STR_PAD_LEFT) . '</div>
                
                <div class="data-label">Nama Siswa</div>
                <div class="data-value large">' . strtoupper(htmlspecialchars($data['nama_lengkap'])) . '</div>
                
                <div class="data-label">NISN</div>
                <div class="data-value">' . htmlspecialchars($data['nisn']) . '</div>
                
                <div class="data-label">Sekolah Asal</div>
                <div class="data-value">' . strtoupper(htmlspecialchars($data['nama_sd'])) . '</div>
                
                <div class="data-label">Kabupaten / Kota Lahir</div>
                <div class="data-value">' . strtoupper(htmlspecialchars($data['tempat_lahir'])) . '</div>
                
                <div class="data-label">Jalur Pendaftaran</div>
                <div class="data-value">' . strtoupper(htmlspecialchars($data['jalur_pendaftaran'] ?? 'REGULER')) . '</div>
            </div>
        </div>
        
        <!-- JALUR / PILIHAN -->
        <h3 class="pilihan-title">Tujuan Pendaftaran & Status</h3>
        <table class="pilihan-table">
            <tr>
                <th style="width: 50%;">LEMBAGA TUJUAN</th>
                <th style="width: 50%;">STATUS PENDAFTARAN</th>
            </tr>
            <tr>
                <td>
                    <ul class="pilihan-list">
                        <li>MTs PP DDI AL-BARAKAH</li>
                        <li>Tingkat: Madrasah Tsanawiyah (SMP)</li>
                    </ul>
                </td>
                <td>
                    <ul class="pilihan-list">
                        <li>Status: <strong>' . strtoupper($data['status']) . '</strong></li>
                        <li>Diterima pada: ' . date('d-m-Y', strtotime($data['updated_at'] ?? $data['created_at'])) . '</li>
                    </ul>
                </td>
            </tr>
        </table>
        
        <!-- PERNYATAAN -->
        <div class="pernyataan-box">
            <h3 class="pernyataan-title">Pernyataan</h3>
            <div class="pernyataan-text">
                Saya menyatakan bahwa data yang saya isikan dalam formulir pendaftaran PPDB MTs PP DDI Al-Barakah adalah benar dan saya bersedia menerima ketentuan yang berlaku di Madrasah yang saya tuju. Saya bersedia menerima sanksi pembatalan penerimaan apabila melanggar pernyataan ini atau terbukti memalsukan dokumen persyaratan.
            </div>
            
            <div class="ttd-area">
                <div class="ttd-box">
                    <div>ttd.</div>
                    <div class="ttd-line"></div>
                    <div class="ttd-name">' . ucwords(strtolower(htmlspecialchars($data['nama_lengkap']))) . '</div>
                </div>
            </div>
            
            <div class="footer-info">
                Dicetak pada: ' . date('D, d M Y H:i:s O') . ' - IP: ' . $_SERVER['REMOTE_ADDR'] . '
            </div>
        </div>
        
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Bersihkan seluruh output layar yang bocor (spasi, warning PHP) sebelum mencetak raw PDF
ob_end_clean();

$dompdf->stream('Kartu_PPDB_' . $data['nisn'] . '.pdf', ['Attachment' => 0]);
exit;
?>
