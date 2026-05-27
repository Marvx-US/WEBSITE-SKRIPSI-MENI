<?php
$active_menu = $active_menu ?? '';
?>
<aside id="sidebar" class="fixed inset-y-0 left-0 w-[280px] bg-panel/80 backdrop-blur-xl border-r border-slate-200/60 flex flex-col justify-between transform -translate-x-full md:translate-x-0 md:static transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] z-50 shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)] md:shadow-none">
    <div>
        <div class="h-20 flex items-center justify-between px-8 border-b border-slate-100/60">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                    <img src="../assets/img/logo.png" alt="Logo" class="w-7 h-7 object-contain">
                </div>
                <h1 class="text-lg font-bold tracking-tight text-slate-800">Admin PPDB</h1>
            </div>
            <button class="md:hidden w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition-colors" onclick="toggleSidebar()">
                <i class="ph ph-x text-lg font-bold"></i>
            </button>
        </div>
        
        <nav class="p-6 space-y-2">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 <?= $active_menu === 'dashboard' ? 'bg-accent/10 text-accent font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium' ?> rounded-stitch transition-all group">
                <i class="ph ph-squares-four text-xl group-hover:scale-110 transition-transform"></i> Dashboard
            </a>
            <a href="verifikasi.php" class="flex items-center gap-3 px-4 py-3 <?= $active_menu === 'verifikasi' ? 'bg-accent/10 text-accent font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium' ?> rounded-stitch transition-all group">
                <i class="ph ph-check-square-offset text-xl group-hover:scale-110 transition-transform"></i> Manajemen Verifikasi
            </a>
            <a href="arsip.php" class="flex items-center gap-3 px-4 py-3 <?= $active_menu === 'arsip' ? 'bg-accent/10 text-accent font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium' ?> rounded-stitch transition-all group">
                <i class="ph ph-archive text-xl group-hover:scale-110 transition-transform"></i> Arsip Tahun Ajaran
            </a>
            
            <a href="faceid.php" class="flex items-center gap-3 px-4 py-3 <?= $active_menu === 'faceid' ? 'bg-accent/10 text-accent font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium' ?> rounded-stitch transition-all group">
                <i class="ph ph-face-id text-xl group-hover:scale-110 transition-transform"></i> Pengaturan FaceID
            </a>
            
            <?php if(isset($_SESSION['role_admin']) && $_SESSION['role_admin'] === 'superadmin'): ?>
            <a href="kelola_users.php" class="flex items-center gap-3 px-4 py-3 <?= $active_menu === 'kelola_users' ? 'bg-accent/10 text-accent font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium' ?> rounded-stitch transition-all group">
                <i class="ph ph-users text-xl group-hover:scale-110 transition-transform"></i> Kelola Panitia
            </a>
            <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 <?= $active_menu === 'pengaturan' ? 'bg-accent/10 text-accent font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800 font-medium' ?> rounded-stitch transition-all group">
                <i class="ph ph-gear-six text-xl group-hover:scale-110 transition-transform"></i> Pengaturan PPDB
            </a>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="p-6 border-t border-slate-100/60 space-y-2 bg-slate-50/50">
        <a href="../index.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 hover:bg-white hover:text-slate-800 font-medium rounded-stitch transition-all group">
            <i class="ph ph-house text-xl group-hover:scale-110 transition-transform"></i> Halaman Depan
        </a>
        <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 hover:text-red-600 font-medium rounded-stitch transition-all group">
            <i class="ph ph-sign-out text-xl group-hover:scale-110 transition-transform"></i> Keluar Sistem
        </a>
    </div>
</aside>
