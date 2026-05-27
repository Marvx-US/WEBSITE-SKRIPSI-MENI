<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}


$stats = [
    'total'   => $pdo->query("SELECT COUNT(*) FROM users_siswa")->fetchColumn(),
    'lulus'   => $pdo->query("SELECT COUNT(*) FROM users_siswa WHERE status='diterima'")->fetchColumn(),
    'ditolak' => $pdo->query("SELECT COUNT(*) FROM users_siswa WHERE status='ditolak'")->fetchColumn(),
    'proses'  => $pdo->query("SELECT COUNT(*) FROM users_siswa WHERE status='pending' OR status IS NULL OR status=''")->fetchColumn(),
    'revisi'  => $pdo->query("SELECT COUNT(*) FROM users_siswa WHERE status='revisi'")->fetchColumn(),
];


$query_trend = $pdo->query("SELECT DATE(tgl_buat) as tgl, COUNT(*) as c FROM users_siswa WHERE tgl_buat >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(tgl_buat) ORDER BY tgl ASC");
$label_trend_chart = [];
$data_trend_chart = [];
while($r = $query_trend->fetch()) {
    $label_trend_chart[] = date('d/m', strtotime($r['tgl']));
    $data_trend_chart[] = $r['c'];
}


$query_gender = $pdo->query("SELECT COALESCE(NULLIF(jenis_kelamin,''), 'Belum Diisi') as jk, COUNT(*) as c FROM users_siswa GROUP BY jk");
$label_gender_chart = [];
$data_gender_chart = [];
while($r = $query_gender->fetch()) {
    $val = $r['jk'];
    if($val === 'L') $val = 'Laki-Laki';
    if($val === 'P') $val = 'Perempuan';
    $label_gender_chart[] = $val;
    $data_gender_chart[] = $r['c'];
}


$query_siswa = $pdo->query("SELECT * FROM users_siswa ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Verifikasi | PPDB MTs Al-Barakah</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <?= csrf_meta() ?>
    
    <!-- Chart.js -->
    

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: { accent: '#10b27c', accentDark: '#0d9466', surface: '#f8faf9', panel: '#ffffff' },
                    borderRadius: { 'stitch': '14px' }
                }
            }
        }
    </script>
    
    <style>
        html { scroll-behavior: smooth; }
        * { -webkit-tap-highlight-color: transparent; }
        
        /* Glassmorphism Utilities */
        .glass { background: rgba(255,255,255,0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Smooth UI Tweaks */
        .card-lift { transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1); }
        .card-lift:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(16,178,124, 0.15); }
        
        /* Form Field Flushed */
        .form-flush { border: none; border-bottom: 1px solid #e2e8f0; border-radius: 0; padding-left: 0; padding-right: 0; background: transparent; transition: border-color 0.4s ease; }
        .form-flush:focus { outline: none; border-bottom-color: #10b27c; box-shadow: 0 1px 0 0 #10b27c; }
        
        /* Drag handle for mobile modal */
        .drag-handle { width: 40px; height: 5px; background: #cbd5e1; border-radius: 3px; margin: 10px auto; opacity: 0.6; }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans flex h-screen overflow-hidden">

    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden md:hidden transition-opacity duration-500 opacity-0" onclick="toggleSidebar()"></div>

    <?php $active_menu = 'verifikasi'; include 'layout/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <header class="h-20 bg-panel border-b border-slate-100 flex items-center justify-between px-6 md:px-8 shrink-0 z-10 relative">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-slate-500 hover:text-slate-800" onclick="toggleSidebar()">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h2 class="text-xl font-extrabold tracking-tight">Command Center</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-full text-sm font-bold border border-slate-100">
                    <i class="ph ph-user-circle text-lg text-accent"></i>
                    <span><?= htmlspecialchars($_SESSION['nama']) ?> <span class="text-slate-400 font-medium">(<?= ucfirst($_SESSION['role_admin'] ?? 'Verifikator') ?>)</span></span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            
            <nav class="flex items-center gap-2 text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
                <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
                <i class="ph ph-caret-right text-[10px]"></i>
                <span class="text-slate-900">Manajemen Verifikasi</span>
            </nav>
            
            <!-- DATA SISWA -->
            <div class="bg-panel rounded-[24px] border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-5 md:p-8 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-lg md:text-xl font-extrabold tracking-tight text-slate-800">Manajemen Verifikasi</h3>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <!-- Vanilla Custom Search -->
                        <div class="relative w-full sm:w-64">
                            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="text" id="customSearch" placeholder="Cari nama atau NISN..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl pl-10 pr-4 py-2.5 outline-none focus:bg-white focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all">
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full sm:w-auto relative">
                            <select id="filterStatus" class="w-full sm:w-auto appearance-none bg-slate-50 border border-slate-200 text-sm font-medium rounded-xl pl-4 pr-10 py-2.5 outline-none focus:bg-white focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all cursor-pointer text-slate-600">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="diterima">Diterima</option>
                                <option value="ditolak">Ditolak</option>
                                <option value="revisi">Revisi</option>
                            </select>
                            <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>

                        <!-- Batch Action -->
                        <div class="w-full sm:w-auto flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1 hidden" id="batchActionContainer">
                            <select id="batchStatus" class="bg-transparent text-sm font-medium outline-none px-2 py-1.5 cursor-pointer text-slate-600">
                                <option value="">Aksi Massal...</option>
                                <option value="diterima">Luluskan Terpilih</option>
                                <option value="ditolak">Tolak Terpilih</option>
                            </select>
                            <button onclick="executeBatchAction()" class="bg-slate-900 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors">Terapkan</button>
                        </div>

                        <a href="export_excel.php" class="bg-accent/10 text-accent px-5 py-2.5 rounded-xl text-sm font-bold flex items-center justify-center gap-2 hover:bg-accent hover:text-white transition-all w-full sm:w-auto">
                            <i class="ph ph-download-simple text-lg"></i> <span class="hidden sm:inline">Export</span>
                        </a>
                    </div>
                </div>

                <!-- Responsive Table Wrapper -->
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-sm text-left" id="siswaTable">
                        <thead class="text-xs text-slate-400 uppercase tracking-wider bg-slate-50/50 hidden lg:table-header-group">
                            <tr>
                                <th class="px-6 py-4 font-bold w-12 text-center"><input type="checkbox" id="selectAll" class="w-4 h-4 accent-accent rounded border-slate-300"></th>
                                <th class="px-6 py-4 font-bold">Identitas Pendaftar</th>
                                <th class="px-6 py-4 font-bold">Asal Sekolah</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                                <th class="px-6 py-4 font-bold text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 flex flex-col lg:table-row-group" id="siswaTableBody">
                            <?php 
                            if ($query_siswa->rowCount() > 0):
                                while($row = $query_siswa->fetch()): 
                                    $status = strtolower($row['status'] ?: 'pending');
                                    $statusText = ucfirst($status);
                                    
                                    $badgeClass = "bg-amber-50 text-amber-600 border border-amber-200/50";
                                    $iconStatus = "ph-hourglass-medium";
                                    if($status === 'diterima') { $badgeClass = "bg-accent/10 text-accent border border-accent/20"; $iconStatus = "ph-check-circle"; }
                                    if($status === 'ditolak') { $badgeClass = "bg-red-50 text-red-600 border border-red-200/50"; $iconStatus = "ph-x-circle"; }
                                    if($status === 'revisi') { $badgeClass = "bg-orange-50 text-orange-600 border border-orange-200/50"; $iconStatus = "ph-arrow-counter-clockwise"; }
                            ?>
                            <tr class="item-row flex flex-col lg:table-row bg-white hover:bg-slate-50/50 transition-colors p-4 lg:p-0" data-nama="<?= strtolower(htmlspecialchars($row['nama_lengkap'])) ?>" data-nisn="<?= htmlspecialchars($row['nisn']) ?>" data-status="<?= $status ?>" id="row-<?= $row['id'] ?>">
                                <!-- Checkbox -->
                                <td class="px-2 lg:px-6 py-2 lg:py-5 lg:text-center flex justify-between lg:table-cell items-center mb-2 lg:mb-0">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase lg:hidden">Pilih</span>
                                    <input type="checkbox" class="row-checkbox w-5 h-5 lg:w-4 lg:h-4 accent-accent rounded border-slate-300" value="<?= $row['id'] ?>">
                                </td>
                                <!-- Identitas -->
                                <td class="px-2 lg:px-6 py-2 lg:py-5 lg:table-cell">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-[12px] bg-slate-100 flex items-center justify-center text-slate-400 shrink-0 font-bold hidden sm:flex">
                                            <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-800 text-base lg:text-sm"><?= htmlspecialchars($row['nama_lengkap']) ?></p>
                                            <p class="text-slate-500 text-[11px] lg:text-xs font-mono mt-0.5">NISN: <?= htmlspecialchars($row['nisn']) ?> &bull; <?= htmlspecialchars($row['jenis_kelamin'] ?? '-') == 'L' ? 'L' : (($row['jenis_kelamin'] ?? '-') == 'P' ? 'P' : '-') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <!-- Asal Sekolah -->
                                <td class="px-2 lg:px-6 py-2 lg:py-5 lg:table-cell flex flex-col">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase lg:hidden mb-1">Asal Sekolah</span>
                                    <span class="text-slate-600 font-medium text-sm lg:text-sm"><?= htmlspecialchars($row['nama_sd'] ?? '-') ?></span>
                                </td>
                                <!-- Status -->
                                <td class="px-2 lg:px-6 py-2 lg:py-5 lg:text-center flex justify-between items-center lg:table-cell">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase lg:hidden">Status</span>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] lg:text-xs font-bold tracking-wide <?= $badgeClass ?> status-badge">
                                        <i class="ph <?= $iconStatus ?> text-sm"></i> <span class="status-text"><?= $statusText ?></span>
                                    </div>
                                </td>
                                <!-- Aksi -->
                                <td class="px-2 lg:px-6 py-3 lg:py-5 lg:text-right mt-2 border-t border-slate-100 lg:border-0 lg:mt-0 flex justify-end lg:table-cell">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="resetPasswordSiswa(<?= $row['id'] ?>)" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 flex items-center justify-center transition-colors tooltip" title="Reset Password (123456)">
                                            <i class="ph ph-key font-bold"></i>
                                        </button>
                                        <button onclick='bukaModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)' class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-slate-900/10 flex items-center gap-2">
                                            <i class="ph ph-magnifying-glass font-bold"></i> Periksa
                                        </button>
                                        <a href="detail_siswa.php?id=<?= $row['id'] ?>" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-accent/10 hover:text-accent text-slate-500 flex items-center justify-center transition-colors">
                                            <i class="ph ph-arrow-up-right font-bold"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; 
                            else: ?>
                            <tr class="block lg:table-row"><td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada data pendaftar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- SPA MODAL VERIFIKASI (BOTTOM SHEET MOBILE / SIDE-PANEL DESKTOP) -->
    <div id="verifikasiModal" class="fixed inset-0 z-[60] flex justify-end items-end md:items-start opacity-0 pointer-events-none transition-opacity duration-500">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md" onclick="tutupModal()"></div>
        
        <!-- Modal Content -->
        <div id="modalPanel" class="relative w-full md:w-[90vw] md:max-w-7xl h-[90vh] md:h-full bg-white md:shadow-2xl translate-y-full md:translate-y-0 md:translate-x-full transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] flex flex-col rounded-t-[32px] md:rounded-none overflow-hidden">
            
            <!-- Drag Handle (Mobile Only) -->
            <div class="md:hidden w-full h-8 flex justify-center items-center shrink-0" onclick="tutupModal()">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
            </div>

            <header class="h-16 md:h-20 px-6 md:px-8 flex items-center justify-between border-b border-slate-100 shrink-0">
                <div>
                    <h3 class="text-lg md:text-xl font-extrabold tracking-tight text-slate-800">Pemeriksaan Berkas</h3>
                    <p class="text-[10px] md:text-xs text-slate-500 font-mono mt-0.5" id="modalNisn">NISN: -</p>
                </div>
                <button onclick="tutupModal()" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors">
                    <i class="ph ph-x text-base md:text-lg font-bold"></i>
                </button>
            </header>

            <!-- SIDE BY SIDE CONTAINER -->
            <div class="flex-1 overflow-hidden flex flex-col lg:flex-row">
                
                <!-- KOLOM KIRI: DATA TEKS -->
                <div class="w-full lg:w-1/3 h-1/2 lg:h-full overflow-y-auto p-6 md:p-8 border-b lg:border-b-0 lg:border-r border-slate-100">
                    <!-- Identitas -->
                    <div class="mb-8">
                        <h4 class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Informasi Pribadi</h4>
                        <div class="space-y-4">
                            <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">Nama Lengkap</p><p class="font-bold text-base md:text-lg text-slate-800" id="modalNama">-</p></div>
                            <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">Asal Sekolah</p><p class="font-semibold text-sm text-slate-700" id="modalSekolah">-</p></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">Tempat, Tgl Lahir</p><p class="font-semibold text-sm text-slate-700" id="modalTTL">-</p></div>
                                <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">Jenis Kelamin</p><p class="font-semibold text-sm text-slate-700" id="modalJK">-</p></div>
                            </div>
                            <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">NIK</p><p class="font-semibold text-sm font-mono text-slate-700" id="modalNIK">-</p></div>
                            <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">Alamat Lengkap</p><p class="font-semibold text-sm text-slate-700" id="modalAlamat">-</p></div>
                            <div><p class="text-[10px] md:text-xs text-slate-500 mb-1">Kontak Ortu/Wali</p><p class="font-semibold text-sm font-mono text-slate-700" id="modalKontak">-</p></div>
                        </div>
                    </div>

                    <!-- Dokumen Selection -->
                    <div class="mb-4 lg:mb-8">
                        <h4 class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Pemilihan Dokumen</h4>
                        <div class="flex flex-col gap-2 md:gap-3">
                            <button onclick="previewFile('foto')" class="text-left px-4 py-3 rounded-[16px] border border-slate-200 hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-accent/10 flex items-center justify-center transition-colors">
                                        <i class="ph ph-image text-lg text-slate-500 group-hover:text-accent transition-colors"></i>
                                    </div>
                                    <span class="font-bold text-sm text-slate-700 group-hover:text-accent">Pas Foto</span>
                                </div>
                                <i class="ph ph-caret-right text-slate-300 group-hover:text-accent group-hover:translate-x-1 transition-all"></i>
                            </button>
                            <button onclick="previewFile('kk')" class="text-left px-4 py-3 rounded-[16px] border border-slate-200 hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-accent/10 flex items-center justify-center transition-colors">
                                        <i class="ph ph-file-text text-lg text-slate-500 group-hover:text-accent transition-colors"></i>
                                    </div>
                                    <span class="font-bold text-sm text-slate-700 group-hover:text-accent">Kartu Keluarga</span>
                                </div>
                                <i class="ph ph-caret-right text-slate-300 group-hover:text-accent group-hover:translate-x-1 transition-all"></i>
                            </button>
                            <button onclick="previewFile('ijazah')" class="text-left px-4 py-3 rounded-[16px] border border-slate-200 hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-accent/10 flex items-center justify-center transition-colors">
                                        <i class="ph ph-certificate text-lg text-slate-500 group-hover:text-accent transition-colors"></i>
                                    </div>
                                    <span class="font-bold text-sm text-slate-700 group-hover:text-accent">Ijazah / SKL</span>
                                </div>
                                <i class="ph ph-caret-right text-slate-300 group-hover:text-accent group-hover:translate-x-1 transition-all"></i>
                            </button>
                            <button onclick="previewFile('akte')" class="text-left px-4 py-3 rounded-[16px] border border-slate-200 hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-accent/10 flex items-center justify-center transition-colors">
                                        <i class="ph ph-file text-lg text-slate-500 group-hover:text-accent transition-colors"></i>
                                    </div>
                                    <span class="font-bold text-sm text-slate-700 group-hover:text-accent">Akte Kelahiran</span>
                                </div>
                                <i class="ph ph-caret-right text-slate-300 group-hover:text-accent group-hover:translate-x-1 transition-all"></i>
                            </button>
                            <button onclick="previewFile('kip')" class="text-left px-4 py-3 rounded-[16px] border border-slate-200 hover:border-accent hover:bg-accent/5 transition-all flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-accent/10 flex items-center justify-center transition-colors">
                                        <i class="ph ph-card text-lg text-slate-500 group-hover:text-accent transition-colors"></i>
                                    </div>
                                    <span class="font-bold text-sm text-slate-700 group-hover:text-accent">KIP / KKS</span>
                                </div>
                                <i class="ph ph-caret-right text-slate-300 group-hover:text-accent group-hover:translate-x-1 transition-all"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- KOLOM KANAN: LIVE PREVIEW -->
                <div class="w-full lg:w-2/3 h-1/2 lg:h-full bg-slate-50 relative flex flex-col p-4 md:p-6">
                    <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm w-full h-full flex items-center justify-center overflow-hidden relative">
                        <div id="emptyPreview" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                            <i class="ph ph-file-magnifying-glass text-4xl md:text-5xl mb-3 opacity-50"></i>
                            <p class="font-medium text-sm">Pilih dokumen di sebelah kiri untuk melihat preview</p>
                        </div>
                        <iframe id="docIframe" class="w-full h-full hidden border-0 bg-transparent"></iframe>
                        <img id="docImg" class="max-w-full max-h-full object-contain hidden p-4" />
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <footer class="p-4 md:p-6 border-t border-slate-100 bg-white flex flex-col sm:flex-row gap-3 shrink-0 pb-safe">
                <input type="hidden" id="modalUserId">
                <div class="flex gap-3 w-full sm:w-auto">
                    <button onclick="verifikasiDitolak()" class="flex-1 sm:flex-none px-6 py-3.5 bg-red-50 text-red-600 hover:bg-red-100 border border-red-100 font-bold text-sm rounded-[16px] transition-colors active:scale-[0.98]">
                        Tolak
                    </button>
                    <button onclick="bukaModalRevisi()" class="flex-1 sm:flex-none px-6 py-3.5 bg-orange-50 text-orange-600 hover:bg-orange-100 border border-orange-100 font-bold text-sm rounded-[16px] transition-colors active:scale-[0.98]">
                        Revisi
                    </button>
                </div>
                <button onclick="verifikasiDiterima()" class="flex-1 py-3.5 bg-slate-900 text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 font-bold text-sm rounded-[16px] transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
                    <i class="ph ph-check-circle text-lg text-accent group-hover:scale-110 transition-transform"></i> Dokumen Valid (Luluskan)
                </button>
            </footer>
        </div>
    </div>

    <!-- MODAL CONFIRM CUSTOM -->
    <div id="confirmModal" class="fixed inset-0 z-[70] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupConfirm()"></div>
        <div id="confirmPanel" class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-8 scale-95 transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] mx-4 text-center">
            <div id="confirmIcon" class="w-16 h-16 rounded-full bg-red-100 text-red-500 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="ph ph-warning-circle"></i>
            </div>
            <h3 id="confirmTitle" class="text-xl font-bold text-slate-900 mb-2">Konfirmasi</h3>
            <p id="confirmDesc" class="text-sm text-slate-500 mb-8 leading-relaxed">Apakah Anda yakin?</p>
            <div class="flex gap-3">
                <button onclick="tutupConfirm()" class="flex-1 py-3 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-xl transition-colors">Batal</button>
                <button id="confirmBtn" class="flex-1 py-3 bg-red-500 text-white hover:bg-red-600 font-semibold rounded-xl transition-colors shadow-lg shadow-red-200 flex items-center justify-center gap-2">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL REVISI (Popup di atas modal verifikasi) -->
    <div id="revisiModal" class="fixed inset-0 z-[60] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupModalRevisi()"></div>
        <div id="revisiPanel" class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl p-8 scale-95 transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] mx-4">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-2xl shrink-0">
                    <i class="ph ph-note-pencil"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Pesan Revisi untuk Siswa</h3>
                    <p class="text-sm text-slate-500">Jelaskan berkas mana yang perlu diperbaiki</p>
                </div>
            </div>
            <textarea id="pesanRevisiInput" rows="5" placeholder="Contoh: Foto pas 3x4 terlalu buram, mohon upload ulang foto yang lebih jelas. Scan KK terpotong bagian bawahnya..." class="w-full border border-slate-200 rounded-2xl p-4 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 transition-all resize-none"></textarea>
            <p id="revisiError" class="text-red-500 text-xs mt-2 hidden">* Pesan revisi wajib diisi agar siswa tahu apa yang harus diperbaiki</p>
            <div class="flex gap-3 mt-6">
                <button onclick="tutupModalRevisi()" class="flex-1 py-3 border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button onclick="kirimRevisi()" class="flex-[2] py-3 bg-orange-500 text-white hover:bg-orange-600 font-semibold rounded-xl transition-colors shadow-lg shadow-orange-200 flex items-center justify-center gap-2">
                    <i class="ph ph-paper-plane-tilt"></i> Kirim Revisi
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- Removed jQuery & DataTables -->
    
    <script>
        let currentFiles = { foto: null, kk: null };

        document.addEventListener('DOMContentLoaded', () => {

            // Custom Search Table (Vanilla JS)
            const searchInput = document.getElementById('customSearch');
            const statusFilter = document.getElementById('filterStatus');
            const tableRows = document.querySelectorAll('.item-row');
            
            function filterTable() {
                const query = searchInput.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();

                tableRows.forEach(row => {
                    const nama = row.getAttribute('data-nama');
                    const nisn = row.getAttribute('data-nisn');
                    const rowStatus = row.getAttribute('data-status');

                    const matchesSearch = nama.includes(query) || nisn.includes(query);
                    const matchesStatus = status === '' || rowStatus === status;

                    if (matchesSearch && matchesStatus) {
                        row.classList.remove('hidden');
                        row.classList.add('flex', 'lg:table-row');
                    } else {
                        row.classList.add('hidden');
                        row.classList.remove('flex', 'lg:table-row');
                    }
                });
            }

            if(searchInput) searchInput.addEventListener('input', filterTable);
            if(statusFilter) statusFilter.addEventListener('change', filterTable);

            // Handle Checkbox All
            const selectAll = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');

            if(selectAll) {
                selectAll.addEventListener('change', (e) => {
                    const isChecked = e.target.checked;
                    rowCheckboxes.forEach(cb => {
                        // Only check visible rows
                        const row = cb.closest('.item-row');
                        if(row && !row.classList.contains('hidden')) {
                            cb.checked = isChecked;
                        }
                    });
                    toggleBatchAction();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const allChecked = Array.from(rowCheckboxes).filter(x => !x.closest('.item-row').classList.contains('hidden')).every(x => x.checked);
                    if(selectAll) selectAll.checked = allChecked;
                    toggleBatchAction();
                });
            });
        });

        function toggleBatchAction() {
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked').length;
            const container = document.getElementById('batchActionContainer');
            if(container) {
                if (checkedBoxes > 0) {
                    container.classList.remove('hidden');
                    container.classList.add('flex');
                } else {
                    container.classList.add('hidden');
                    container.classList.remove('flex');
                }
            }
        }

        function executeBatchAction() {
            const statusInput = document.getElementById('batchStatus');
            const status = statusInput ? statusInput.value : '';
            if (!status) {
                alert('Pilih aksi massal terlebih dahulu!');
                return;
            }

            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
            if (checkedBoxes.length === 0) return;

            const ids = Array.from(checkedBoxes).map(cb => cb.value);

            bukaConfirm(
                'Konfirmasi Aksi Massal', 
                `Anda yakin ingin merubah status ${ids.length} pendaftar menjadi ${status.toUpperCase()}?`, 
                () => {
                    fetch('batch_aksi.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: `ids=${JSON.stringify(ids)}&status=${status}`
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            location.reload(); // Reload untuk memperbarui UI dan chart
                        } else {
                            tutupConfirm();
                            alert('Gagal update: ' + res.message);
                        }
                    })
                    .catch(err => {
                        tutupConfirm();
                        console.error(err);
                        alert('Terjadi kesalahan koneksi.');
                    });
                }
            );
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

        const modal = document.getElementById('verifikasiModal');
        const panel = document.getElementById('modalPanel');

        function bukaModal(data) {
            // Populate data
            document.getElementById('modalUserId').value = data.id;
            document.getElementById('modalNisn').innerText = 'NISN: ' + data.nisn;
            document.getElementById('modalNama').innerText = data.nama_lengkap;
            document.getElementById('modalSekolah').innerText = data.nama_sd || '-';
            document.getElementById('modalTTL').innerText = (data.tempat_lahir || '-') + ', ' + (data.tanggal_lahir || '-');
            document.getElementById('modalJK').innerText = data.jenis_kelamin || '-';
            document.getElementById('modalNIK').innerText = data.nik || '-';
            
            const desa = data.desa ? data.desa + ', ' : '';
            const kec = data.kecamatan ? data.kecamatan + ', ' : '';
            const kab = data.kabupaten ? data.kabupaten : '-';
            document.getElementById('modalAlamat').innerText = (desa + kec + kab) || '-';
            document.getElementById('modalKontak').innerText = data.hp_ortu || '-';

            // Reset File Viewer
            document.getElementById('emptyPreview').classList.remove('hidden');
            document.getElementById('docIframe').classList.add('hidden');
            document.getElementById('docImg').classList.add('hidden');

            currentFiles = {
                foto: data.foto ? '../uploads/' + data.foto : null,
                kk: data.kk ? '../uploads/' + data.kk : null,
                ijazah: data.ijazah ? '../uploads/' + data.ijazah : null,
                akte: data.akte ? '../uploads/' + data.akte : null,
                kip: data.kip ? '../uploads/' + data.kip : null
            };

            // Show Modal
            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                panel.classList.remove('md:translate-x-full', 'translate-y-full');
            }, 50);
        }

        function previewFile(type) {
            const url = currentFiles[type];
            const empty = document.getElementById('emptyPreview');
            const iframe = document.getElementById('docIframe');
            const img = document.getElementById('docImg');

            empty.classList.add('hidden');
            iframe.classList.add('hidden');
            img.classList.add('hidden');

            if (!url) {
                empty.classList.remove('hidden');
                empty.innerHTML = '<i class="ph ph-file-x text-4xl md:text-5xl mb-3 opacity-50"></i><p class="font-medium text-red-400 text-sm">File tidak diunggah oleh pendaftar</p>';
                return;
            }

            // Check file extension
            if (url.toLowerCase().endsWith('.pdf')) {
                iframe.src = url;
                iframe.classList.remove('hidden');
            } else {
                img.src = url;
                img.classList.remove('hidden');
            }
        }

        function tutupModal() {
            panel.classList.add('md:translate-x-full', 'translate-y-full');
            setTimeout(() => {
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 700);
        }

        function prosesVerifikasi(status, pesanRevisi = '') {
            const id = document.getElementById('modalUserId').value;
            const buttons = document.querySelectorAll('#verifikasiModal footer button');
            
            // Disable buttons and show loading
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });
            
            let bodyData = `id=${id}&status=${status}&ajax=1`;
            if (status === 'revisi' && pesanRevisi) {
                bodyData += `&pesan_revisi=${encodeURIComponent(pesanRevisi)}`;
            }

            fetch('verifikasi_aksi.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: bodyData
            })
            .then(response => response.json())
            .then(res => {
                tutupConfirm();
                // Reset buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
                if (res.success) {
                    tutupModal();

                    // Update the row badge without page reload
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        const badge = row.querySelector('.status-badge');
                        const textEl = row.querySelector('.status-text');
                        const iconEl = row.querySelector('.status-badge i');

                        if (badge && textEl) {
                            // Update text
                            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
                            textEl.innerText = statusText;

                            // Update data-status attribute for filter
                            row.setAttribute('data-status', status);

                            // Remove old color classes
                            badge.classList.remove(
                                'bg-amber-50', 'text-amber-600', 'border-amber-200/50',
                                'bg-accent/10', 'text-accent', 'border-accent/20',
                                'bg-red-50', 'text-red-600', 'border-red-200/50',
                                'bg-orange-50', 'text-orange-600', 'border-orange-200/50'
                            );

                            // Apply new color classes and icon
                            const iconMap = {
                                'diterima': { classes: ['bg-accent/10', 'text-accent', 'border-accent/20'], icon: 'ph-check-circle' },
                                'ditolak':  { classes: ['bg-red-50', 'text-red-600', 'border-red-200/50'],  icon: 'ph-x-circle' },
                                'revisi':   { classes: ['bg-orange-50', 'text-orange-600', 'border-orange-200/50'], icon: 'ph-arrow-counter-clockwise' },
                                'pending':  { classes: ['bg-amber-50', 'text-amber-600', 'border-amber-200/50'], icon: 'ph-hourglass-medium' }
                            };
                            const map = iconMap[status] || iconMap['pending'];
                            badge.classList.add(...map.classes);
                            if (iconEl) {
                                iconEl.className = `ph ${map.icon} text-sm`;
                            }
                        }

                        // Flash highlight
                        row.classList.add('bg-accent/10');
                        setTimeout(() => row.classList.remove('bg-accent/10'), 1500);
                    }
                } else {
                    alert('Gagal memperbarui status: ' + (res.message || 'Terjadi kesalahan.'));
                }
            })
            .catch(err => {
                tutupConfirm();
                // Reset buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
                console.error('[prosesVerifikasi] Network error:', err);
                alert('Terjadi kesalahan jaringan. Cek koneksi Anda.');
            });
        }

        // === REVISI MODAL FUNCTIONS ===
        const revisiModal = document.getElementById('revisiModal');
        const revisiPanel = document.getElementById('revisiPanel');

        function bukaModalRevisi() {
            document.getElementById('pesanRevisiInput').value = '';
            document.getElementById('revisiError').classList.add('hidden');
            revisiModal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                revisiPanel.classList.remove('scale-95');
                revisiPanel.classList.add('scale-100');
            }, 50);
            // Auto-focus textarea
            setTimeout(() => document.getElementById('pesanRevisiInput').focus(), 200);
        }

        function resetPasswordSiswa(id) {
            bukaConfirm(
                'Reset Password', 
                "Yakin ingin mereset password siswa ini menjadi '123456'? \nPassword baru harus diinformasikan ke siswa secara offline.", 
                () => {
                    fetch('reset_password_siswa.php', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(res => {
                        tutupConfirm();
                        if (res.success) {
                            alert('Password berhasil direset menjadi: 123456');
                        } else {
                            alert('Gagal mereset password: ' + res.message);
                        }
                    })
                    .catch(err => {
                        tutupConfirm();
                        console.error(err);
                        alert('Terjadi kesalahan jaringan.');
                    });
                }
            );
        }

        function tutupModalRevisi() {
            revisiPanel.classList.remove('scale-100');
            revisiPanel.classList.add('scale-95');
            setTimeout(() => {
                revisiModal.classList.add('opacity-0', 'pointer-events-none');
            }, 200);
        }

        function kirimRevisi() {
            const pesan = document.getElementById('pesanRevisiInput').value.trim();
            if (!pesan) {
                document.getElementById('revisiError').classList.remove('hidden');
                document.getElementById('pesanRevisiInput').classList.add('border-red-300');
                return;
            }
            tutupModalRevisi();
            prosesVerifikasi('revisi', pesan);
        }

        // === CUSTOM CONFIRM MODAL ===
        const confirmModal = document.getElementById('confirmModal');
        const confirmPanel = document.getElementById('confirmPanel');
        let confirmAction = null;

        function bukaConfirm(title, desc, actionCallback, type = 'danger') {
            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmDesc').innerText = desc;
            confirmAction = actionCallback;
            
            const btn = document.getElementById('confirmBtn');
            const icon = document.getElementById('confirmIcon');
            
            // Reset styles
            btn.className = 'flex-1 py-3 text-white font-semibold rounded-xl transition-colors shadow-lg flex items-center justify-center gap-2';
            icon.className = 'w-16 h-16 rounded-full flex items-center justify-center text-3xl mx-auto mb-4';
            
            if (type === 'success') {
                btn.classList.add('bg-accent', 'hover:bg-[#0d9466]', 'shadow-emerald-200');
                icon.classList.add('bg-emerald-100', 'text-accent');
                icon.innerHTML = '<i class="ph ph-check-circle"></i>';
            } else {
                btn.classList.add('bg-red-500', 'hover:bg-red-600', 'shadow-red-200');
                icon.classList.add('bg-red-100', 'text-red-500');
                icon.innerHTML = '<i class="ph ph-warning-circle"></i>';
            }

            confirmModal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                confirmPanel.classList.remove('scale-95');
                confirmPanel.classList.add('scale-100');
            }, 50);
        }

        function tutupConfirm() {
            confirmPanel.classList.remove('scale-100');
            confirmPanel.classList.add('scale-95');
            setTimeout(() => {
                confirmModal.classList.add('opacity-0', 'pointer-events-none');
                confirmAction = null;
                // Reset button text
                document.getElementById('confirmBtn').innerHTML = 'Ya, Lanjutkan';
            }, 200);
        }

        document.getElementById('confirmBtn').addEventListener('click', () => {
            if (confirmAction) {
                const btn = document.getElementById('confirmBtn');
                btn.innerHTML = '<i class="ph ph-spinner animate-spin text-xl"></i> Memproses...';
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                confirmAction();
            }
        });

        // Update footer actions to use confirm
        function verifikasiDitolak() {
            bukaConfirm('Tolak Pendaftar', 'Apakah Anda yakin ingin menolak pendaftar ini? Tindakan ini akan memberitahukan siswa bahwa berkas tidak memenuhi syarat.', () => prosesVerifikasi('ditolak'));
        }

        function verifikasiDiterima() {
            bukaConfirm('Luluskan Pendaftar', 'Pastikan semua dokumen sudah valid. Siswa akan dinyatakan LULUS seleksi administrasi.', () => prosesVerifikasi('diterima'), 'success');
        }
    </script>
</body>
</html>