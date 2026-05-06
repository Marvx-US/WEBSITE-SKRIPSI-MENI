<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin' || $_SESSION['role_admin'] != 'superadmin') {
    header('Location: ../auth/login.php');
    exit;
}

$success = "";
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    csrf_check();
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    
    if (empty($judul) || empty($isi)) {
        $error = "Judul dan isi pengumuman tidak boleh kosong.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO ppdb_pengumuman (judul, isi) VALUES (?, ?)");
        if ($stmt->execute([$judul, $isi])) {
            $success = "Pengumuman berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan pengumuman.";
        }
    }
}


if (isset($_GET['hapus'])) {
    if(!isset($_GET['csrf']) || $_GET['csrf'] !== csrf_token()) {
        die("Invalid CSRF token.");
    }
    $id = (int) $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM ppdb_pengumuman WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = "Pengumuman berhasil dihapus.";
    } else {
        $error = "Gagal menghapus pengumuman.";
    }
}


$pengumuman = $pdo->query("SELECT * FROM ppdb_pengumuman ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengumuman | Admin PPDB</title>
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
</head>
<body class="bg-surface text-slate-800 antialiased font-sans min-h-screen">
    <!-- Header -->
    <header class="bg-panel border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 h-20 flex items-center gap-4">
            <a href="dashboard.php" class="p-2 hover:bg-slate-100 rounded-full transition-colors">
                <i class="ph ph-arrow-left text-2xl text-slate-600"></i>
            </a>
            <h1 class="text-xl font-bold tracking-tight">Kelola Pengumuman Publik</h1>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8">
        <!-- BREADCRUMB -->
        <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
            <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
            <i class="ph ph-caret-right text-[10px]"></i>
            <span class="text-slate-900">Kelola Pengumuman</span>
        </nav>
        <?php if($success): ?>
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 flex items-center gap-3">
            <i class="ph ph-check-circle text-xl"></i> <?= $success ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl border border-red-100 flex items-center gap-3">
            <i class="ph ph-x-circle text-xl"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Tambah -->
            <div class="lg:col-span-1">
                <div class="bg-panel p-6 rounded-[24px] border border-slate-200 shadow-sm">
                    <h2 class="text-lg font-bold mb-4">Buat Pengumuman Baru</h2>
                    <form action="" method="POST">
                        <?= csrf_field() ?>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Judul Pengumuman</label>
                                <input type="text" name="judul" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-accent focus:ring-2 focus:ring-accent/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-600 mb-1">Isi Pengumuman</label>
                                <textarea name="isi" required rows="5" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg outline-none focus:border-accent focus:ring-2 focus:ring-accent/10 transition-all resize-none"></textarea>
                            </div>
                            <button type="submit" name="tambah" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-colors">
                                Simpan Pengumuman
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daftar Pengumuman -->
            <div class="lg:col-span-2 space-y-4">
                <?php if(empty($pengumuman)): ?>
                    <div class="bg-panel p-10 rounded-[24px] border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center">
                        <i class="ph ph-megaphone text-4xl text-slate-300 mb-2"></i>
                        <h3 class="text-slate-500 font-medium">Belum ada pengumuman</h3>
                    </div>
                <?php else: ?>
                    <?php foreach($pengumuman as $p): ?>
                        <div class="bg-panel p-6 rounded-[24px] border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-start gap-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800 mb-1"><?= htmlspecialchars($p['judul']) ?></h3>
                                <p class="text-xs text-slate-400 mb-3"><i class="ph ph-clock"></i> <?= date('d M Y, H:i', strtotime($p['tgl_buat'])) ?></p>
                                <p class="text-sm text-slate-600 whitespace-pre-wrap"><?= htmlspecialchars($p['isi']) ?></p>
                            </div>
                            <div class="shrink-0 pt-1">
                                <a href="?hapus=<?= $p['id'] ?>&csrf=<?= csrf_token() ?>" onclick="return confirm('Hapus pengumuman ini?')" class="text-red-500 hover:text-red-600 font-medium text-sm flex items-center gap-1 bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
                                    <i class="ph ph-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
