<?php
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


$foto_path = '../uploads/' . $data['foto'];
$foto_base64 = '';
if (file_exists($foto_path)) {
    $foto_data = file_get_contents($foto_path);
    $foto_base64 = 'data:image/jpeg;base64,' . base64_encode($foto_data);
} else {
    
    $foto_base64 = 'https://ui-avatars.com/api/?name=' . urlencode($data['nama_lengkap']) . '&background=EBF4FF&color=4A90E2&size=150';
}

$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Pendaftaran PPDB</title>
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; color: #333; }
        .container { width: 100%; border: 2px solid #10b27c; padding: 0; margin: 0 auto; }
        .header { background: #10b27c; color: #fff; text-align: center; padding: 20px; }
        .header h2 { margin: 0 0 5px 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; }
        .content { padding: 20px; }
        .photo-area { float: right; width: 120px; height: 160px; border: 1px solid #ccc; text-align: center; }
        .photo-area img { width: 100%; height: 100%; object-fit: cover; }
        .data-area { width: 75%; float: left; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; vertical-align: top; }
        .label { font-weight: bold; width: 180px; }
        .separator { width: 20px; text-align: center; }
        .footer { clear: both; margin-top: 40px; text-align: center; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; background: #e6f7f2; color: #10b27c; border: 1px solid #10b27c; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>KARTU BUKTI PENDAFTARAN</h2>
            <p>PANITIA PENERIMAAN PESERTA DIDIK BARU (PPDB)</p>
            <p>MTs PONDOK PESANTREN DDI AL-BARAKAH</p>
        </div>
        <div class="content">
            <div class="photo-area">
                <img src="' . $foto_base64 . '" alt="Foto Siswa">
            </div>
            <div class="data-area">
                <table>
                    <tr>
                        <td class="label">Nomor Registrasi</td>
                        <td class="separator">:</td>
                        <td><strong>REG-' . str_pad($data['id'], 5, '0', STR_PAD_LEFT) . '</strong></td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td>
                        <td class="separator">:</td>
                        <td>' . htmlspecialchars($data['nisn']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Nama Lengkap</td>
                        <td class="separator">:</td>
                        <td>' . htmlspecialchars($data['nama_lengkap']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Tempat, Tanggal Lahir</td>
                        <td class="separator">:</td>
                        <td>' . htmlspecialchars($data['tempat_lahir']) . ', ' . date('d F Y', strtotime($data['tanggal_lahir'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Kelamin</td>
                        <td class="separator">:</td>
                        <td>' . ($data['jenis_kelamin'] == 'L' ? 'Laki-Laki' : 'Perempuan') . '</td>
                    </tr>
                    <tr>
                        <td class="label">Asal Sekolah</td>
                        <td class="separator">:</td>
                        <td>' . htmlspecialchars($data['nama_sd']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Nama Orang Tua/Wali</td>
                        <td class="separator">:</td>
                        <td>' . htmlspecialchars($data['nama_ayah']) . '</td>
                    </tr>
                </table>
                <div class="status-badge">
                    STATUS: ' . strtoupper($data['status'] ? $data['status'] : 'DALAM PROSES VERIFIKASI') . '
                </div>
            </div>
        </div>
        <div class="footer">
            <p>Bawa kartu ini saat melakukan daftar ulang atau ujian seleksi di sekolah.</p>
            <p>Dicetak pada: ' . date('d M Y H:i:s') . '</p>
        </div>
    </div>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();


$dompdf->stream('Kartu_PPDB_' . $data['nisn'] . '.pdf', ['Attachment' => 0]);

?>
