<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit;
}
if (($_SESSION['role_admin'] ?? '') !== 'superadmin') {
    header("Location: dashboard.php"); exit;
}

$success = "";
$error   = "";




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}

if (isset($_POST['save_jadwal'])) {
    $buka  = $_POST['jadwal_buka'];
    $tutup = $_POST['jadwal_tutup'];
    $pengumuman = $_POST['jadwal_pengumuman'] ?? null;
    $stmt1 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='jadwal_buka'");
    $stmt1->execute([$buka]);
    $stmt2 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='jadwal_tutup'");
    $stmt2->execute([$tutup]);
    if ($pengumuman) {
        $stmt3 = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('jadwal_pengumuman', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        $stmt3->execute([$pengumuman]);
    }
    $success = "Jadwal berhasil diperbarui.";
}

if (isset($_POST['save_info'])) {
    $info_berkas     = $_POST['info_berkas'];
    $info_pengumuman = $_POST['info_pengumuman'];
    $stmt1 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='info_berkas'");
    $stmt1->execute([$info_berkas]);
    $stmt2 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='info_pengumuman'");
    $stmt2->execute([$info_pengumuman]);
    $success = "Informasi berhasil diperbarui.";
}


if (isset($_POST['save_banner'])) {
    $banner = $_POST['banner_teks'];
    $stmt = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('banner_teks', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $stmt->execute([$banner]);
    $success = "Teks banner berhasil diperbarui.";
}

if (isset($_POST['save_persyaratan'])) {
    $items = [];
    $icons  = $_POST['syarat_icon']  ?? [];
    $juduls = $_POST['syarat_judul'] ?? [];
    $descs  = $_POST['syarat_desc']  ?? [];
    foreach ($juduls as $i => $judul) {
        if (trim($judul) === '') continue;
        $items[] = [
            'icon'  => strip_tags($icons[$i]  ?? 'ph-file'),
            'judul' => strip_tags($judul),
            'desc'  => strip_tags($descs[$i] ?? ''),
        ];
    }
    $json = json_encode($items, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('persyaratan_json', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $stmt->execute([$json]);
    $success = "Persyaratan berhasil diperbarui.";
}

if (isset($_POST['save_jadwal_publik'])) {
    $tahaps   = [];
    $tanggals = $_POST['tahap_tanggal'] ?? [];
    $namas    = $_POST['tahap_nama']    ?? [];
    $descs    = $_POST['tahap_desc']    ?? [];
    $styles   = $_POST['tahap_style']   ?? [];
    foreach ($namas as $i => $nama) {
        if (trim($nama) === '') continue;
        $tahaps[] = [
            'tanggal' => strip_tags($tanggals[$i] ?? ''),
            'nama'    => strip_tags($nama),
            'desc'    => strip_tags($descs[$i]    ?? ''),
            'style'   => in_array($styles[$i], ['normal','accent']) ? $styles[$i] : 'normal',
        ];
    }
    $json = json_encode($tahaps, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('jadwal_json', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $stmt->execute([$json]);
    $success = "Jadwal halaman depan berhasil diperbarui.";
}




function getSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM ppdb_settings WHERE setting_key=?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: '';
}

$jadwal_buka      = getSetting($pdo, 'jadwal_buka');
$jadwal_tutup     = getSetting($pdo, 'jadwal_tutup');
$jadwal_pengumuman= getSetting($pdo, 'jadwal_pengumuman');
$info_berkas      = getSetting($pdo, 'info_berkas');
$info_pengumuman  = getSetting($pdo, 'info_pengumuman');


$banner_teks     = getSetting($pdo, 'banner_teks') ?: 'Penerimaan Tahun 2026/2027 Dibuka';
$persyaratan_raw = getSetting($pdo, 'persyaratan_json');
$jadwal_pub_raw  = getSetting($pdo, 'jadwal_json');

$persyaratan_edit = $persyaratan_raw ? json_decode($persyaratan_raw, true) : [
    ['icon' => 'ph-certificate', 'judul' => 'Surat Keterangan Lulus (SKL) / Ijazah', 'desc' => 'Scan dokumen asli atau fotokopi yang telah dilegalisir dari sekolah dasar/sederajat asal. Diunggah dalam format PDF.'],
    ['icon' => 'ph-users-three', 'judul' => 'Kartu Keluarga (KK)',                   'desc' => 'Scan Kartu Keluarga asli terbaru. Pastikan Nomor Induk Kependudukan (NIK) siswa dan nama orang tua tercantum dengan jelas.'],
    ['icon' => 'ph-user-focus',  'judul' => 'Pas Foto Terbaru',                       'desc' => 'Foto setengah badan dengan pakaian formal (seragam asal), latar belakang warna merah atau biru. Format yang diterima adalah gambar (JPG/PNG).'],
];

$jadwal_pub_edit = $jadwal_pub_raw ? json_decode($jadwal_pub_raw, true) : [
    ['tanggal' => '1 Mei - 30 Juni', 'nama' => 'Pendaftaran Daring',   'desc' => 'Pembuatan akun, pengisian data biodata, dan pengunggahan berkas persyaratan melalui portal ini.',                   'style' => 'normal'],
    ['tanggal' => '5 Juli',          'nama' => 'Pengumuman Kelulusan', 'desc' => 'Hasil verifikasi berkas dan pengumuman diterima akan diperbarui secara real-time pada dashboard siswa.',         'style' => 'accent'],
    ['tanggal' => '6 - 10 Juli',     'nama' => 'Daftar Ulang',         'desc' => 'Proses pendaftaran ulang dengan menyerahkan berkas fisik ke madrasah bagi peserta didik yang dinyatakan lulus.','style' => 'normal'],
];

$today = date('Y-m-d');
$is_open = ($today >= $jadwal_buka && $today <= $jadwal_tutup);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan PPDB | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Lexend', 'sans-serif'] },
                colors: { accent: '#10b27c', surface: '#f9fafa', panel: '#ffffff' },
                borderRadius: { 'stitch': '12px' }
            }}
        }
    </script>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans flex h-screen overflow-hidden">

    <!-- OVERLAY MOBILE -->
    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-72 bg-panel border-r border-slate-200 flex flex-col justify-between transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 z-50 shrink-0">
        <div>
            <div class="h-20 flex items-center justify-between px-8 border-b border-slate-100">
                <div class="flex items-center">
                    <img src="../assets/img/logo.png" alt="Logo" class="w-10 h-10 object-contain mr-3">
                    <h1 class="text-xl font-bold tracking-tight">Admin PPDB</h1>
                </div>
                <button class="md:hidden text-slate-400 hover:text-slate-700" onclick="toggleSidebar()">
                    <i class="ph ph-x text-2xl"></i>
                </button>
            </div>
            <nav class="p-6 space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 font-medium rounded-stitch transition-colors">
                    <i class="ph ph-squares-four text-xl"></i> Dashboard
                </a>
                <a href="arsip.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 font-medium rounded-stitch transition-colors">
                    <i class="ph ph-archive text-xl"></i> Arsip Tahun Ajaran
                </a>
                <?php if(($_SESSION['role_admin'] ?? '') === 'superadmin'): ?>
                <a href="kelola_users.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 font-medium rounded-stitch transition-colors">
                    <i class="ph ph-users text-xl"></i> Kelola Panitia
                </a>
                <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 bg-accent/10 text-accent font-semibold rounded-stitch transition-colors">
                    <i class="ph ph-gear-six text-xl"></i> Pengaturan PPDB
                </a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="p-6 border-t border-slate-100 space-y-2">
            <a href="../index.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 font-medium rounded-stitch transition-colors">
                <i class="ph ph-house text-xl"></i> Halaman Depan
            </a>
            <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 font-medium rounded-stitch transition-colors">
                <i class="ph ph-sign-out text-xl"></i> Keluar Sistem
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-20 bg-panel border-b border-slate-200 flex items-center justify-between px-6 md:px-8 shrink-0">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-slate-500" onclick="toggleSidebar()"><i class="ph ph-list text-2xl"></i></button>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Pengaturan PPDB</h2>
                    <p class="text-xs text-slate-400 font-medium">Hak akses: Superadmin</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1.5 text-xs font-bold rounded-full <?= $is_open ? 'bg-accent/10 text-accent' : 'bg-red-100 text-red-600' ?>">
                    <i class="ph ph-<?= $is_open ? 'check-circle' : 'x-circle' ?> mr-1"></i>
                    Pendaftaran <?= $is_open ? 'BUKA' : 'TUTUP' ?>
                </span>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-8">

            <!-- BREADCRUMB -->
            <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
                <i class="ph ph-caret-right text-[10px]"></i>
                <span class="text-slate-900">Pengaturan Sistem</span>
            </nav>

            <?php if ($success): ?>
            <div class="p-4 bg-accent/10 border border-accent/20 text-accent rounded-2xl font-medium flex items-center gap-3">
                <i class="ph ph-check-circle text-xl shrink-0"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <!-- ===== PANEL 1: JADWAL ===== -->
            <section class="bg-panel rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="ph ph-calendar-blank"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Jadwal Pendaftaran</h3>
                        <p class="text-sm text-slate-400">Atur kapan portal pendaftaran siswa buka dan tutup secara otomatis.</p>
                    </div>
                </div>
                <form method="POST" class="p-6 md:p-8">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Pembukaan</label>
                            <input type="date" name="jadwal_buka" value="<?= htmlspecialchars($jadwal_buka) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Penutupan</label>
                            <input type="date" name="jadwal_tutup" value="<?= htmlspecialchars($jadwal_tutup) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Waktu Pengumuman SNBT</label>
                            <input type="datetime-local" name="jadwal_pengumuman" value="<?= htmlspecialchars($jadwal_pengumuman) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium" required>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-slate-50 rounded-xl text-sm text-slate-500 flex items-start gap-3">
                        <i class="ph ph-info text-lg text-blue-400 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="mb-1">Jika hari ini berada di luar rentang jadwal buka-tutup, form pengisian di portal siswa akan otomatis dikunci.</p>
                            <p class="font-bold text-slate-700">Khusus H-1 (24 Jam sebelum Waktu Pengumuman SNBT), dashboard siswa akan terkunci dan berubah menjadi Layar Hitung Mundur Raksasa.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" name="save_jadwal" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </section>

            <!-- ===== PANEL 2: INFORMASI BERKAS ===== -->
            <section class="bg-panel rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="ph ph-megaphone"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Informasi & Pengumuman</h3>
                        <p class="text-sm text-slate-400">Teks ini tampil langsung di halaman dashboard siswa.</p>
                    </div>
                </div>
                <form method="POST" class="p-6 md:p-8 space-y-6">
                    <?= csrf_field() ?>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Teks Banner Pengumuman Utama</label>
                        <textarea name="info_pengumuman" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium resize-none"><?= htmlspecialchars($info_pengumuman) ?></textarea>
                        <p class="text-xs text-slate-400 mt-1">Tampil sebagai judul/deskripsi pengumuman di atas dashboard siswa.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Syarat & Informasi Berkas</label>
                        <textarea name="info_berkas" rows="4" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium resize-none"><?= htmlspecialchars($info_berkas) ?></textarea>
                        <p class="text-xs text-slate-400 mt-1">Tampil di panel bantuan / syarat dokumen dalam dashboard siswa.</p>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="save_info" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Simpan Informasi
                        </button>
                    </div>
                </form>
            </section>
            <!-- ===== PANEL 3: BANNER HERO ===== -->
            <section class="bg-panel rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="ph ph-sparkle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Teks Banner Halaman Depan</h3>
                        <p class="text-sm text-slate-400">Teks yang muncul di banner kecil paling atas hero section halaman publik.</p>
                    </div>
                </div>
                <form method="POST" class="p-6 md:p-8">
                    <?= csrf_field() ?>
                    <input type="text" name="banner_teks" value="<?= htmlspecialchars($banner_teks) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium" placeholder="Penerimaan Tahun 2026/2027 Dibuka">
                    <p class="text-xs text-slate-400 mt-2">Contoh: <em>Penerimaan Tahun 2026/2027 Dibuka</em> — tampil di badge atas hero.</p>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" name="save_banner" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Simpan Banner
                        </button>
                    </div>
                </form>
            </section>

            <!-- ===== PANEL 4: PERSYARATAN ===== -->
            <section class="bg-panel rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="ph ph-file-text"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Persyaratan Pendaftaran</h3>
                        <p class="text-sm text-slate-400">Atur daftar dokumen yang tampil di halaman depan. Tambah atau hapus baris sesuka hati.</p>
                    </div>
                </div>
                <form method="POST" class="p-6 md:p-8">
                    <?= csrf_field() ?>
                    <div id="syarat-list" class="space-y-4">
                        <?php foreach ($persyaratan_edit as $idx => $s): ?>
                        <div class="syarat-row grid grid-cols-1 md:grid-cols-[160px_1fr_2fr_auto] gap-3 items-start p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Ikon (Phosphor)</label>
                                <input type="text" name="syarat_icon[]" value="<?= htmlspecialchars($s['icon']) ?>" placeholder="ph-certificate" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Dokumen</label>
                                <input type="text" name="syarat_judul[]" value="<?= htmlspecialchars($s['judul']) ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</label>
                                <input type="text" name="syarat_desc[]" value="<?= htmlspecialchars($s['desc']) ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                            </div>
                            <div class="pt-5">
                                <button type="button" onclick="hapusRow(this)" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="tambahSyarat()" class="mt-4 flex items-center gap-2 text-sm font-semibold text-accent hover:text-accentDark transition-colors">
                        <i class="ph ph-plus-circle text-xl"></i> Tambah Dokumen
                    </button>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" name="save_persyaratan" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Simpan Persyaratan
                        </button>
                    </div>
                </form>
            </section>

            <!-- ===== PANEL 5: JADWAL PUBLIK ===== -->
            <section class="bg-panel rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center text-2xl shrink-0">
                        <i class="ph ph-list-numbers"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">Jadwal Tahapan Halaman Depan</h3>
                        <p class="text-sm text-slate-400">Atur timeline tahapan PPDB yang tampil di bagian Jadwal pada halaman publik.</p>
                    </div>
                </div>
                <form method="POST" class="p-6 md:p-8">
                    <?= csrf_field() ?>
                    <div id="jadwal-list" class="space-y-4">
                        <?php foreach ($jadwal_pub_edit as $idx => $t): ?>
                        <div class="tahap-row grid grid-cols-1 md:grid-cols-[160px_160px_1fr_120px_auto] gap-3 items-start p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                                <input type="text" name="tahap_tanggal[]" value="<?= htmlspecialchars($t['tanggal']) ?>" placeholder="1 Mei - 30 Juni" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Tahap</label>
                                <input type="text" name="tahap_nama[]" value="<?= htmlspecialchars($t['nama']) ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</label>
                                <input type="text" name="tahap_desc[]" value="<?= htmlspecialchars($t['desc']) ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tampilan</label>
                                <select name="tahap_style[]" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                                    <option value="normal" <?= $t['style'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                                    <option value="accent" <?= $t['style'] === 'accent' ? 'selected' : '' ?>>Highlight</option>
                                </select>
                            </div>
                            <div class="pt-5">
                                <button type="button" onclick="hapusRow(this)" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="ph ph-trash text-lg"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="tambahTahap()" class="mt-4 flex items-center gap-2 text-sm font-semibold text-accent hover:text-accentDark transition-colors">
                        <i class="ph ph-plus-circle text-xl"></i> Tambah Tahap
                    </button>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" name="save_jadwal_publik" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function hapusRow(btn) {
            btn.closest('.syarat-row, .tahap-row').remove();
        }

        function tambahSyarat() {
            const list = document.getElementById('syarat-list');
            const div  = document.createElement('div');
            div.className = 'syarat-row grid grid-cols-1 md:grid-cols-[160px_1fr_2fr_auto] gap-3 items-start p-4 bg-slate-50 rounded-xl border border-slate-200';
            div.innerHTML = `
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Ikon (Phosphor)</label>
                    <input type="text" name="syarat_icon[]" placeholder="ph-file" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Dokumen</label>
                    <input type="text" name="syarat_judul[]" placeholder="Nama dokumen..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</label>
                    <input type="text" name="syarat_desc[]" placeholder="Deskripsi singkat..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                </div>
                <div class="pt-5">
                    <button type="button" onclick="hapusRow(this)" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="ph ph-trash text-lg"></i>
                    </button>
                </div>`;
            list.appendChild(div);
        }

        function tambahTahap() {
            const list = document.getElementById('jadwal-list');
            const div  = document.createElement('div');
            div.className = 'tahap-row grid grid-cols-1 md:grid-cols-[160px_160px_1fr_120px_auto] gap-3 items-start p-4 bg-slate-50 rounded-xl border border-slate-200';
            div.innerHTML = `
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                    <input type="text" name="tahap_tanggal[]" placeholder="1 Mei - 30 Juni" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Tahap</label>
                    <input type="text" name="tahap_nama[]" placeholder="Pendaftaran Daring" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Deskripsi</label>
                    <input type="text" name="tahap_desc[]" placeholder="Deskripsi tahap..." class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tampilan</label>
                    <select name="tahap_style[]" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-accent">
                        <option value="normal">Normal</option>
                        <option value="accent">Highlight</option>
                    </select>
                </div>
                <div class="pt-5">
                    <button type="button" onclick="hapusRow(this)" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="ph ph-trash text-lg"></i>
                    </button>
                </div>`;
            list.appendChild(div);
        }
    </script>
</body>
</html>
