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

    // Gabungkan tanggal + jam pengumuman
    $tgl_pengumuman  = $_POST['tgl_pengumuman']  ?? '';
    $jam_hour        = str_pad($_POST['jam_hour'] ?? '08', 2, '0', STR_PAD_LEFT);
    $jam_minute      = str_pad($_POST['jam_minute'] ?? '00', 2, '0', STR_PAD_LEFT);
    $jam_pengumuman  = $jam_hour . ':' . $jam_minute;
    
    // VALIDASI LOGIKA WAKTU
    if (strtotime($tutup) < strtotime($buka)) {
        $error = "Gagal disimpan: Tanggal Penutupan tidak boleh lebih awal dari Tanggal Pembukaan!";
    } elseif ($tgl_pengumuman && strtotime($tgl_pengumuman) < strtotime($tutup)) {
        $error = "Gagal disimpan: Tanggal Pengumuman PPDB tidak boleh mendahului Tanggal Penutupan!";
    } else {
        $pengumuman = ($tgl_pengumuman) ? $tgl_pengumuman . 'T' . $jam_pengumuman : null;

        // Auto-generate tahun ajaran dari tahun tanggal buka (Gunakan tahun sekarang jika tanggal buka tidak valid)
        $timestamp_buka = strtotime($buka);
        $tahun_buka     = ($timestamp_buka) ? (int) date('Y', $timestamp_buka) : (int) date('Y');
        $tahun_ajaran   = $tahun_buka . '/' . ($tahun_buka + 1);

        $stmt1 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='jadwal_buka'");
        $stmt1->execute([$buka]);
        $stmt2 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='jadwal_tutup'");
        $stmt2->execute([$tutup]);

        if ($pengumuman) {
            $stmt3 = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('jadwal_pengumuman', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            $stmt3->execute([$pengumuman]);
        }

        $stmt4 = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('tahun_ajaran', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        $stmt4->execute([$tahun_ajaran]);

        $success = "Jadwal berhasil diperbarui. Tahun Ajaran otomatis: <strong>{$tahun_ajaran}</strong>";
    }
}

if (isset($_POST['save_info'])) {
    $info_berkas     = $_POST['info_berkas'];
    $info_pengumuman = $_POST['info_pengumuman'];
    
    // Filter WA: pastikan hanya tersisa angka, lalu sanitasi spasi dll
    $kontak_wa = preg_replace('/[^0-9]/', '', $_POST['kontak_wa'] ?? '');

    $stmt1 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='info_berkas'");
    $stmt1->execute([$info_berkas]);
    $stmt2 = $pdo->prepare("UPDATE ppdb_settings SET setting_value=? WHERE setting_key='info_pengumuman'");
    $stmt2->execute([$info_pengumuman]);
    
    $stmt3 = $pdo->prepare("INSERT INTO ppdb_settings (setting_key, setting_value) VALUES ('kontak_wa', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
    $stmt3->execute([$kontak_wa]);
    
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
$tahun_ajaran     = getSetting($pdo, 'tahun_ajaran') ?: (date('Y') . '/' . (date('Y') + 1));
$info_berkas      = getSetting($pdo, 'info_berkas');
$info_pengumuman  = getSetting($pdo, 'info_pengumuman');
$kontak_wa        = getSetting($pdo, 'kontak_wa') ?: '6281234567890';


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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                colors: { accent: '#10b27c', accentDark: '#0d9466', surface: '#f8faf9', panel: '#ffffff' },
                borderRadius: { 'stitch': '14px' }
            }}
        }
    </script>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans flex h-screen overflow-hidden">

    <!-- OVERLAY MOBILE -->
    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <?php $active_menu = 'pengaturan'; include 'layout/sidebar.php'; ?>

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

            <?php if ($error): ?>
            <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-2xl font-medium flex items-center gap-3">
                <i class="ph ph-warning-circle text-xl shrink-0"></i> <?= $error ?>
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
                <form method="POST" class="p-6 md:p-8" id="formJadwal">
                    <?= csrf_field() ?>

                    <!-- Preview Tahun Ajaran Otomatis -->
                    <div class="mb-6 flex items-center gap-3 p-4 bg-accent/5 border border-accent/20 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center shrink-0">
                            <i class="ph ph-graduation-cap text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahun Ajaran Aktif — Otomatis</p>
                            <p class="text-lg font-extrabold text-accent" id="previewTahunAjaran"><?= htmlspecialchars($tahun_ajaran) ?></p>
                        </div>
                        <div class="ml-auto text-xs text-slate-400 italic hidden md:block">Dihitung otomatis dari Tanggal Pembukaan</div>
                    </div>

                    <!-- Periode Pendaftaran -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Pembukaan</label>
                            <input type="date" name="jadwal_buka" id="inputJadwalBuka" value="<?= htmlspecialchars($jadwal_buka) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Penutupan</label>
                            <input type="date" name="jadwal_tutup" value="<?= htmlspecialchars($jadwal_tutup) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium">
                        </div>
                    </div>

                    <!-- Pengumuman PPDB -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Pengumuman PPDB</label>
                            <input type="date" name="tgl_pengumuman" value="<?= htmlspecialchars(substr($jadwal_pengumuman, 0, 10)) ?>" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jam Pengumuman PPDB</label>
                            <?php
                                $saved_time = substr($jadwal_pengumuman, 11, 5) ?: '08:00';
                                $parts = explode(':', $saved_time);
                                $saved_h = $parts[0] ?? '08';
                                $saved_m = $parts[1] ?? '00';
                            ?>
                            <div class="flex items-center gap-3">
                                <!-- Stepper Jam -->
                                <div class="flex-1 flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:border-accent focus-within:bg-white focus-within:ring-4 focus-within:ring-accent/10 transition-all h-[52px]">
                                    <button type="button" onclick="ubahWaktu('jam', -1)" class="w-12 h-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-800 transition-colors active:scale-95">
                                        <i class="ph ph-minus font-bold"></i>
                                    </button>
                                    <input type="text" name="jam_hour" id="input_jam" value="<?= $saved_h ?>" class="flex-1 w-full text-center font-extrabold text-slate-800 text-lg outline-none tabular-nums bg-transparent" maxlength="2" onchange="validasiWaktu(this, 23)">
                                    <button type="button" onclick="ubahWaktu('jam', 1)" class="w-12 h-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-800 transition-colors active:scale-95">
                                        <i class="ph ph-plus font-bold"></i>
                                    </button>
                                </div>
                                
                                <span class="text-2xl font-extrabold text-slate-300 pb-1 animate-pulse">:</span>
                                
                                <!-- Stepper Menit -->
                                <div class="flex-1 flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:border-accent focus-within:bg-white focus-within:ring-4 focus-within:ring-accent/10 transition-all h-[52px]">
                                    <button type="button" onclick="ubahWaktu('menit', -1)" class="w-12 h-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-800 transition-colors active:scale-95">
                                        <i class="ph ph-minus font-bold"></i>
                                    </button>
                                    <input type="text" name="jam_minute" id="input_menit" value="<?= $saved_m ?>" class="flex-1 w-full text-center font-extrabold text-slate-800 text-lg outline-none tabular-nums bg-transparent" maxlength="2" onchange="validasiWaktu(this, 59)">
                                    <button type="button" onclick="ubahWaktu('menit', 1)" class="w-12 h-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-slate-800 transition-colors active:scale-95">
                                        <i class="ph ph-plus font-bold"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-xl text-sm text-slate-500 flex items-start gap-3">
                        <i class="ph ph-info text-lg text-blue-400 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="mb-1">Jika hari ini berada di luar rentang jadwal buka-tutup, form pengisian di portal siswa akan otomatis dikunci.</p>
                            <p class="font-bold text-slate-700">Khusus H-1 (24 Jam sebelum Waktu Pengumuman PPDB), dashboard siswa akan terkunci dan berubah menjadi Layar Hitung Mundur Raksasa.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" name="save_jadwal" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-all flex items-center gap-2 active:scale-[0.98]">
                            <i class="ph ph-floppy-disk"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>

                <script>
                    // Live preview: update Tahun Ajaran badge saat tanggal buka diganti
                    const inputBuka = document.getElementById('inputJadwalBuka');
                    const previewTA = document.getElementById('previewTahunAjaran');
                    if (inputBuka && previewTA) {
                        inputBuka.addEventListener('change', () => {
                            const tahun = new Date(inputBuka.value).getFullYear();
                            if (!isNaN(tahun)) {
                                previewTA.textContent = tahun + '/' + (tahun + 1);
                            }
                        });
                    }
                </script>
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
                        <textarea name="info_berkas" rows="4" class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium resize-none mb-1"><?= htmlspecialchars($info_berkas) ?></textarea>
                        <p class="text-xs text-slate-400 mb-4">Tampil di panel bantuan / syarat dokumen dalam dashboard siswa.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nomor WhatsApp Panitia</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                                <i class="ph ph-whatsapp-logo text-xl"></i>
                            </div>
                            <input type="text" name="kontak_wa" value="<?= htmlspecialchars($kontak_wa) ?>" class="w-full border border-slate-200 rounded-xl pl-11 pr-4 py-3 outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all font-medium" placeholder="628xxx (Gunakan awalan 62 tanpa +)">
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Nomor ini terhubung dengan semua tombol "Hubungi Panitia" di area publik & siswa.</p>
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
    function ubahWaktu(tipe, arah) {
        const el = document.getElementById('input_' + tipe);
        let val = parseInt(el.value) || 0;
        const max = tipe === 'jam' ? 23 : 59;
        
        // Loncat 5 menit untuk kecepatan, jam tetap loncat 1
        const step = tipe === 'menit' ? 5 : 1; 
        
        val += (arah * step);
        
        if (val > max) val = (tipe === 'menit') ? val - 60 : 0;
        if (val < 0) val = (tipe === 'menit') ? 60 + val : max;
        
        el.value = val.toString().padStart(2, '0');
    }

    function validasiWaktu(el, max) {
        let val = parseInt(el.value) || 0;
        if (val > max) val = max;
        if (val < 0) val = 0;
        el.value = val.toString().padStart(2, '0');
    }

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
