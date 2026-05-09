<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Ambil semua tahun ajaran yang unik dari DB (Hanya yang memiliki siswa diterima)
$stmt_tahun = $pdo->query("SELECT DISTINCT tahun_ajaran FROM users_siswa WHERE tahun_ajaran IS NOT NULL AND tahun_ajaran != '' AND status = 'diterima' ORDER BY tahun_ajaran DESC");
$daftar_tahun = $stmt_tahun->fetchAll(PDO::FETCH_COLUMN);

// Tahun ajaran yang dipilih (default: tahun terbaru)
$tahun_dipilih = $_GET['tahun'] ?? ($daftar_tahun[0] ?? '');

// Statistik per tahun yang dipilih (Hanya Diterima)
$stats_arsip = [
    'total'  => 0,
    'laki'   => 0,
    'perempuan' => 0,
];

$data_diterima = [];

if ($tahun_dipilih !== '') {
    $stmt_stats = $pdo->prepare("SELECT jenis_kelamin, COUNT(*) as c FROM users_siswa WHERE tahun_ajaran = ? AND status = 'diterima' GROUP BY jenis_kelamin");
    $stmt_stats->execute([$tahun_dipilih]);
    while ($r = $stmt_stats->fetch()) {
        $jk = $r['jenis_kelamin'];
        if ($jk === 'L') {
            $stats_arsip['laki'] += (int)$r['c'];
        } elseif ($jk === 'P') {
            $stats_arsip['perempuan'] += (int)$r['c'];
        }
        $stats_arsip['total'] += (int)$r['c'];
    }

    $stmt_diterima = $pdo->prepare("SELECT * FROM users_siswa WHERE tahun_ajaran = ? AND status = 'diterima' ORDER BY nama_lengkap ASC");
    $stmt_diterima->execute([$tahun_dipilih]);
    $data_diterima = $stmt_diterima->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Siswa Diterima | PPDB MTs Al-Barakah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
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
        ::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .card-lift { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .card-lift:hover { transform: translateY(-3px); box-shadow: 0 16px 32px -8px rgba(0,0,0,0.08); }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans flex h-screen overflow-hidden">

    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-72 bg-panel/95 backdrop-blur-xl border-r border-slate-100 flex flex-col justify-between transform -translate-x-full md:translate-x-0 md:static transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] z-50 shrink-0">
        <div>
            <div class="h-20 flex items-center justify-between px-8 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <img src="../assets/img/logo.png" alt="Logo" class="w-9 h-9 object-contain">
                    <h1 class="text-lg font-extrabold tracking-tight">Admin PPDB</h1>
                </div>
                <button class="md:hidden w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500" onclick="toggleSidebar()">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            <nav class="p-6 space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-stitch transition-colors">
                    <i class="ph ph-squares-four text-xl"></i> Dashboard
                </a>
                <a href="arsip.php" class="flex items-center gap-3 px-4 py-3 bg-accent/10 text-accent font-semibold rounded-stitch transition-colors">
                    <i class="ph ph-archive text-xl"></i> Arsip Tahun Ajaran
                </a>
                <?php if(isset($_SESSION['role_admin']) && $_SESSION['role_admin'] === 'superadmin'): ?>
                <a href="kelola_users.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-stitch transition-colors">
                    <i class="ph ph-users text-xl"></i> Kelola Panitia
                </a>
                <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium rounded-stitch transition-colors">
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
        <header class="h-20 bg-panel border-b border-slate-100 flex items-center justify-between px-6 md:px-8 shrink-0 z-10">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-slate-500 hover:text-slate-800" onclick="toggleSidebar()">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight">Arsip Tahun Ajaran</h2>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Rekap historis siswa yang diterima per tahun ajaran</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <?php if($tahun_dipilih && count($data_diterima) > 0): ?>
                <a href="export_excel.php?status=diterima&tahun=<?= urlencode($tahun_dipilih) ?>" class="bg-accent/10 text-accent px-5 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-accent hover:text-white transition-all">
                    <i class="ph ph-download-simple text-lg"></i> <span class="hidden sm:inline">Export Excel</span>
                </a>
                <?php endif; ?>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <nav class="flex items-center gap-2 text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
                <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
                <i class="ph ph-caret-right text-[10px]"></i>
                <span class="text-slate-900">Arsip Tahun Ajaran</span>
            </nav>

            <?php if(empty($daftar_tahun)): ?>
            <!-- EMPTY STATE -->
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center text-4xl text-slate-300 mb-4">
                    <i class="ph ph-archive"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-500 mb-2">Belum Ada Data Arsip</h3>
                <p class="text-sm text-slate-400 max-w-sm">Data arsip akan otomatis muncul setelah ada siswa yang mendaftar. Kolom <code class="bg-slate-100 px-1 rounded">tahun_ajaran</code> terisi saat pendaftaran.</p>
            </div>
            <?php else: ?>

            <!-- FILTER TAHUN AJARAN -->
            <div class="bg-panel rounded-[24px] border border-slate-100 shadow-sm p-6 md:p-8 mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pilih Tahun Ajaran</p>
                        <p class="text-sm text-slate-500">Tersedia <strong class="text-slate-800"><?= count($daftar_tahun) ?></strong> tahun ajaran dalam arsip sistem</p>
                    </div>
                    <form method="GET" class="sm:ml-auto flex items-center gap-3">
                        <div class="relative">
                            <i class="ph ph-calendar-blank absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select name="tahun" onchange="this.form.submit()" class="pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-sm font-semibold rounded-xl outline-none focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all cursor-pointer appearance-none text-slate-700">
                                <?php foreach($daftar_tahun as $thn): ?>
                                <option value="<?= htmlspecialchars($thn) ?>" <?= $thn === $tahun_dipilih ? 'selected' : '' ?>>
                                    T.A. <?= htmlspecialchars($thn) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="ph ph-caret-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </form>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8">
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-accent/20 shadow-sm flex flex-col gap-3 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-accent/[0.03] group-hover:bg-accent/[0.06] transition-colors duration-500"></div>
                    <div class="w-11 h-11 rounded-[14px] bg-accent/10 text-accent flex items-center justify-center text-xl relative z-10"><i class="ph ph-users-three"></i></div>
                    <div class="relative z-10">
                        <h3 class="text-3xl font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats_arsip['total'] ?></h3>
                        <p class="text-[10px] font-bold text-accent uppercase tracking-wider">Total Diterima</p>
                    </div>
                </div>
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-slate-100 shadow-sm flex flex-col gap-3">
                    <div class="w-11 h-11 rounded-[14px] bg-blue-50 text-blue-500 flex items-center justify-center text-xl"><i class="ph ph-gender-male"></i></div>
                    <div>
                        <h3 class="text-3xl font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats_arsip['laki'] ?></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Laki-laki</p>
                    </div>
                </div>
                <div class="card-lift bg-panel p-5 md:p-6 rounded-[24px] border border-slate-100 shadow-sm flex flex-col gap-3">
                    <div class="w-11 h-11 rounded-[14px] bg-rose-50 text-rose-500 flex items-center justify-center text-xl"><i class="ph ph-gender-female"></i></div>
                    <div>
                        <h3 class="text-3xl font-extrabold tabular-nums tracking-tight text-slate-800 leading-none mb-1"><?= $stats_arsip['perempuan'] ?></h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Perempuan</p>
                    </div>
                </div>
            </div>

            <!-- TABEL SISWA DITERIMA -->
            <div class="bg-panel rounded-[24px] border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold tracking-tight text-slate-800">
                            Daftar Siswa Diterima
                            <span class="ml-2 text-sm font-semibold text-accent bg-accent/10 px-2.5 py-0.5 rounded-full">T.A. <?= htmlspecialchars($tahun_dipilih) ?></span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Total <strong class="text-slate-600"><?= count($data_diterima) ?></strong> siswa resmi diterima pada tahun ajaran ini</p>
                    </div>
                    <!-- Search filter client-side -->
                    <div class="relative w-full sm:w-64">
                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="text" id="searchArsip" placeholder="Cari nama atau NISN..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl pl-10 pr-4 py-2.5 outline-none focus:bg-white focus:border-accent focus:ring-4 focus:ring-accent/10 transition-all">
                    </div>
                </div>

                <?php if(empty($data_diterima)): ?>
                <div class="py-16 text-center">
                    <i class="ph ph-smiley-sad text-5xl text-slate-200 mb-3"></i>
                    <p class="text-slate-400 font-medium text-sm">Belum ada siswa diterima pada tahun ajaran <?= htmlspecialchars($tahun_dipilih) ?></p>
                </div>
                <?php else: ?>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase tracking-wider bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-4 font-bold w-12 text-center">No</th>
                                <th class="px-6 py-4 font-bold">Identitas Siswa</th>
                                <th class="px-6 py-4 font-bold">Tempat, Tanggal Lahir</th>
                                <th class="px-6 py-4 font-bold">Asal Sekolah</th>
                                <th class="px-6 py-4 font-bold">Nama Ayah</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="arsipTableBody">
                            <?php foreach($data_diterima as $i => $siswa): ?>
                            <tr class="arsip-row hover:bg-slate-50/50 transition-colors"
                                data-nama="<?= strtolower(htmlspecialchars($siswa['nama_lengkap'])) ?>"
                                data-nisn="<?= htmlspecialchars($siswa['nisn']) ?>">
                                <td class="px-6 py-4 text-center text-slate-400 font-mono text-xs"><?= $i + 1 ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-accent/10 text-accent flex items-center justify-center font-bold text-sm shrink-0">
                                            <?= strtoupper(substr($siswa['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800"><?= htmlspecialchars($siswa['nama_lengkap']) ?></p>
                                            <p class="text-xs text-slate-400 font-mono">NISN: <?= htmlspecialchars($siswa['nisn']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">
                                    <?= htmlspecialchars(($siswa['tempat_lahir'] ?? '-') . ', ' . ($siswa['tanggal_lahir'] ?? '-')) ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">
                                    <?= htmlspecialchars($siswa['nama_sd'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600 font-medium">
                                    <?= htmlspecialchars($siswa['nama_ayah'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-accent/10 text-accent border border-accent/20">
                                        <i class="ph ph-check-circle"></i> Diterima
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
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

        // Client-side search for archive table
        const searchArsip = document.getElementById('searchArsip');
        if (searchArsip) {
            searchArsip.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.arsip-row').forEach(row => {
                    const nama = row.getAttribute('data-nama');
                    const nisn = row.getAttribute('data-nisn');
                    row.style.display = (nama.includes(q) || nisn.includes(q)) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>
