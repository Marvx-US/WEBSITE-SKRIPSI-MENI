<?php
include '../config/helpers.php';
include '../config/koneksi.php';

$msg = "";
$rate_key = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if (isset($_POST['login'])) {
    csrf_check(); 

    $rate_limit = rate_limit_check($rate_key, 5, 300); 
    if (!$rate_limit['allowed']) {
        $msg = $rate_limit['message'];
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Query ke tabel siswa (hanya siswa yang boleh login di sini)

        
        $stmt = $pdo->prepare("SELECT * FROM users_siswa WHERE nisn = ?");
        $stmt->execute([$username]);
        $data_siswa = $stmt->fetch();

        if ($data_siswa && password_verify($password, $data_siswa['password'])) {
            rate_limit_clear($rate_key);
            $_SESSION['user_id'] = $data_siswa['id'];
            $_SESSION['nama'] = $data_siswa['nama_lengkap'] ?? $data_siswa['username'];
            $_SESSION['role'] = 'siswa';
            header("Location: ../siswa/dashboard.php");
            exit;
        }

        rate_limit_record($rate_key);
        $msg = "Login gagal! ID Pengguna atau Kata Sandi salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | PPDB MTs Al-Barakah</title>
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
    .btn-auth{background:#0f172a;color:#fff;padding:1rem;border-radius:16px;font-weight:600;width:100%;transition:all .35s cubic-bezier(.16,1,.3,1);display:flex;align-items:center;justify-content:center;gap:.5rem}
    .btn-auth:hover{background:#1e293b;transform:translateY(-2px);box-shadow:0 12px 24px -8px rgba(15,23,42,.35)}
    .btn-auth:active{transform:scale(.98)}
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased selection:bg-accent selection:text-white">

<div class="min-h-[100svh] flex flex-col lg:flex-row">
    <!-- LEFT: IMAGE PANEL -->
    <div class="relative w-full lg:w-1/2 h-56 sm:h-72 lg:h-auto shrink-0 overflow-hidden">
        <img src="../assets/img/kelas-1.jpg" alt="Suasana Belajar MTs Al-Barakah" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b lg:bg-gradient-to-r from-slate-900/80 via-slate-900/50 to-transparent"></div>
        <div class="relative z-10 p-6 lg:p-12 flex flex-col justify-end h-full">
            <a href="../index.php" class="flex items-center gap-2.5 mb-4 lg:mb-auto">
                <img src="../assets/img/logo.png" alt="Logo" class="w-10 h-10 object-contain">
                <span class="text-white font-bold text-sm">MTs Al-Barakah</span>
            </a>
            <div class="hidden lg:block">
                <h2 class="text-3xl font-extrabold text-white leading-tight tracking-tight mb-3">Pendidikan<br>Berkarakter Islami</h2>
                <p class="text-white/60 text-sm max-w-sm leading-relaxed">MTs PP DDI Al-Barakah Teteaji membentuk generasi yang berilmu, berakhlak, dan berprestasi.</p>
            </div>
        </div>
    </div>

    <!-- RIGHT: FORM PANEL -->
    <div class="flex-1 flex items-center justify-center px-6 py-10 lg:py-16 lg:px-16">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <!-- BREADCRUMB -->
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-6">
                    <a href="../index.php" class="hover:text-accent transition-colors">Beranda</a>
                    <i class="ph ph-caret-right"></i>
                    <span class="text-slate-900">Masuk</span>
                </nav>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">Selamat Datang</h1>
                <p class="text-slate-500 mt-2 text-sm">Masuk dengan NISN dan Kata Sandi</p>
            </div>

            <?php if($msg): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-sm font-medium flex items-center gap-3">
                <i class="ph ph-warning-circle text-xl shrink-0"></i> <?= $msg ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">ID Pengguna</label>
                    <div class="relative">
                        <i class="ph ph-identification-card absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="text" name="username" required placeholder="Ketik NISN Anda" autocomplete="off" class="input-auth">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <i class="ph ph-lock-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="password" name="password" id="passwordField" required placeholder="••••••••" class="input-auth">
                        <button type="button" onclick="togglePw()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors" aria-label="Toggle password">
                            <i class="ph ph-eye text-lg" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-auth mt-2">
                    Masuk <i class="ph ph-sign-in text-lg"></i>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-500">
                    Belum punya akun? <a href="register.php" class="text-accent font-semibold hover:underline underline-offset-4">Daftar sekarang</a>
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
</script>
</body>
</html>