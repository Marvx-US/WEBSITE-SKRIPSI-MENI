<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['role_admin'] !== 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['add_admin'])) {
    csrf_check();
    
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $role         = $_POST['role'] ?? 'verifikator';

    $stmt = $pdo->prepare("INSERT INTO users_admin (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$nama_lengkap, $username, $password, $role])) {
        $msg_success = "Panitia berhasil ditambahkan!";
    } else {
        $msg_error = "Gagal menambah panitia!";
    }
}

if (isset($_POST['reset_admin'])) {
    csrf_check();
    $id = $_POST['id'];
    $new_password = password_hash($_POST['new_password'] ?? '', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users_admin SET password = ? WHERE id = ?");
    if ($stmt->execute([$new_password, $id])) {
        $msg_success = "Password panitia berhasil direset!";
    } else {
        $msg_error = "Gagal mereset password!";
    }
}

$query = $pdo->query("SELECT * FROM users_admin ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Panitia | Admin PPDB</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
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
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans flex h-screen overflow-hidden">

    <!-- OVERLAY MOBILE -->
    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <?php $active_menu = 'kelola_users'; include 'layout/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <!-- TOPBAR -->
        <header class="h-20 bg-panel/60 backdrop-blur-md border-b border-slate-200/60 flex items-center gap-4 px-6 md:px-8 shrink-0 z-10 sticky top-0">
            <button class="md:hidden w-10 h-10 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-600 hover:text-accent transition-colors" onclick="toggleSidebar()">
                <i class="ph ph-list text-xl font-bold"></i>
            </button>
            <h2 class="text-xl md:text-2xl font-extrabold tracking-tight text-slate-800">Kelola Akun Panitia</h2>
        </header>

        <!-- SCROLLABLE CONTENT -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8 bg-surface">
            
            <!-- BREADCRUMB -->
            <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
                <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
                <i class="ph ph-caret-right text-[10px]"></i>
                <span class="text-slate-900">Kelola Panitia</span>
            </nav>
            
            <?php if(isset($msg_success)): ?>
            <div class="mb-6 p-4 bg-accent/10 border border-accent/20 text-accent rounded-stitch font-medium flex items-center gap-2">
                <i class="ph ph-check-circle text-xl"></i> <?= $msg_success ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($msg_error)): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-stitch font-medium flex items-center gap-2">
                <i class="ph ph-warning-circle text-xl"></i> <?= $msg_error ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- FORM TAMBAH -->
                <div class="bg-panel rounded-[24px] border border-slate-200 shadow-sm p-6 lg:col-span-1 h-fit">
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6">Tambah Panitia Baru</h3>
                    <form method="POST" class="space-y-4">
                        <?= csrf_field() ?>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" required class="w-full border-b border-slate-300 bg-transparent py-2 outline-none focus:border-accent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                            <input type="text" name="username" required class="w-full border-b border-slate-300 bg-transparent py-2 outline-none focus:border-accent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                            <input type="password" name="password" required class="w-full border-b border-slate-300 bg-transparent py-2 outline-none focus:border-accent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Wewenang</label>
                            <select name="role" required class="w-full border-b border-slate-300 bg-transparent py-2 outline-none focus:border-accent transition-colors appearance-none">
                                <option value="verifikator">Verifikator</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                        <button type="submit" name="add_admin" class="w-full py-3 bg-slate-900 text-white font-bold rounded-full hover:bg-slate-800 transition-all mt-4">
                            Simpan Panitia
                        </button>
                    </form>
                </div>

                <!-- TABLE DATA -->
                <div class="bg-panel rounded-[24px] border border-slate-200 shadow-sm p-6 lg:col-span-2 overflow-hidden flex flex-col">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Daftar Panitia</h3>
                        <!-- Custom Search -->
                        <div class="relative w-full sm:w-64">
                            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="customSearch" placeholder="Cari nama atau username..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl pl-10 pr-4 py-2 focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap hidden lg:table">
                            <thead>
                                <tr class="text-xs text-slate-400 border-b border-slate-100 uppercase tracking-wider">
                                    <th class="pb-3 font-semibold">ID</th>
                                    <th class="pb-3 font-semibold">Nama Lengkap</th>
                                    <th class="pb-3 font-semibold">Username</th>
                                    <th class="pb-3 font-semibold">Role</th>
                                    <th class="pb-3 font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php 
                                $query->execute(); 
                                while ($row = $query->fetch()) : 
                                ?>
                                <tr class="item-row border-b border-slate-50 hover:bg-slate-50 transition-colors" data-nama="<?= strtolower(htmlspecialchars($row['nama_lengkap'])) ?>" data-username="<?= strtolower(htmlspecialchars($row['username'])) ?>">
                                    <td class="py-4 font-bold text-slate-900">#<?= $row['id'] ?></td>
                                    <td class="py-4 font-medium text-slate-700"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                    <td class="py-4"><span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold"><?= htmlspecialchars($row['username']) ?></span></td>
                                    <td class="py-4">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider <?= $row['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                            <?= htmlspecialchars($row['role']) ?>
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <?php if ($row['id'] != 1 && $row['id'] != $_SESSION['user_id']) : ?>
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="bukaResetModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="text-blue-500 hover:text-blue-700 font-medium text-sm flex items-center gap-1 active:scale-95 transition-transform">
                                                    <i class="ph ph-key"></i> Reset Sandi
                                                </button>
                                                <span class="text-slate-300">|</span>
                                                <form method="POST" action="hapus_user.php" class="inline" onsubmit="return confirm('Yakin menghapus panitia ini?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium text-sm flex items-center gap-1 active:scale-95 transition-transform">
                                                        <i class="ph ph-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else : ?>
                                            <span class="text-slate-400 text-xs italic font-medium">Super Admin</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <!-- Mobile View List -->
                        <div class="lg:hidden flex flex-col gap-4">
                            <?php 
                            $query->execute(); 
                            while ($row = $query->fetch()) : 
                            ?>
                            <div class="item-row bg-white border border-slate-100 p-4 rounded-[16px] shadow-sm flex flex-col gap-3" data-nama="<?= strtolower(htmlspecialchars($row['nama_lengkap'])) ?>" data-username="<?= strtolower(htmlspecialchars($row['username'])) ?>">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs text-slate-400 font-bold">#<?= $row['id'] ?></p>
                                        <p class="font-bold text-slate-800 text-base"><?= htmlspecialchars($row['nama_lengkap']) ?></p>
                                    </div>
                                    <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider <?= $row['role'] === 'superadmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                        <?= htmlspecialchars($row['role']) ?>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-3 border-t border-slate-50">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold"><?= htmlspecialchars($row['username']) ?></span>
                                    <?php if ($row['id'] != 1 && $row['id'] != $_SESSION['user_id']) : ?>
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="bukaResetModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama_lengkap'])) ?>')" class="text-blue-500 hover:text-blue-700 font-bold text-sm p-2 bg-blue-50 rounded-lg active:scale-95 transition-all">
                                                <i class="ph ph-key"></i>
                                            </button>
                                            <form method="POST" action="hapus_user.php" class="inline" onsubmit="return confirm('Yakin menghapus panitia ini?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm p-2 bg-red-50 rounded-lg active:scale-95 transition-all">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-slate-400 text-[10px] italic font-medium">Super Admin</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- MODAL RESET SANDI -->
    <div id="resetModal" class="fixed inset-0 z-[60] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-500">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="tutupResetModal()"></div>
        <div id="resetPanel" class="relative bg-white w-full max-w-md p-6 rounded-3xl shadow-2xl scale-95 transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]">
            <h3 class="text-xl font-bold text-slate-800 mb-2">Reset Kata Sandi</h3>
            <p class="text-sm text-slate-500 mb-6">Masukkan kata sandi baru untuk <span id="resetTargetName" class="font-bold text-slate-800"></span>.</p>
            
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="resetUserId">
                <input type="hidden" name="reset_admin" value="1">
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kata Sandi Baru</label>
                    <input type="password" name="new_password" required placeholder="Minimal 6 karakter..." class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-3 outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                </div>
                
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="tutupResetModal()" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-full transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-full shadow-md hover:shadow-lg transition-all active:scale-95">Reset Sandi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Removed jQuery & DataTables -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Vanilla JS Search Filter
            const searchInput = document.getElementById('customSearch');
            const tableRows = document.querySelectorAll('.item-row');
            
            if(searchInput) {
                searchInput.addEventListener('input', () => {
                    const query = searchInput.value.toLowerCase();

                    tableRows.forEach(row => {
                        const nama = row.getAttribute('data-nama');
                        const username = row.getAttribute('data-username');

                        if (nama.includes(query) || username.includes(query)) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                });
            }
        });


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

        // Modal Reset Logic
        function bukaResetModal(id, nama) {
            document.getElementById('resetUserId').value = id;
            document.getElementById('resetTargetName').innerText = nama;
            
            const modal = document.getElementById('resetModal');
            const panel = document.getElementById('resetPanel');
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                panel.classList.remove('scale-95');
                panel.classList.add('scale-100');
            }, 50);
        }

        function tutupResetModal() {
            const modal = document.getElementById('resetModal');
            const panel = document.getElementById('resetPanel');
            
            panel.classList.remove('scale-100');
            panel.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('opacity-0', 'pointer-events-none');
            }, 300);
        }
    </script>
</body>
</html>
