<?php
session_start();
include 'config/helpers.php';
include 'config/koneksi.php';

function getSetting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT setting_value FROM ppdb_settings WHERE setting_key=?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: $default;
}

$banner_teks = getSetting($pdo, 'banner_teks', 'Penerimaan Tahun 2026/2027 Dibuka');

// Fallback default
$default_persyaratan = [
    ['icon'=>'ph-certificate','judul'=>'Ijazah / SKL','desc'=>'Scan dokumen asli atau fotokopi legalisir dari SD/MI asal.'],
    ['icon'=>'ph-users-three','judul'=>'Kartu Keluarga','desc'=>'Scan KK asli terbaru dengan NIK siswa dan nama orang tua jelas.'],
    ['icon'=>'ph-user-focus','judul'=>'Pas Foto 3x4','desc'=>'Foto formal latar merah/biru, format JPG/PNG.'],
];
$default_jadwal = [
    ['tanggal'=>'1 Mei - 30 Jun','nama'=>'Pendaftaran Daring','desc'=>'Buat akun, isi biodata, upload berkas via portal.','style'=>'normal'],
    ['tanggal'=>'5 Juli','nama'=>'Pengumuman','desc'=>'Hasil verifikasi diumumkan di halaman pengumuman.','style'=>'accent'],
    ['tanggal'=>'6 - 10 Juli','nama'=>'Daftar Ulang','desc'=>'Serahkan berkas fisik ke madrasah.','style'=>'normal'],
];

$db_persyaratan = getSetting($pdo, 'persyaratan_json');
$db_jadwal = getSetting($pdo, 'jadwal_json');

$persyaratan = $db_persyaratan ? json_decode($db_persyaratan, true) : $default_persyaratan;
$jadwal = $db_jadwal ? json_decode($db_jadwal, true) : $default_jadwal;

$stmt_total = $pdo->query("SELECT COUNT(*) FROM users_siswa");
$total_siswa = $stmt_total->fetchColumn();

$stmt_lulus = $pdo->prepare("SELECT COUNT(*) FROM users_siswa WHERE status='diterima'");
$stmt_lulus->execute();
$total_lulus = $stmt_lulus->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB Online | MTs PP DDI Al-Barakah</title>
    <meta name="description" content="Sistem Penerimaan Peserta Didik Baru MTs PP DDI Al-Barakah Teteaji. Daftar online, pantau status, dan cetak bukti pendaftaran.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
    tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{accent:'#10b27c',accentDark:'#0d9466',surface:'#f8faf9'},borderRadius:{stitch:'14px'}}}}
    </script>
    <style>
    html{scroll-behavior:smooth}
    *{-webkit-tap-highlight-color:transparent}
    .glass{background:rgba(255,255,255,.75);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3)}
    .glass-dark{background:rgba(15,23,42,.6);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
    .btn-p{background:#10b27c;color:#fff;transition:all .35s cubic-bezier(.16,1,.3,1)}
    .btn-p:hover{background:#0d9466;transform:translateY(-2px);box-shadow:0 12px 28px -6px rgba(16,178,124,.45)}
    .btn-p:active{transform:scale(.97)}
    .hero-img{object-fit:cover;filter:brightness(.55);transition:transform 8s ease}
    .hero-img:hover{transform:scale(1.05)}
    .card-lift{transition:all .4s cubic-bezier(.16,1,.3,1)}
    .card-lift:hover{transform:translateY(-6px);box-shadow:0 20px 40px -12px rgba(0,0,0,.1)}
    .stat-num{font-variant-numeric:tabular-nums}
    @keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
    .anim-up{animation:fadeUp .7s cubic-bezier(.16,1,.3,1) both}
    .anim-d1{animation-delay:.1s}.anim-d2{animation-delay:.2s}.anim-d3{animation-delay:.3s}.anim-d4{animation-delay:.4s}
    @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .float{animation:float 4s ease-in-out infinite}
    .gallery-img{aspect-ratio:4/3;object-fit:cover;border-radius:16px;transition:all .5s cubic-bezier(.16,1,.3,1)}
    .gallery-img:hover{filter:brightness(1);transform:scale(1.03)}
    .hamburger span{display:block;width:22px;height:2px;background:#334155;border-radius:2px;transition:all .3s ease}
    .hamburger.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
    .hamburger.active span:nth-child(2){opacity:0}
    .hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px)}
    </style>
</head>
<body class="text-slate-800 antialiased bg-surface selection:bg-accent selection:text-white overflow-x-hidden">

<!-- NAVBAR -->
<nav id="navbar" class="fixed w-full z-50 px-4 md:px-6 py-3 transition-all duration-500">
    <div class="max-w-6xl mx-auto glass rounded-2xl px-4 md:px-6 py-3 flex justify-between items-center shadow-sm">
        <a href="index.php" class="flex items-center gap-2.5 shrink-0">
            <img src="assets/img/logo.png" alt="Logo" class="w-10 h-10 md:w-11 md:h-11 object-contain">
            <div class="leading-tight">
                <span class="font-bold text-sm md:text-base tracking-tight block">MTs Al-Barakah</span>
                <span class="text-[10px] text-slate-400 font-medium hidden sm:block">PP DDI Teteaji</span>
            </div>
        </a>
        <div class="hidden lg:flex items-center gap-7 text-sm font-medium text-slate-500">
            <a href="#alur" class="hover:text-accent transition-colors">Alur</a>
            <a href="#syarat" class="hover:text-accent transition-colors">Persyaratan</a>
            <a href="#jadwal" class="hover:text-accent transition-colors">Jadwal</a>
            <a href="#galeri" class="hover:text-accent transition-colors">Galeri</a>
            <a href="#kontak" class="hover:text-accent transition-colors">Kontak</a>
            <a href="pengumuman.php" class="hover:text-accent transition-colors">Pengumuman</a>
        </div>
        <div class="flex items-center gap-2">
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="<?= $_SESSION['role']==='admin'?'admin/dashboard.php':'siswa/dashboard.php' ?>" class="btn-p rounded-xl px-4 md:px-5 py-2.5 text-sm font-semibold flex items-center gap-2">
                <i class="ph ph-squares-four"></i> <span class="hidden sm:inline">Dashboard</span>
            </a>
            <?php else: ?>
            <a href="auth/login.php" class="text-sm font-semibold text-slate-600 hover:text-accent transition-colors px-3 py-2 hidden sm:inline-block">Masuk</a>
            <a href="auth/register.php" class="btn-p rounded-xl px-4 md:px-5 py-2.5 text-sm font-semibold">Daftar</a>
            <?php endif; ?>
            <button onclick="toggleMobileMenu()" class="hamburger lg:hidden flex flex-col gap-1.5 p-2 ml-1" id="hamburgerBtn" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="lg:hidden hidden mt-2 glass rounded-2xl p-5 shadow-lg max-w-6xl mx-auto">
        <div class="flex flex-col gap-1">
            <a href="#alur" onclick="closeMobile()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-list-numbers text-lg"></i> Alur Pendaftaran</a>
            <a href="#syarat" onclick="closeMobile()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-clipboard-text text-lg"></i> Persyaratan</a>
            <a href="#jadwal" onclick="closeMobile()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-calendar text-lg"></i> Jadwal</a>
            <a href="#galeri" onclick="closeMobile()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-images text-lg"></i> Galeri</a>
            <a href="#kontak" onclick="closeMobile()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-envelope-simple text-lg"></i> Kontak</a>
            <a href="pengumuman.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-megaphone text-lg"></i> Pengumuman</a>
            <hr class="border-slate-200 my-2">
            <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="auth/login.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100 font-medium transition-colors"><i class="ph ph-sign-in text-lg"></i> Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="relative min-h-[100svh] flex items-end md:items-center overflow-hidden">
    <img src="assets/img/hero-gedung.png" alt="Gedung MTs PP DDI Al-Barakah" class="absolute inset-0 w-full h-full hero-img">
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/70 to-slate-900/30"></div>
    <div class="relative z-10 w-full px-5 md:px-8 pb-12 pt-28 md:py-0">
        <div class="max-w-6xl mx-auto">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-accent/20 text-accent font-semibold text-xs uppercase tracking-widest mb-5 border border-accent/30 anim-up">
                    <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span> <?= htmlspecialchars($banner_teks) ?>
                </div>
                <h1 class="text-[clamp(2rem,7vw,3.8rem)] font-extrabold leading-[1.08] tracking-tight text-white mb-5 anim-up anim-d1">
                    Langkah Awal Menuju Pendidikan <span class="text-accent">Berkarakter</span>
                </h1>
                <p class="text-base md:text-lg text-slate-300 max-w-lg mb-8 leading-relaxed font-light anim-up anim-d2">
                    Daftar secara daring, pantau status verifikasi, dan lengkapi berkas — semua tanpa harus datang ke sekolah.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 anim-up anim-d3">
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?= $_SESSION['role']==='admin'?'admin/dashboard.php':'siswa/dashboard.php' ?>" class="btn-p rounded-xl px-7 py-4 font-semibold flex items-center justify-center gap-2 text-base">
                        Dashboard Saya <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                    <?php else: ?>
                    <a href="auth/register.php" class="btn-p rounded-xl px-7 py-4 font-semibold flex items-center justify-center gap-2 text-base w-full sm:w-auto">
                        Mulai Pendaftaran <i class="ph ph-arrow-right font-bold"></i>
                    </a>
                    <a href="pengumuman.php" class="rounded-xl px-7 py-4 font-semibold w-full sm:w-auto glass text-white hover:bg-white/20 transition-all flex items-center justify-center gap-2 text-base">
                        <i class="ph ph-magnifying-glass"></i> Cek Kelulusan
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Stats Floating -->
            <div class="flex gap-6 mt-10 anim-up anim-d4">
                <div>
                    <p class="text-2xl md:text-3xl font-extrabold text-white stat-num"><?= $total_siswa ?>+</p>
                    <p class="text-xs text-slate-400 font-medium">Total Pendaftar</p>
                </div>
                <div class="w-px bg-white/20"></div>
                <div>
                    <p class="text-2xl md:text-3xl font-extrabold text-accent stat-num"><?= $total_lulus ?></p>
                    <p class="text-xs text-slate-400 font-medium">Diterima</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ALUR -->
<section id="alur" class="py-16 md:py-24 px-5 md:px-8 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs font-bold text-accent uppercase tracking-[.2em] mb-3">Alur Pendaftaran</p>
            <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight text-slate-900">Tiga Langkah Mudah</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="card-lift bg-surface border border-slate-100 rounded-2xl p-7 relative overflow-hidden group">
                <div class="text-[5rem] font-black text-slate-100 absolute -top-4 -right-2 leading-none select-none group-hover:text-accent/10 transition-colors">1</div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center text-accent text-2xl mb-5"><i class="ph ph-user-plus"></i></div>
                    <h3 class="font-bold text-lg mb-2 text-slate-800">Buat Akun & Isi Data</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Daftar dengan NISN, lengkapi biodata pribadi dan data orang tua secara bertahap.</p>
                </div>
            </div>
            <div class="card-lift bg-surface border border-slate-100 rounded-2xl p-7 relative overflow-hidden group">
                <div class="text-[5rem] font-black text-slate-100 absolute -top-4 -right-2 leading-none select-none group-hover:text-accent/10 transition-colors">2</div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center text-accent text-2xl mb-5"><i class="ph ph-upload-simple"></i></div>
                    <h3 class="font-bold text-lg mb-2 text-slate-800">Upload Dokumen</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Unggah Pas Foto, Kartu Keluarga, dan Ijazah/SKL dalam format JPG atau PDF.</p>
                </div>
            </div>
            <div class="card-lift bg-surface border border-slate-100 rounded-2xl p-7 relative overflow-hidden group">
                <div class="text-[5rem] font-black text-slate-100 absolute -top-4 -right-2 leading-none select-none group-hover:text-accent/10 transition-colors">3</div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-accent/10 rounded-xl flex items-center justify-center text-accent text-2xl mb-5"><i class="ph ph-check-circle"></i></div>
                    <h3 class="font-bold text-lg mb-2 text-slate-800">Verifikasi & Lulus</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Pantau status di dashboard. Cetak bukti kelulusan saat dinyatakan diterima.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PERSYARATAN -->
<section id="syarat" class="py-16 md:py-24 px-5 md:px-8 bg-surface">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs font-bold text-accent uppercase tracking-[.2em] mb-3">Persyaratan</p>
            <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight text-slate-900">Siapkan Berkas Ini</h2>
        </div>
        <div class="space-y-4">
            <?php foreach($persyaratan as $i => $item): ?>
            <div class="card-lift flex items-start gap-4 bg-white border border-slate-100 rounded-2xl p-6">
                <div class="w-11 h-11 bg-accent/10 rounded-xl flex items-center justify-center text-accent shrink-0 text-xl">
                    <i class="ph <?= htmlspecialchars($item['icon']) ?>"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800"><?= htmlspecialchars($item['judul']) ?></h4>
                    <p class="text-sm text-slate-500 mt-1 leading-relaxed"><?= htmlspecialchars($item['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- JADWAL -->
<section id="jadwal" class="py-16 md:py-24 px-5 md:px-8 bg-white">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs font-bold text-accent uppercase tracking-[.2em] mb-3">Timeline</p>
            <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight text-slate-900">Jadwal PPDB</h2>
        </div>
        <div class="relative">
            <div class="absolute left-5 md:left-6 top-0 bottom-0 w-px bg-slate-200"></div>
            <div class="space-y-6">
                <?php foreach($jadwal as $j): $isA = (isset($j['style']) && $j['style'] === 'accent'); ?>
                <div class="relative pl-14 md:pl-16">
                    <div class="absolute left-3 md:left-4 top-1 w-4 h-4 rounded-full border-[3px] <?= $isA ? 'border-accent bg-accent/20 shadow-lg shadow-accent/30 animate-pulse' : 'border-slate-300 bg-white' ?>"></div>
                    <div class="<?= $isA ? 'bg-accent/5 border-accent/20' : 'bg-surface border-slate-100' ?> border rounded-2xl p-5 card-lift">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-2 <?= $isA ? 'bg-accent/10 text-accent' : 'bg-slate-100 text-slate-600' ?>"><?= htmlspecialchars($j['tanggal'] ?? $j['tgl'] ?? '') ?></span>
                        <h4 class="font-bold text-slate-800 text-base"><?= htmlspecialchars($j['nama'] ?? '') ?></h4>
                        <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($j['desc'] ?? '') ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- GALERI -->
<section id="galeri" class="py-16 md:py-24 px-5 md:px-8 bg-surface">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs font-bold text-accent uppercase tracking-[.2em] mb-3">Galeri</p>
            <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight text-slate-900">Suasana Madrasah Kami</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
            <div class="col-span-2 md:col-span-2 md:row-span-2">
                <img src="assets/img/foto-bersama.jpg" alt="Foto bersama siswa berprestasi" class="gallery-img w-full h-full object-cover brightness-90 hover:brightness-100" loading="lazy">
            </div>
            <div>
                <img src="assets/img/kelas-1.jpg" alt="Kegiatan belajar mengajar" class="gallery-img w-full h-full brightness-90 hover:brightness-100" loading="lazy">
            </div>
            <div>
                <img src="assets/img/kelas-2.jpg" alt="Siswa mengerjakan ujian" class="gallery-img w-full h-full brightness-90 hover:brightness-100" loading="lazy">
            </div>
            <div class="col-span-2 md:col-span-1">
                <img src="assets/img/kelas-3.jpg" alt="Suasana kelas" class="gallery-img w-full brightness-90 hover:brightness-100" loading="lazy">
            </div>
            <div class="col-span-2">
                <img src="assets/img/hero-gedung.png" alt="Gedung MTs PP DDI Al-Barakah" class="gallery-img w-full h-48 md:h-56 brightness-90 hover:brightness-100" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- CONTACT & MAPS -->
<section id="kontak" class="py-16 md:py-24 px-5 md:px-8 bg-panel">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Teks & Info -->
            <div class="space-y-8 animate-fade-in-up">
                <div>
                    <p class="text-xs font-bold text-accent uppercase tracking-[.2em] mb-3">Kontak Kami</p>
                    <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">Hubungi Kami Untuk Informasi Lebih Lanjut</h2>
                    <p class="text-slate-500 mt-4 text-base md:text-lg max-w-lg">Panitia PPDB kami siap membantu menjawab pertanyaan Anda seputar proses pendaftaran dan persyaratan.</p>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-surface rounded-2xl border border-slate-100 transition-all hover:border-accent/30 group">
                        <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center text-2xl group-hover:bg-accent group-hover:text-white transition-all">
                            <i class="ph ph-map-pin"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Lokasi Sekolah</p>
                            <p class="font-bold text-slate-800 text-sm md:text-base">Jl. M. Junaid Hanzah No. 9, Teteaji</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 bg-surface rounded-2xl border border-slate-100 transition-all hover:border-accent/30 group">
                        <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center text-2xl group-hover:bg-accent group-hover:text-white transition-all">
                            <i class="ph ph-phone"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telepon / WhatsApp</p>
                            <p class="font-bold text-slate-800 text-sm md:text-base">+62 823-4567-8910</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 p-4 bg-surface rounded-2xl border border-slate-100 transition-all hover:border-accent/30 group">
                        <div class="w-12 h-12 rounded-xl bg-accent/10 text-accent flex items-center justify-center text-2xl group-hover:bg-accent group-hover:text-white transition-all">
                            <i class="ph ph-envelope"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email Resmi</p>
                            <p class="font-bold text-slate-800 text-sm md:text-base">ppdb@mtsalbarakah.sch.id</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Google Maps -->
            <div class="rounded-[32px] overflow-hidden shadow-2xl shadow-slate-200 border-8 border-white animate-fade-in-up" style="animation-delay: 200ms">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15915.632296720235!2d119.8943714!3d-4.2384752!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2d959587425121df%3A0xe7449231f87e5b6b!2sTeteaji%2C%20Tellu%20Limpoe%2C%20Kabupaten%20Sidenreng%20Rappang%2C%20Sulawesi%20Selatan!5e0!3m2!1sid!2sid!4v1714650000000!5m2!1sid!2sid" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    class="grayscale contrast-125 hover:grayscale-0 transition-all duration-1000">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 md:py-24 px-5 md:px-8 bg-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 right-0 w-96 h-96 bg-accent rounded-full blur-[150px]"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-400 rounded-full blur-[120px]"></div>
    </div>
    <div class="max-w-3xl mx-auto text-center relative z-10">
        <h2 class="text-2xl md:text-4xl font-extrabold text-white tracking-tight mb-4">Siap Bergabung Bersama Kami?</h2>
        <p class="text-slate-400 mb-8 text-base md:text-lg">Pendaftaran mudah, cepat, dan sepenuhnya online. Mulai sekarang.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="auth/register.php" class="btn-p rounded-xl px-8 py-4 font-semibold text-base flex items-center justify-center gap-2">
                Daftar Sekarang <i class="ph ph-arrow-right font-bold"></i>
            </a>
            <a href="pengumuman.php" class="rounded-xl px-8 py-4 font-semibold text-base border border-white/20 text-white hover:bg-white/10 transition-all flex items-center justify-center gap-2">
                <i class="ph ph-magnifying-glass"></i> Cek Kelulusan
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-slate-950 py-10 px-5 text-center">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-center gap-2.5 mb-4">
            <img src="assets/img/logo.png" alt="Logo" class="w-8 h-8 object-contain opacity-60">
            <span class="text-sm font-semibold text-slate-500">MTs PP DDI Al-Barakah</span>
        </div>
        <p class="text-slate-600 text-xs">Jl. M. Junaid Hanzah No. 9, Teteaji</p>
        <p class="text-slate-700 text-xs mt-4">&copy; 2026 Sistem Informasi PPDB Digital. All rights reserved.</p>
    </div>
</footer>

<script>
function toggleMobileMenu(){
    const m=document.getElementById('mobileMenu');
    const b=document.getElementById('hamburgerBtn');
    m.classList.toggle('hidden');
    b.classList.toggle('active');
}
function closeMobile(){
    document.getElementById('mobileMenu').classList.add('hidden');
    document.getElementById('hamburgerBtn').classList.remove('active');
}
// Navbar scroll effect
let lastY=0;
const nav=document.getElementById('navbar');
window.addEventListener('scroll',()=>{
    const y=window.scrollY;
    if(y>80){nav.classList.add('py-1.5');nav.classList.remove('py-3')}
    else{nav.classList.remove('py-1.5');nav.classList.add('py-3')}
    lastY=y;
},{passive:true});
</script>
</body>
</html>