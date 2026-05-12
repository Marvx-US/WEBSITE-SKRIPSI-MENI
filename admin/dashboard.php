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


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator | PPDB MTs Al-Barakah</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <?= csrf_meta() ?>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

    <?php $active_menu = 'dashboard'; include 'layout/sidebar.php'; ?>

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
                <span class="text-slate-900">Dashboard</span>
            </nav>
            
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 md:gap-6 mb-8">
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-slate-100 shadow-sm flex flex-col justify-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-[14px] bg-blue-50 text-blue-500 flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-users-three"></i></div>
                    <div>
                        <h3 class="text-2xl md:text-[2rem] font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats['total'] ?></h3>
                        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendaftar</p>
                    </div>
                </div>
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-accent/20 shadow-[0_8px_32px_0_rgba(16,178,124,0.05)] flex flex-col justify-center gap-3 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-accent/[0.03] group-hover:bg-accent/[0.06] transition-colors duration-700"></div>
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-[14px] bg-accent/10 text-accent flex items-center justify-center text-xl md:text-2xl relative z-10"><i class="ph ph-check-circle"></i></div>
                    <div class="relative z-10">
                        <h3 class="text-2xl md:text-[2rem] font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats['lulus'] ?></h3>
                        <p class="text-[10px] md:text-xs font-bold text-accent uppercase tracking-wider">Lulus / Diterima</p>
                    </div>
                </div>
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-slate-100 shadow-sm flex flex-col justify-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-[14px] bg-red-50 text-red-500 flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-x-circle"></i></div>
                    <div>
                        <h3 class="text-2xl md:text-[2rem] font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats['ditolak'] ?></h3>
                        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider">Ditolak</p>
                    </div>
                </div>
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-slate-100 shadow-sm flex flex-col justify-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-[14px] bg-amber-50 text-amber-500 flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-hourglass-medium"></i></div>
                    <div>
                        <h3 class="text-2xl md:text-[2rem] font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats['proses'] ?></h3>
                        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider">Pending</p>
                    </div>
                </div>
                <div class="col-span-2 lg:col-span-1 xl:col-span-1 card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-slate-100 shadow-sm flex flex-col justify-center gap-3">
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-[14px] bg-orange-50 text-orange-500 flex items-center justify-center text-xl md:text-2xl"><i class="ph ph-arrow-counter-clockwise"></i></div>
                    <div>
                        <h3 class="text-2xl md:text-[2rem] font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats['revisi'] ?></h3>
                        <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-wider">Butuh Revisi</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              
                <div class="bg-panel p-6 rounded-[24px] border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6">Trend Pendaftar (7 Hari Terakhir)</h3>
                    <div class="h-64 w-full relative">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
             
                <div class="bg-panel p-6 rounded-[24px] border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6">Demografi Jenis Kelamin</h3>
                    <div class="h-64 w-full relative flex justify-center">
                        <canvas id="genderPieChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Scripts -->
    <!-- Removed jQuery & DataTables -->
    
    <script>
        // Data for ChartJS
        const chartTrend = {
            labels: <?= json_encode($label_trend_chart) ?>,
            data: <?= json_encode($data_trend_chart) ?>
        };

        const chartGender = {
            labels: <?= json_encode($label_gender_chart) ?>,
            data: <?= json_encode($data_gender_chart) ?>
        };

        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
        });

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
    </script>
</body>
</html>