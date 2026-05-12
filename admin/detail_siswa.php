<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users_siswa WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    die("Data siswa tidak ditemukan.");
}

$status = ucfirst($data['status'] ?: 'Pending');
$badgeClass = "bg-amber-100 text-amber-700";
if(strtolower($status) === 'diterima') $badgeClass = "bg-accent/10 text-accent";
if(strtolower($status) === 'ditolak') $badgeClass = "bg-red-100 text-red-700";
if(strtolower($status) === 'revisi') $badgeClass = "bg-orange-100 text-orange-700";


function renderRow($label, $value) {
    $val = htmlspecialchars($value ?? '-');
    if ($val === '') $val = '-';
    return "
    <div class='flex flex-col sm:flex-row py-3 border-b border-slate-100 last:border-0'>
        <div class='w-full sm:w-1/3 text-sm font-semibold text-slate-500'>{$label}</div>
        <div class='w-full sm:w-2/3 text-sm text-slate-800 font-medium'>{$val}</div>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Siswa | PPDB MTs Al-Barakah</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Lexend', 'sans-serif'] },
                    colors: { accent: '#10b27c', surface: '#f9fafa', panel: '#ffffff' },
                    borderRadius: { 'stitch': '12px' }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .print-border { border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans min-h-screen pb-12">

    <!-- Header -->
    <header class="bg-panel border-b border-slate-200 sticky top-0 z-40 shadow-sm no-print">
        <div class="max-w-5xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="verifikasi.php" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                    <i class="ph ph-arrow-left text-2xl text-slate-600"></i>
                </a>
                <h1 class="text-xl font-bold tracking-tight">Detail Lengkap Siswa</h1>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-stitch transition-colors flex items-center gap-2 text-sm">
                    <i class="ph ph-printer text-lg"></i> Cetak Profil
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-6 mt-8">
        
        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 mb-6 no-print">
            <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
            <i class="ph ph-caret-right text-[10px]"></i>
            <a href="verifikasi.php" class="hover:text-accent transition-colors">Pendaftar</a>
            <i class="ph ph-caret-right text-[10px]"></i>
            <span class="text-slate-900">Detail Siswa</span>
        </nav>
        
        <!-- Header Info -->
        <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm mb-8 print-border">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Foto -->
                <div class="w-32 h-40 shrink-0 border-4 border-white shadow-lg rounded-xl overflow-hidden bg-slate-100">
                    <?php if(!empty($data['foto'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($data['foto']) ?>" alt="Foto Siswa" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                            <i class="ph ph-user text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Info Utama -->
                <div class="flex-1 text-center md:text-left">
                    <div class="inline-block px-3 py-1 mb-3 text-xs font-bold rounded-full uppercase tracking-wider <?= $badgeClass ?>">
                        Status: <?= $status ?>
                    </div>
                    <h2 class="text-3xl font-extrabold tracking-tight mb-2"><?= htmlspecialchars($data['nama_lengkap'] ?: 'Nama Belum Diisi') ?></h2>
                    <p class="text-lg text-slate-500 font-medium mb-4">NISN: <span class="font-mono text-slate-800"><?= htmlspecialchars($data['nisn']) ?></span></p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-sm font-medium text-slate-600">
                        <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                            <i class="ph ph-calendar-blank text-accent"></i> Daftar: <?= date('d M Y', strtotime($data['tgl_buat'])) ?>
                        </div>
                        <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                            <i class="ph ph-gender-intersex text-accent"></i> <?= ($data['jenis_kelamin'] === 'L') ? 'Laki-Laki' : (($data['jenis_kelamin'] === 'P') ? 'Perempuan' : '-') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Kolom Kiri -->
            <div class="space-y-8">
                <!-- Biodata -->
                <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm print-border">
                    <h3 class="text-lg font-bold flex items-center gap-2 mb-6 text-slate-800"><i class="ph ph-identification-card text-accent"></i> Biodata Pribadi</h3>
                    <div class="space-y-1">
                        <?= renderRow('NIK', $data['nik']) ?>
                        <?= renderRow('Tempat, Tanggal Lahir', $data['tempat_lahir'] . ', ' . ($data['tanggal_lahir'] ? date('d F Y', strtotime($data['tanggal_lahir'])) : '-')) ?>
                        <?= renderRow('Anak Ke', $data['anak_ke'] ? $data['anak_ke'] . ' dari ' . $data['jumlah_saudara'] . ' bersaudara' : '-') ?>
                        <?= renderRow('Status dalam Keluarga', $data['status_keluarga']) ?>
                        <?= renderRow('Nomor HP/WA Siswa', $data['no_hp']) ?>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm print-border">
                    <h3 class="text-lg font-bold flex items-center gap-2 mb-6 text-slate-800"><i class="ph ph-map-pin text-accent"></i> Alamat Siswa</h3>
                    <div class="space-y-1">
                        <?= renderRow('Desa / Kelurahan', $data['desa']) ?>
                        <?= renderRow('Kecamatan', $data['kecamatan']) ?>
                        <?= renderRow('Kabupaten', $data['kabupaten']) ?>
                        <?= renderRow('Provinsi', $data['provinsi']) ?>
                    </div>
                </div>

                <!-- Pendidikan Sebelumnya -->
                <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm print-border">
                    <h3 class="text-lg font-bold flex items-center gap-2 mb-6 text-slate-800"><i class="ph ph-graduation-cap text-accent"></i> Pendidikan & Minat</h3>
                    <div class="space-y-1">
                        <?= renderRow('Asal Sekolah (SD/MI)', $data['nama_sd']) ?>
                        <?= renderRow('Alamat Sekolah', $data['alamat_sd']) ?>
                        <?= renderRow('Ekstrakurikuler Pilihan', $data['ekstrakurikuler']) ?>
                        <?= renderRow('Prestasi', $data['prestasi']) ?>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="space-y-8">
                <!-- Orang Tua -->
                <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm print-border">
                    <h3 class="text-lg font-bold flex items-center gap-2 mb-6 text-slate-800"><i class="ph ph-users text-accent"></i> Data Orang Tua</h3>
                    <div class="space-y-1">
                        <?= renderRow('Nama Ayah', $data['nama_ayah']) ?>
                        <?= renderRow('Pekerjaan Ayah', $data['pekerjaan_ayah']) ?>
                        <?= renderRow('Nama Ibu', $data['nama_ibu']) ?>
                        <?= renderRow('Pekerjaan Ibu', $data['pekerjaan_ibu']) ?>
                        <?= renderRow('No HP/WA Orang Tua', $data['hp_ortu']) ?>
                    </div>
                </div>

                <!-- Wali -->
                <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm print-border">
                    <h3 class="text-lg font-bold flex items-center gap-2 mb-6 text-slate-800"><i class="ph ph-user-focus text-accent"></i> Data Wali (Opsional)</h3>
                    <div class="space-y-1">
                        <?= renderRow('Nama Wali', $data['nama_wali']) ?>
                        <?= renderRow('Pekerjaan Wali', $data['pekerjaan_wali']) ?>
                        <?= renderRow('Alamat Wali', $data['alamat_wali']) ?>
                    </div>
                </div>

                <!-- Dokumen -->
                <div class="bg-panel p-8 rounded-[24px] border border-slate-200 shadow-sm no-print">
                    <h3 class="text-lg font-bold flex items-center gap-2 mb-6 text-slate-800"><i class="ph ph-files text-accent"></i> Dokumen Terlampir</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <?php
                        $docs = [
                            'kk' => ['icon' => 'users-three', 'label' => 'Kartu Keluarga'],
                            'ijazah' => ['icon' => 'certificate', 'label' => 'Ijazah / SKL'],
                            'akte' => ['icon' => 'file-text', 'label' => 'Akte Kelahiran'],
                            'kip' => ['icon' => 'card', 'label' => 'KIP / KKS']
                        ];
                        
                        foreach($docs as $key => $doc):
                            $val = $data[$key];
                        ?>
                        <div class="p-4 border <?= $val ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-slate-50 opacity-50' ?> rounded-xl flex flex-col items-center justify-center text-center gap-2 transition-all">
                            <i class="ph ph-<?= $doc['icon'] ?> text-3xl <?= $val ? 'text-emerald-500' : 'text-slate-400' ?>"></i>
                            <span class="text-xs font-bold <?= $val ? 'text-emerald-700' : 'text-slate-500' ?>"><?= $doc['label'] ?></span>
                            <?php if($val): ?>
                                <a href="../uploads/<?= htmlspecialchars($val) ?>" target="_blank" class="mt-1 px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded text-xs font-medium">Buka File</a>
                            <?php else: ?>
                                <span class="text-[10px] text-slate-400">Tidak ada file</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>
</html>
