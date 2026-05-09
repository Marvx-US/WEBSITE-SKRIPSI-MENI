<?php
include '../config/helpers.php';
include '../config/koneksi.php';

$msg = ""; $type = "";
if (isset($_POST['register'])) {
    csrf_check(); 

    $nisn     = trim($_POST['nisn'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $raw_pass = $_POST['password'] ?? '';
    
    
    if (strlen($raw_pass) < 6) {
        $msg = "Kata sandi minimal 6 karakter!";
        $type = "error";
    } elseif (!preg_match('/^\d{10}$/', $nisn)) {
        $msg = "NISN harus terdiri dari tepat 10 digit angka!";
        $type = "error";
    } else {
        $password = password_hash($raw_pass, PASSWORD_DEFAULT);
        $role     = 'siswa';

        
        $stmt_cek = $pdo->prepare("SELECT nisn FROM users_siswa WHERE nisn = ?");
        $stmt_cek->execute([$nisn]);
        
        if ($stmt_cek->rowCount() > 0) {
            $msg = "NISN sudah terdaftar di sistem!";
            $type = "error";
        } else {
            // Ambil tahun_ajaran aktif dari ppdb_settings
            $stmt_ta = $pdo->prepare("SELECT setting_value FROM ppdb_settings WHERE setting_key = 'tahun_ajaran'");
            $stmt_ta->execute();
            $tahun_ajaran = $stmt_ta->fetchColumn() ?: date('Y') . '/' . (date('Y') + 1);

            $stmt_insert = $pdo->prepare("INSERT INTO users_siswa (nisn, nama_lengkap, password, role, tahun_ajaran) VALUES (?, ?, ?, ?, ?)");
            if ($stmt_insert->execute([$nisn, $nama, $password, $role, $tahun_ajaran])) {
                $msg = "Registrasi berhasil! Silakan masuk.";
                $type = "success";
            } else {
                $msg = "Terjadi kesalahan sistem.";
                $type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | PPDB MTs Al-Barakah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{accent:'#10b27c',accentDark:'#0d9466',surface:'#f8faf9'}}}}</script>
    <style>
    *{-webkit-tap-highlight-color:transparent}
    .input-auth{width:100%;background:#f8fafc;border:2px solid #e2e8f0;padding:.875rem 1rem .875rem 2.75rem;border-radius:16px;outline:none;font-weight:500;transition:all .3s}
    .input-auth:focus{background:#fff;border-color:#10b27c;box-shadow:0 0 0 4px rgba(16,178,124,.08)}
    .input-auth::placeholder{color:#94a3b8}
    .btn-auth{background:#10b27c;color:#fff;padding:1rem;border-radius:16px;font-weight:600;width:100%;transition:all .35s cubic-bezier(.16,1,.3,1);display:flex;align-items:center;justify-content:center;gap:.5rem}
    .btn-auth:hover{background:#0d9466;transform:translateY(-2px);box-shadow:0 12px 24px -8px rgba(16,178,124,.4)}
    .btn-auth:active{transform:scale(.98)}
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased selection:bg-accent selection:text-white">

<div class="min-h-[100svh] flex flex-col lg:flex-row">
    <!-- LEFT: IMAGE -->
    <div class="relative w-full lg:w-1/2 h-56 sm:h-72 lg:h-auto shrink-0 overflow-hidden">
        <img src="../assets/img/kelas-1.jpg" alt="Suasana Belajar MTs Al-Barakah" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b lg:bg-gradient-to-r from-slate-900/80 via-slate-900/50 to-transparent"></div>
        <div class="relative z-10 p-6 lg:p-12 flex flex-col justify-end h-full">
            <a href="../index.php" class="flex items-center gap-2.5 mb-4 lg:mb-auto">
                <img src="../assets/img/logo.png" alt="Logo" class="w-10 h-10 object-contain">
                <span class="text-white font-bold text-sm">MTs Al-Barakah</span>
            </a>
            <div class="hidden lg:block">
                <h2 class="text-3xl font-extrabold text-white leading-tight tracking-tight mb-3">Mulai Perjalanan<br>Pendidikanmu</h2>
                <p class="text-white/60 text-sm max-w-sm leading-relaxed">Buat akun PPDB untuk mendaftar sebagai calon peserta didik baru MTs PP DDI Al-Barakah.</p>
            </div>
        </div>
    </div>

    <!-- RIGHT: FORM -->
    <div class="flex-1 flex items-center justify-center px-6 py-10 lg:py-16 lg:px-16">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <!-- BREADCRUMB -->
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-6">
                    <a href="../index.php" class="hover:text-accent transition-colors">Beranda</a>
                    <i class="ph ph-caret-right"></i>
                    <span class="text-slate-900">Daftar</span>
                </nav>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">Buat Akun Baru</h1>
                <p class="text-slate-500 mt-2 text-sm">Gunakan NISN yang valid sebagai identitas utama Anda</p>
            </div>

            <?php if($msg && $type === 'error'): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-sm font-medium flex items-center gap-3">
                <i class="ph ph-warning-circle text-xl shrink-0"></i> <?= $msg ?>
            </div>
            <?php elseif($msg && $type === 'success'): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl text-sm font-medium flex items-center gap-3">
                <i class="ph ph-check-circle text-xl shrink-0"></i> <?= $msg ?>
                <a href="login.php" class="ml-auto text-accent font-bold text-xs hover:underline shrink-0">Masuk →</a>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">NISN <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="ph ph-identification-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="text" name="nisn" required placeholder="10 digit angka NISN" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" autocomplete="off" class="input-auth">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="text" name="nama" required placeholder="Sesuai Ijazah / Akte" autocomplete="off" class="input-auth">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kata Sandi <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <i class="ph ph-lock-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="password" name="password" id="passwordField" required placeholder="Minimal 6 karakter" minlength="6" class="input-auth">
                        <button type="button" onclick="togglePw()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors" aria-label="Toggle password">
                            <i class="ph ph-eye text-lg" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" name="register" class="btn-auth mt-2">
                    Daftar Sekarang <i class="ph ph-arrow-right font-bold"></i>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-500">
                    Sudah punya akun? <a href="login.php" class="text-accent font-semibold hover:underline underline-offset-4">Masuk di sini</a>
                </p>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-100 text-center hidden lg:block">
                <a href="../index.php" class="text-xs text-slate-400 hover:text-accent transition-colors inline-flex items-center gap-1.5">
                    <i class="ph ph-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(){
    const f=document.getElementById('passwordField');
    const i=document.getElementById('eyeIcon');
    if(f.type==='password'){f.type='text';i.className='ph ph-eye-slash text-lg'}
    else{f.type='password';i.className='ph ph-eye text-lg'}
}
// Only allow digits for NISN
document.querySelector('input[name="nisn"]').addEventListener('input',function(){this.value=this.value.replace(/\D/g,'').slice(0,10)});
</script>
</body>
</html>