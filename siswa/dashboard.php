<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header('Location: ../auth/login.php');
    exit;
}

$success = "";
$error = "";
$user_id = $_SESSION['user_id'];


if (isset($_POST['simpan'])) {
    csrf_check(); 

    $nama        = trim($_POST['nama'] ?? '');
    $nik         = trim($_POST['nik'] ?? '');
    $jk          = trim($_POST['jenis_kelamin'] ?? '');
    $tempat      = trim($_POST['tempat_lahir'] ?? '');
    $tgl         = trim($_POST['tgl_lahir'] ?? '');
    
    
    $desa        = trim($_POST['desa'] ?? '');
    $kecamatan   = trim($_POST['kecamatan'] ?? '');
    $kabupaten   = trim($_POST['kabupaten'] ?? '');
    $provinsi    = trim($_POST['provinsi'] ?? '');
    
    
    $nama_ayah   = trim($_POST['nama_ayah'] ?? '');
    $pek_ayah    = trim($_POST['pekerjaan_ayah'] ?? '');
    $nama_ibu    = trim($_POST['nama_ibu'] ?? '');
    $pek_ibu     = trim($_POST['pekerjaan_ibu'] ?? '');
    $hp_ortu     = trim($_POST['hp_ortu'] ?? '');
    
    $nama_wali   = trim($_POST['nama_wali'] ?? '');
    $pek_wali    = trim($_POST['pekerjaan_wali'] ?? '');
    $alamat_wali = trim($_POST['alamat_wali'] ?? '');

    
    $anak_ke         = (int)($_POST['anak_ke'] ?? 0);
    $jumlah_saudara  = (int)($_POST['jumlah_saudara'] ?? 0);
    $status_keluarga = trim($_POST['status_keluarga'] ?? '');
    $no_hp           = trim($_POST['no_hp'] ?? '');
    $nama_sd         = trim($_POST['nama_sd'] ?? '');
    $alamat_sd       = trim($_POST['alamat_sd'] ?? '');
    $ekstrakurikuler = trim($_POST['ekstrakurikuler'] ?? '');
    $prestasi        = trim($_POST['prestasi'] ?? '');

    $target_dir = "../uploads/";

    
    $stmt_existing = $pdo->prepare("SELECT foto, kk, ijazah, akte, kip FROM users_siswa WHERE id = ?");
    $stmt_existing->execute([$user_id]);
    $existing = $stmt_existing->fetch();

    $foto   = $existing['foto'];
    $kk     = $existing['kk'];
    $ijazah = $existing['ijazah'];
    $akte   = $existing['akte'];
    $kip    = $existing['kip'];

    
    $handleUpload = function($fileKey) use ($target_dir) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
            $val = validate_upload($_FILES[$fileKey]);
            if ($val['valid']) {
                $nama_file = time() . '_' . bin2hex(random_bytes(4)) . '.' . $val['ext'];
                if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $target_dir . $nama_file)) {
                    return $nama_file;
                }
            }
        }
        return null;
    };

    $new_foto   = $handleUpload('foto');   if ($new_foto) $foto = $new_foto;
    $new_kk     = $handleUpload('kk');     if ($new_kk) $kk = $new_kk;
    $new_ijazah = $handleUpload('ijazah'); if ($new_ijazah) $ijazah = $new_ijazah;
    $new_akte   = $handleUpload('akte');   if ($new_akte) $akte = $new_akte;
    $new_kip    = $handleUpload('kip');    if ($new_kip) $kip = $new_kip;

    
    $sql = "UPDATE users_siswa SET 
            nama_lengkap=?, nik=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?,
            desa=?, kecamatan=?, kabupaten=?, provinsi=?,
            nama_ayah=?, pekerjaan_ayah=?, nama_ibu=?, pekerjaan_ibu=?, hp_ortu=?,
            nama_wali=?, pekerjaan_wali=?, alamat_wali=?,
            anak_ke=?, jumlah_saudara=?, status_keluarga=?, no_hp=?, nama_sd=?, alamat_sd=?, ekstrakurikuler=?, prestasi=?,
            foto=?, kk=?, ijazah=?, akte=?, kip=?
            WHERE id=?";
            
    $stmt_update = $pdo->prepare($sql);
    $exec = $stmt_update->execute([
        $nama, $nik, $jk, $tempat, $tgl,
        $desa, $kecamatan, $kabupaten, $provinsi,
        $nama_ayah, $pek_ayah, $nama_ibu, $pek_ibu, $hp_ortu,
        $nama_wali, $pek_wali, $alamat_wali,
        $anak_ke, $jumlah_saudara, $status_keluarga, $no_hp, $nama_sd, $alamat_sd, $ekstrakurikuler, $prestasi,
        $foto, $kk, $ijazah, $akte, $kip,
        $user_id
    ]);

    if ($exec) {
        $success = "Profil dan Berkas Anda berhasil diperbarui!";
    } else {
        $error = "Terjadi kesalahan saat menyimpan data.";
    }
}


if (isset($_POST['ganti_password'])) {
    csrf_check(); 

    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    $stmt_pass = $pdo->prepare("SELECT password FROM users_siswa WHERE id = ?");
    $stmt_pass->execute([$user_id]);
    $current_hash = $stmt_pass->fetchColumn();

    if (!password_verify($old_pass, $current_hash)) {
        $error = "Password saat ini tidak cocok.";
    } elseif ($new_pass !== $conf_pass) {
        $error = "Konfirmasi password baru tidak cocok.";
    } elseif (strlen($new_pass) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } else {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt_update_pass = $pdo->prepare("UPDATE users_siswa SET password = ? WHERE id = ?");
        if ($stmt_update_pass->execute([$new_hash, $user_id])) {
            $success = "Password berhasil diubah!";
        } else {
            $error = "Gagal mengubah password.";
        }
    }
}


$stmt_siswa = $pdo->prepare("SELECT * FROM users_siswa WHERE id = ?");
$stmt_siswa->execute([$user_id]);
$data_siswa = $stmt_siswa->fetch();


function getPpdbSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM ppdb_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $r = $stmt->fetch();
    return $r ? $r['setting_value'] : '';
}

$jadwal_buka     = getPpdbSetting($pdo, 'jadwal_buka');
$jadwal_tutup    = getPpdbSetting($pdo, 'jadwal_tutup');
$info_berkas     = getPpdbSetting($pdo, 'info_berkas');
$info_pengumuman = getPpdbSetting($pdo, 'info_pengumuman');

$today   = date('Y-m-d');
$is_open = (!empty($jadwal_buka) && !empty($jadwal_tutup))
           ? ($today >= $jadwal_buka && $today <= $jadwal_tutup)
           : true;


$kelengkapan = 20; 
if(!empty($data_siswa['nik'])) $kelengkapan += 10;
if(!empty($data_siswa['desa'])) $kelengkapan += 15;
if(!empty($data_siswa['nama_ayah'])) $kelengkapan += 15;
if(!empty($data_siswa['nama_sd'])) $kelengkapan += 10;
if(!empty($data_siswa['foto']) && !empty($data_siswa['kk'])) $kelengkapan += 30;

$status = $data_siswa['status'] ?: 'pending';
$badgeClass = "bg-amber-100 text-amber-700";
if($status === 'diterima') $badgeClass = "bg-accent/10 text-accent";
if($status === 'ditolak') $badgeClass = "bg-red-100 text-red-700";
if($status === 'revisi') $badgeClass = "bg-orange-100 text-orange-700";

$statusText = ucfirst($status);


$pengumuman_title = "Pendaftaran Sedang Diproses";
$pengumuman_desc  = $info_pengumuman ?: "Mohon lengkapi data Anda di tab 'Kelengkapan Berkas' jika belum 100%.";
if($status === 'diterima') {
    $pengumuman_title = "Selamat! Anda Diterima";
    $pengumuman_desc = "Anda resmi diterima sebagai calon siswa baru. Silakan cetak bukti pendaftaran PDF.";
} elseif($status === 'ditolak') {
    $pengumuman_title = "Mohon Maaf, Anda Ditolak";
    $pengumuman_desc = "Berkas Anda tidak memenuhi syarat teknis PPDB kami.";
} elseif($status === 'revisi') {
    $pengumuman_title = "Berkas Perlu Direvisi";
    $pesan_revisi = $data_siswa['pesan_revisi'] ?? '';
    $pengumuman_desc = $pesan_revisi ?: "Panitia mengembalikan berkas Anda. Mohon unggah ulang dokumen yang lebih jelas di tab Kelengkapan Berkas.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Siswa | PPDB MTs Al-Barakah</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{accent:'#10b27c',accentDark:'#0d9466',surface:'#f8faf9',panel:'#ffffff'},borderRadius:{stitch:'14px'}}}}</script>
    <style>
    *{-webkit-tap-highlight-color:transparent}
    ::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}::-webkit-scrollbar-thumb:hover{background:#94a3b8}
    .tab-content{display:none;opacity:0;transition:opacity .3s ease}.tab-content.active{display:block;opacity:1}
    .input-field{width:100%;padding:.875rem 1rem;border:2px solid #e2e8f0;border-radius:14px;transition:all .3s;outline:none;background:#f8fafc;font-weight:500}
    .input-field:focus{border-color:#10b27c;box-shadow:0 0 0 4px rgba(16,178,124,.08);background:#fff}
    .label-text{display:block;font-size:.8125rem;font-weight:600;color:#475569;margin-bottom:.375rem}
    .card{background:#fff;border-radius:20px;border:1px solid #f1f5f9;transition:all .35s cubic-bezier(.16,1,.3,1)}
    .card:hover{box-shadow:0 8px 24px -8px rgba(0,0,0,.06)}
    @keyframes fadeIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    .anim-in{animation:fadeIn .5s cubic-bezier(.16,1,.3,1) both}
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased h-screen flex overflow-hidden">

    <!-- OVERLAY MOBILE -->
    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-[280px] bg-white border-r border-slate-100 flex flex-col justify-between transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 z-50 shrink-0">
        <div>
            <div class="h-[72px] flex items-center justify-between px-6 border-b border-slate-100">
                <a href="../index.php" class="flex items-center gap-2.5">
                    <img src="../assets/img/logo.png" alt="Logo" class="w-9 h-9 object-contain">
                    <div class="leading-tight"><span class="font-bold text-sm block">Portal Siswa</span><span class="text-[10px] text-slate-400">MTs Al-Barakah</span></div>
                </a>
                <button class="md:hidden text-slate-400 hover:text-slate-700 p-2" onclick="toggleSidebar()"><i class="ph ph-x text-xl"></i></button>
            </div>
            <nav class="p-4 space-y-1">
                <button onclick="switchTab('overview')" id="nav-overview" class="w-full flex items-center gap-3 px-4 py-3 bg-accent/10 text-accent font-semibold rounded-xl transition-all text-left text-sm">
                    <i class="ph ph-squares-four text-lg"></i> Ringkasan
                </button>
                <button onclick="switchTab('form')" id="nav-form" class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-xl transition-all text-left text-sm">
                    <i class="ph ph-folder-user text-lg"></i> Kelengkapan Berkas
                    <?php if($kelengkapan < 100): ?>
                    <span class="w-2 h-2 rounded-full bg-red-500 ml-auto animate-pulse"></span>
                    <?php endif; ?>
                </button>
                <button onclick="switchTab('password')" id="nav-password" class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-xl transition-all text-left text-sm">
                    <i class="ph ph-lock-key text-lg"></i> Ganti Password
                </button>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-100 space-y-1">
            <a href="../index.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 font-medium rounded-xl transition-all text-sm"><i class="ph ph-house text-lg"></i> Beranda</a>
            <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 font-medium rounded-xl transition-all text-sm"><i class="ph ph-sign-out text-lg"></i> Keluar</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- TOPBAR -->
        <header class="h-[72px] bg-white border-b border-slate-100 flex items-center justify-between px-5 md:px-8 shrink-0 z-10">
            <div class="flex items-center gap-3">
                <button class="md:hidden text-slate-500 hover:text-slate-800 p-2" onclick="toggleSidebar()"><i class="ph ph-list text-xl"></i></button>
                <div>
                    <h2 class="text-base md:text-lg font-bold tracking-tight">Halo, <?= htmlspecialchars($data_siswa['nama_lengkap']) ?> 👋</h2>
                    <p class="text-xs text-slate-400">NISN: <?= htmlspecialchars($data_siswa['nisn'] ?? '') ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-xs font-bold rounded-full <?= $badgeClass ?>"><?= $statusText ?></span>
            </div>
        </header>

        <!-- SCROLLABLE CONTENT AREA -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <div class="max-w-6xl mx-auto pb-10">
                
                <?php if($success): ?>
                <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl border border-emerald-100 mb-6 flex items-center gap-3 anim-in">
                    <i class="ph ph-check-circle text-xl"></i> <?= $success ?>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl border border-red-100 mb-6 flex items-center gap-3 anim-in">
                    <i class="ph ph-warning-circle text-xl"></i> <?= $error ?>
                </div>
                <?php endif; ?>

                <!-- ================= TAB 1: OVERVIEW ================= -->
                <div id="tab-overview" class="tab-content active space-y-6">
                    
                    <!-- STATUS BAR -->
                    <div class="card p-6 md:p-7 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 anim-in">
                        <div>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Status Pendaftaran</h3>
                            <div class="flex items-center gap-3">
                                <span class="px-4 py-1.5 text-sm font-bold rounded-full uppercase tracking-wider <?= $badgeClass ?>">
                                    <?= $statusText ?>
                                </span>
                                <?php if($status === 'diterima'): ?>
                                <span class="text-accent flex items-center gap-1 text-sm font-medium"><i class="ph ph-check-circle-fill"></i> Terverifikasi Resmi</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="w-full md:w-1/3">
                            <div class="flex justify-between text-sm font-semibold mb-2">
                                <span class="text-slate-500">Persentase Berkas</span>
                                <span class="text-accent"><?= $kelengkapan ?>%</span>
                            </div>
                            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-accent transition-all duration-1000" style="width: <?= $kelengkapan ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <?php if($status === 'revisi' && !empty($data_siswa['pesan_revisi'])): ?>
                    <!-- PESAN REVISI DARI ADMIN -->
                    <div class="bg-orange-50 rounded-2xl border-2 border-orange-200 p-6 md:p-7 anim-in">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-2xl shrink-0">
                                <i class="ph ph-note-pencil"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-orange-800 mb-1">Catatan Revisi dari Panitia</h3>
                                <p class="text-sm text-orange-600 mb-4">Silakan perbaiki berkas sesuai catatan di bawah, lalu upload ulang di tab <strong>Kelengkapan Berkas</strong>.</p>
                                <div class="bg-white rounded-2xl border border-orange-200 p-5">
                                    <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($data_siswa['pesan_revisi']) ?></p>
                                </div>
                                <button onclick="switchTab('form')" class="mt-4 inline-flex items-center gap-2 bg-orange-500 text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-orange-600 transition-colors shadow-lg shadow-orange-200">
                                    <i class="ph ph-pencil-line"></i> Perbaiki Berkas Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- PENGUMUMAN -->
                        <div class="lg:col-span-2">
                            <div class="card overflow-hidden h-full flex flex-col">
                                <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-2xl shrink-0"><i class="ph ph-megaphone"></i></div>
                                    <div>
                                        <h3 class="text-lg font-bold tracking-tight text-slate-900"><?= $pengumuman_title ?></h3>
                                        <p class="text-slate-500 text-sm mt-1"><?= $pengumuman_desc ?></p>
                                    </div>
                                </div>
                                <div class="bg-slate-50 p-6 md:p-8 flex-1">
                                    <h4 class="text-sm font-semibold text-slate-700 mb-4">Tugas Anda Selanjutnya:</h4>
                                    <ul class="space-y-4">
                                        <?php if($kelengkapan < 100): ?>
                                        <li class="flex items-start gap-3 p-4 bg-white rounded-stitch border border-amber-100 shadow-sm cursor-pointer hover:border-amber-300 transition-colors" onclick="switchTab('form')">
                                            <i class="ph ph-warning-circle text-amber-500 text-2xl mt-0.5 shrink-0"></i>
                                            <div>
                                                <p class="font-bold text-slate-800">Lengkapi Profil & Berkas</p>
                                                <p class="text-sm text-slate-500 mt-1">Data Anda belum mencapai 100%. Silakan klik di sini untuk mengunggah KK, Ijazah, dan Foto.</p>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <li class="flex items-start gap-3 p-4 bg-white rounded-stitch border border-slate-100 shadow-sm">
                                            <i class="ph ph-printer text-accent text-2xl mt-0.5 shrink-0"></i>
                                            <div class="w-full">
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                    <div>
                                                        <p class="font-bold text-slate-800">Cetak Bukti Pendaftaran</p>
                                                        <p class="text-sm text-slate-500 mt-1">Gunakan berkas ini saat pendaftaran ulang di sekolah.</p>
                                                    </div>
                                                    <a href="cetak_kartu.php" target="_blank" class="<?= $kelengkapan < 100 ? 'opacity-50 pointer-events-none' : '' ?> shrink-0 bg-accent/10 text-accent hover:bg-accent hover:text-white px-4 py-2 rounded-lg font-semibold text-sm transition-colors text-center inline-block">
                                                        <i class="ph ph-download-simple mr-1"></i> Cetak PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- INFO WIDGETS -->
                        <div class="space-y-6">
                            <div class="bg-slate-900 rounded-2xl p-6 text-white relative overflow-hidden group">
                                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-[0.05] rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                                <h4 class="text-slate-400 text-sm font-bold tracking-wider mb-1">Nomor Peserta</h4>
                                <p class="text-3xl font-bold font-mono tracking-tight"><?= str_pad($data_siswa['id'], 5, '0', STR_PAD_LEFT) ?></p>
                            </div>
                            
                            <div class="card p-6">
                                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><i class="ph ph-note-pencil text-accent"></i> Syarat Berkas</h4>
                                <p class="text-sm text-slate-500 leading-relaxed mb-4"><?= nl2br(htmlspecialchars($info_berkas ?: 'Siapkan: Fotocopy KK, Ijazah, Pas Foto 3x4.')) ?></p>
                                <a href="https://wa.me/6281234567890" target="_blank" class="w-full flex justify-center items-center gap-2 border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold py-2.5 rounded-xl transition-colors text-sm">
                                    <i class="ph ph-whatsapp-logo text-green-500 text-lg"></i> Hubungi Panitia
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 2: LENGKAPI DATA FORM ================= -->
                <div id="tab-form" class="tab-content">
                    <div class="card overflow-hidden">
                        
                        <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50">
                            <h2 class="text-xl font-bold text-slate-900">Formulir Identitas Siswa Baru</h2>
                            <p class="text-sm text-slate-500 mt-1">Pastikan data yang diinputkan sesuai dengan dokumen resmi (KK / Ijazah).</p>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="p-6 md:p-8" id="formSiswa" onsubmit="return handleFormSubmit(this)">
                            <?= csrf_field() ?>

                            <?php if (!$is_open): ?>
                            <div class="mb-8 p-5 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-4">
                                <i class="ph ph-lock-simple text-red-500 text-3xl shrink-0"></i>
                                <div>
                                    <h4 class="font-bold text-red-700">Formulir Pendaftaran Ditutup</h4>
                                    <p class="text-sm text-red-500 mt-1">Masa pendaftaran telah berakhir atau belum dibuka. Hubungi panitia untuk informasi lebih lanjut.</p>
                                </div>
                            </div>
                            <fieldset disabled class="opacity-60 pointer-events-none">
                            <?php else: ?>
                            <fieldset>
                            
                            <!-- DATA PRIBADI -->
                            <div class="mb-10">
                                <h3 class="text-sm font-bold text-accent uppercase tracking-wider mb-6 flex items-center gap-2">
                                    <i class="ph ph-user"></i> 1. Data Pribadi
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="label-text">Nama Lengkap (Sesuai Ijazah)</label>
                                        <input type="text" name="nama" class="input-field" value="<?= htmlspecialchars($data_siswa['nama_lengkap'] ?? '') ?>" required>
                                    </div>
                                    <div>
                                        <label class="label-text">Nomor Induk Kependudukan (NIK)</label>
                                        <input type="text" name="nik" class="input-field" value="<?= htmlspecialchars($data_siswa['nik'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="input-field">
                                            <option value="" <?= empty($data_siswa['jenis_kelamin']) ? 'selected' : '' ?>>Pilih...</option>
                                            <option value="L" <?= ($data_siswa['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-Laki</option>
                                            <option value="P" <?= ($data_siswa['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="input-field" value="<?= htmlspecialchars($data_siswa['tempat_lahir'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Tanggal Lahir</label>
                                        <input type="date" name="tgl_lahir" class="input-field" value="<?= htmlspecialchars($data_siswa['tanggal_lahir'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Agama</label>
                                        <input type="text" name="agama" class="input-field" value="Islam" disabled>
                                    </div>
                                    <div>
                                        <label class="label-text">Anak Ke-</label>
                                        <input type="number" name="anak_ke" class="input-field" value="<?= htmlspecialchars($data_siswa['anak_ke'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Jumlah Saudara</label>
                                        <input type="number" name="jumlah_saudara" class="input-field" value="<?= htmlspecialchars($data_siswa['jumlah_saudara'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Status Dalam Keluarga</label>
                                        <select name="status_keluarga" class="input-field">
                                            <option value="">Pilih...</option>
                                            <option value="Anak Kandung" <?= ($data_siswa['status_keluarga'] ?? '') == 'Anak Kandung' ? 'selected' : '' ?>>Anak Kandung</option>
                                            <option value="Anak Tiri" <?= ($data_siswa['status_keluarga'] ?? '') == 'Anak Tiri' ? 'selected' : '' ?>>Anak Tiri</option>
                                            <option value="Anak Angkat" <?= ($data_siswa['status_keluarga'] ?? '') == 'Anak Angkat' ? 'selected' : '' ?>>Anak Angkat</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">No. HP Siswa Aktif</label>
                                        <input type="text" name="no_hp" class="input-field" value="<?= htmlspecialchars($data_siswa['no_hp'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100 mb-10">

                            <!-- DATA ALAMAT -->
                            <div class="mb-10">
                                <h3 class="text-sm font-bold text-accent uppercase tracking-wider mb-6 flex items-center gap-2">
                                    <i class="ph ph-map-pin"></i> 2. Data Alamat Siswa
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="label-text">Desa / Kelurahan</label>
                                        <input type="text" name="desa" class="input-field" value="<?= htmlspecialchars($data_siswa['desa'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="input-field" value="<?= htmlspecialchars($data_siswa['kecamatan'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Kabupaten / Kota</label>
                                        <input type="text" name="kabupaten" class="input-field" value="<?= htmlspecialchars($data_siswa['kabupaten'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Provinsi</label>
                                        <input type="text" name="provinsi" class="input-field" value="<?= htmlspecialchars($data_siswa['provinsi'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100 mb-10">

                            <!-- DATA KELUARGA -->
                            <div class="mb-10">
                                <h3 class="text-sm font-bold text-accent uppercase tracking-wider mb-6 flex items-center gap-2">
                                    <i class="ph ph-users"></i> 2. Informasi Orang Tua
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="label-text">Nama Lengkap Ayah</label>
                                        <input type="text" name="nama_ayah" class="input-field" value="<?= htmlspecialchars($data_siswa['nama_ayah'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Pekerjaan Ayah</label>
                                        <input type="text" name="pekerjaan_ayah" class="input-field" value="<?= htmlspecialchars($data_siswa['pekerjaan_ayah'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Nama Lengkap Ibu</label>
                                        <input type="text" name="nama_ibu" class="input-field" value="<?= htmlspecialchars($data_siswa['nama_ibu'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="label-text">Pekerjaan Ibu</label>
                                        <input type="text" name="pekerjaan_ibu" class="input-field" value="<?= htmlspecialchars($data_siswa['pekerjaan_ibu'] ?? '') ?>">
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="label-text">Nomor WhatsApp Aktif Orang Tua</label>
                                        <input type="text" name="hp_ortu" class="input-field" value="<?= htmlspecialchars($data_siswa['hp_ortu'] ?? '') ?>">
                                    </div>
                                    
                                    <!-- DATA WALI -->
                                    <div class="col-span-1 md:col-span-2 mt-4 p-4 border border-slate-200 bg-white rounded-xl">
                                        <p class="text-xs text-slate-500 mb-3"><i class="ph ph-info"></i> Isi bagian ini jika Anda tinggal dengan Wali (bukan Orang Tua kandung)</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <div>
                                                <label class="label-text">Nama Lengkap Wali</label>
                                                <input type="text" name="nama_wali" class="input-field" value="<?= htmlspecialchars($data_siswa['nama_wali'] ?? '') ?>">
                                            </div>
                                            <div>
                                                <label class="label-text">Pekerjaan Wali</label>
                                                <input type="text" name="pekerjaan_wali" class="input-field" value="<?= htmlspecialchars($data_siswa['pekerjaan_wali'] ?? '') ?>">
                                            </div>
                                            <div class="col-span-1 md:col-span-2">
                                                <label class="label-text">Alamat Lengkap Wali</label>
                                                <input type="text" name="alamat_wali" class="input-field" value="<?= htmlspecialchars($data_siswa['alamat_wali'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-slate-100 mb-10">

                            <!-- DATA SEKOLAH & BERKAS -->
                            <div class="mb-10">
                                <h3 class="text-sm font-bold text-accent uppercase tracking-wider mb-6 flex items-center gap-2">
                                    <i class="ph ph-graduation-cap"></i> 3. Sekolah Asal & Upload Dokumen
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="label-text">Nama SD/MI Asal</label>
                                        <input type="text" name="nama_sd" class="input-field" value="<?= htmlspecialchars($data_siswa['nama_sd'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="label-text">Alamat SD/MI Asal</label>
                                        <input type="text" name="alamat_sd" class="input-field" value="<?= htmlspecialchars($data_siswa['alamat_sd'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="label-text">Ekstrakurikuler yang Diminati</label>
                                            <input type="text" name="ekstrakurikuler" class="input-field" value="<?= htmlspecialchars($data_siswa['ekstrakurikuler'] ?? '') ?>" placeholder="Misal: Pramuka, PMR">
                                        </div>
                                        <div>
                                            <label class="label-text">Prestasi Pernah Diraih</label>
                                            <input type="text" name="prestasi" class="input-field" value="<?= htmlspecialchars($data_siswa['prestasi'] ?? '') ?>" placeholder="Misal: Juara 1 Lomba Puisi Tingkat Kab">
                                        </div>
                                    </div>
                                    
                                    <div class="p-5 border border-slate-200 bg-slate-50 rounded-stitch mt-4">
                                        <label class="label-text font-bold text-slate-800"><i class="ph ph-image text-accent mr-1"></i> Pas Foto 3x4 (Maks 2MB)</label>
                                        <?php if(!empty($data_siswa['foto'])): ?>
                                            <p class="text-xs text-emerald-600 font-semibold mb-2">✓ Sudah diunggah (<?= $data_siswa['foto'] ?>)</p>
                                        <?php endif; ?>
                                        <input type="file" name="foto" accept="image/jpeg,image/png" <?= empty($data_siswa['foto']) ? 'required' : '' ?> class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all cursor-pointer bg-white border border-slate-200 rounded-lg p-1">
                                    </div>

                                    <div class="p-5 border border-slate-200 bg-slate-50 rounded-stitch mt-4">
                                        <label class="label-text font-bold text-slate-800"><i class="ph ph-file-text text-accent mr-1"></i> Scan Kartu Keluarga (Maks 2MB)</label>
                                        <?php if(!empty($data_siswa['kk'])): ?>
                                            <p class="text-xs text-emerald-600 font-semibold mb-2">✓ Sudah diunggah (<?= $data_siswa['kk'] ?>)</p>
                                        <?php endif; ?>
                                        <input type="file" name="kk" accept="image/jpeg,image/png,application/pdf" <?= empty($data_siswa['kk']) ? 'required' : '' ?> class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all cursor-pointer bg-white border border-slate-200 rounded-lg p-1">
                                    </div>

                                    <div class="col-span-1 md:col-span-2 p-5 border border-slate-200 bg-slate-50 rounded-stitch">
                                        <label class="label-text font-bold text-slate-800"><i class="ph ph-certificate text-accent mr-1"></i> Scan Ijazah / SKL (Maks 2MB)</label>
                                        <?php if(!empty($data_siswa['ijazah'])): ?>
                                            <p class="text-xs text-emerald-600 font-semibold mb-2">✓ Sudah diunggah (<?= $data_siswa['ijazah'] ?>)</p>
                                        <?php endif; ?>
                                        <input type="file" name="ijazah" accept="image/jpeg,image/png,application/pdf" <?= empty($data_siswa['ijazah']) ? 'required' : '' ?> class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all cursor-pointer bg-white border border-slate-200 rounded-lg p-1">
                                    </div>
                                    
                                    <div class="p-5 border border-slate-200 bg-slate-50 rounded-stitch">
                                        <label class="label-text font-bold text-slate-800"><i class="ph ph-file text-accent mr-1"></i> Scan Akta Kelahiran (Maks 2MB)</label>
                                        <?php if(!empty($data_siswa['akte'])): ?>
                                            <p class="text-xs text-emerald-600 font-semibold mb-2">✓ Sudah diunggah (<?= $data_siswa['akte'] ?>)</p>
                                        <?php endif; ?>
                                        <input type="file" name="akte" accept="image/jpeg,image/png,application/pdf" class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all cursor-pointer bg-white border border-slate-200 rounded-lg p-1">
                                        <p class="text-xs text-slate-400 mt-2">*Opsional</p>
                                    </div>
                                    
                                    <div class="p-5 border border-slate-200 bg-slate-50 rounded-stitch">
                                        <label class="label-text font-bold text-slate-800"><i class="ph ph-card text-accent mr-1"></i> Scan KIP / KKS (Maks 2MB)</label>
                                        <?php if(!empty($data_siswa['kip'])): ?>
                                            <p class="text-xs text-emerald-600 font-semibold mb-2">✓ Sudah diunggah (<?= $data_siswa['kip'] ?>)</p>
                                        <?php endif; ?>
                                        <input type="file" name="kip" accept="image/jpeg,image/png,application/pdf" class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 transition-all cursor-pointer bg-white border border-slate-200 rounded-lg p-1">
                                        <p class="text-xs text-slate-400 mt-2">*Opsional, jika ada</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-slate-100 flex justify-end">
                                <button type="submit" name="simpan" id="btnSimpan" class="bg-accent hover:bg-[#0d9466] text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-accent/20 transition-all flex items-center gap-2">
                                    <i class="ph ph-floppy-disk text-xl" id="btnIcon"></i> <span id="btnText">Simpan Data Permanen</span>
                                </button>
                            </div>
                            </fieldset>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>

                <!-- TAB: GANTI PASSWORD -->
                <div id="tab-password" class="tab-content">
                    <div class="card p-6 md:p-10 mb-8 border-t-4 border-t-accent shadow-sm">
                        <div class="mb-8">
                            <h3 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2"><i class="ph ph-lock-key text-accent text-2xl"></i> Ganti Password</h3>
                            <p class="text-slate-500 mt-1 text-sm font-medium">Perbarui password akun Anda secara berkala untuk menjaga keamanan.</p>
                        </div>
                        
                        <form action="" method="POST" class="max-w-xl">
                            <?= csrf_field() ?>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="label-text">Password Saat Ini</label>
                                    <div class="relative">
                                        <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                        <input type="password" name="old_password" required class="input-field pl-11" placeholder="Masukkan password saat ini">
                                    </div>
                                </div>
                                
                                <div class="pt-4 border-t border-slate-100">
                                    <label class="label-text">Password Baru</label>
                                    <div class="relative">
                                        <i class="ph ph-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                        <input type="password" name="new_password" required minlength="6" class="input-field pl-11" placeholder="Minimal 6 karakter">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="label-text">Konfirmasi Password Baru</label>
                                    <div class="relative">
                                        <i class="ph ph-check-circle absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                                        <input type="password" name="confirm_password" required minlength="6" class="input-field pl-11" placeholder="Ulangi password baru">
                                    </div>
                                </div>
                                
                                <div class="pt-4">
                                    <button type="submit" name="ganti_password" class="bg-slate-800 hover:bg-slate-900 text-white px-8 py-3.5 rounded-xl font-bold transition-all flex items-center gap-2">
                                        <i class="ph ph-arrows-clockwise text-xl"></i> Perbarui Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- SCRIPTS -->
    <script>
        function handleFormSubmit(form) {
            const btn = document.getElementById('btnSimpan');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            // Konfirmasi sebelum simpan
            if(!confirm('Apakah Anda yakin semua data sudah benar? Proses ini akan menyimpan data secara permanen.')) {
                return false;
            }
            
            // Loading state
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            icon.className = 'ph ph-spinner-gap text-xl animate-spin';
            text.textContent = 'Menyimpan...';
            
            return true;
        }

        // Toggle Sidebar Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Tab Switching Logic
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            // Show target tab
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Reset nav styling
            document.getElementById('nav-overview').className = "w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-xl transition-all text-left text-sm";
            document.getElementById('nav-form').className = "w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-xl transition-all text-left text-sm";
            document.getElementById('nav-password').className = "w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-xl transition-all text-left text-sm";
            
            // Activate selected nav styling
            document.getElementById('nav-' + tabName).className = "w-full flex items-center gap-3 px-4 py-3 bg-accent/10 text-accent font-semibold rounded-xl transition-all text-left text-sm";

            // If on mobile, close sidebar after clicking
            if (window.innerWidth < 768) {
                toggleSidebar();
            }
            
            // Smooth scroll to top
            document.querySelector('.flex-1.overflow-y-auto').scrollTo({top: 0, behavior: 'smooth'});
        }

        // Check if there's a success/error message, auto switch to form tab to show it clearly if they were submitting
        <?php if(isset($_POST['simpan'])): ?>
            switchTab('form');
        <?php elseif(isset($_POST['ganti_password'])): ?>
            switchTab('password');
        <?php endif; ?>
    </script>
</body>
</html>