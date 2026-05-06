<?php

session_start();
include 'config/helpers.php';
include 'config/koneksi.php';


$pengumuman_list = $pdo->query("SELECT * FROM ppdb_pengumuman ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title>Cek Pengumuman Kelulusan | PPDB MTs PP DDI Al-Barakah</title>
    <meta name="description" content="Cek status kelulusan PPDB MTs PP DDI Al-Barakah secara online. Masukkan NISN untuk melihat hasil seleksi.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','sans-serif']},colors:{accent:'#10b27c',accentDark:'#0d9466',surface:'#f8faf9'},borderRadius:{stitch:'14px'}}}}</script>
    <style>
    html{scroll-behavior:smooth}*{-webkit-tap-highlight-color:transparent}
    .glass{background:rgba(255,255,255,.75);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.3)}
    .btn-p{background:#10b27c;color:#fff;transition:all .35s cubic-bezier(.16,1,.3,1)}
    .btn-p:hover{background:#0d9466;transform:translateY(-2px);box-shadow:0 12px 28px -6px rgba(16,178,124,.45)}
    .btn-p:active{transform:scale(.97)}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    @keyframes pulseGlow{0%,100%{box-shadow:0 0 0 0 rgba(16,178,124,.4)}50%{box-shadow:0 0 0 12px rgba(16,178,124,0)}}
    @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
    .animate-fade-in-up{animation:fadeInUp .6s cubic-bezier(.16,1,.3,1) forwards}
    .animate-pulse-glow{animation:pulseGlow 2s infinite}
    .skeleton{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px}
    .nisn-input:focus{border-color:#10b27c;box-shadow:0 0 0 4px rgba(16,178,124,.1);outline:none}
    .nisn-input::placeholder{color:#94a3b8}
    .hamburger span{display:block;width:22px;height:2px;background:#334155;border-radius:2px;transition:all .3s ease}
    .hamburger.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
    .hamburger.active span:nth-child(2){opacity:0}
    .hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px)}
    </style>
</head>
<body class="text-slate-800 antialiased bg-surface min-h-screen flex flex-col selection:bg-accent selection:text-white overflow-x-hidden">

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
            <a href="index.php#alur" class="hover:text-accent transition-colors">Alur</a>
            <a href="index.php#syarat" class="hover:text-accent transition-colors">Persyaratan</a>
            <a href="index.php#jadwal" class="hover:text-accent transition-colors">Jadwal</a>
            <a href="index.php#galeri" class="hover:text-accent transition-colors">Galeri</a>
            <a href="pengumuman.php" class="text-accent font-semibold">Pengumuman</a>
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
    <div id="mobileMenu" class="lg:hidden hidden mt-2 glass rounded-2xl p-5 shadow-lg max-w-6xl mx-auto">
        <div class="flex flex-col gap-1">
            <a href="index.php#alur" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-list-numbers text-lg"></i> Alur Pendaftaran</a>
            <a href="index.php#syarat" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-clipboard-text text-lg"></i> Persyaratan</a>
            <a href="index.php#jadwal" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-calendar text-lg"></i> Jadwal</a>
            <a href="index.php#galeri" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-accent/5 hover:text-accent font-medium transition-colors"><i class="ph ph-images text-lg"></i> Galeri</a>
            <a href="pengumuman.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-accent/5 text-accent font-semibold transition-colors"><i class="ph ph-megaphone text-lg"></i> Pengumuman</a>
            <hr class="border-slate-200 my-2">
            <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="auth/login.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-100 font-medium transition-colors"><i class="ph ph-sign-in text-lg"></i> Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center pt-32 pb-20 px-6">
        <div class="max-w-xl w-full mx-auto text-center">

            <!-- BREADCRUMB -->
            <nav class="flex items-center justify-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-8">
                <a href="index.php" class="hover:text-accent transition-colors">Beranda</a>
                <i class="ph ph-caret-right"></i>
                <span class="text-slate-900">Pengumuman</span>
            </nav>

            <!-- Header -->
            <div class="mb-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-accent/10 text-accent font-semibold text-xs uppercase tracking-widest mb-6 border border-accent/20">
                    <i class="ph ph-magnifying-glass"></i> Pengumuman Kelulusan
                </div>
                <h1 class="text-[clamp(2rem,5vw,3rem)] font-bold leading-[1.15] tracking-tight mb-4 text-slate-900">
                    Cek Status<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-accent to-emerald-400">Pendaftaran Anda</span>
                </h1>
                <p class="text-slate-500 text-base md:text-lg leading-relaxed font-light max-w-md mx-auto">
                    Masukkan Nomor Induk Siswa Nasional (NISN) 10 digit Anda untuk melihat status pendaftaran secara real-time.
                </p>
            </div>

            <!-- Search Card -->
            <div class="glass rounded-[24px] p-6 md:p-8 shadow-sm mb-8">
                <form id="formCekStatus" onsubmit="return cekStatus(event)">
                    <label for="nisn" class="block text-left text-sm font-semibold text-slate-700 mb-3">Nomor Induk Siswa Nasional (NISN)</label>
                    <div class="flex gap-3">
                        <div class="relative flex-1">
                            <i class="ph ph-identification-card absolute left-4 top-1/2 -translate-y-1/2 text-xl text-slate-400"></i>
                            <input
                                type="text"
                                id="nisn"
                                name="nisn"
                                maxlength="10"
                                inputmode="numeric"
                                pattern="[0-9]{10}"
                                placeholder="Masukkan 10 digit NISN"
                                class="nisn-input w-full pl-12 pr-4 py-4 border-2 border-slate-200 rounded-2xl text-base font-medium tracking-wider transition-all duration-300"
                                required
                                autocomplete="off"
                            >
                        </div>
                        <button type="submit" id="btnCari" class="btn-p rounded-2xl px-6 py-4 font-semibold flex items-center gap-2 shrink-0 text-base">
                            <i class="ph ph-magnifying-glass text-xl"></i>
                            <span class="hidden sm:inline">Cari</span>
                        </button>
                    </div>
                    <p class="text-left text-xs text-slate-400 mt-3"><i class="ph ph-info mr-1"></i> NISN dapat dilihat di rapor, ijazah, atau hubungi sekolah asal Anda.</p>
                </form>
            </div>

            <!-- Loading Skeleton (Hidden by default) -->
            <div id="loadingSkeleton" class="hidden animate-fade-in-up">
                <div class="bg-white rounded-[24px] border border-slate-200 p-8 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="skeleton w-16 h-16 rounded-full shrink-0"></div>
                        <div class="flex-1 space-y-3">
                            <div class="skeleton h-5 w-3/4"></div>
                            <div class="skeleton h-4 w-1/2"></div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="skeleton h-4 w-full"></div>
                        <div class="skeleton h-4 w-5/6"></div>
                        <div class="skeleton h-12 w-1/3 mt-4"></div>
                    </div>
                </div>
            </div>

            <!-- Error Message (Hidden by default) -->
            <div id="errorContainer" class="hidden animate-fade-in-up">
                <div class="bg-red-50 rounded-[24px] border-2 border-red-200 p-8 text-center">
                    <div class="w-16 h-16 rounded-full bg-red-100 text-red-500 flex items-center justify-center text-3xl mx-auto mb-4">
                        <i class="ph ph-x-circle"></i>
                    </div>
                    <h3 class="font-bold text-lg text-red-800 mb-2">Data Tidak Ditemukan</h3>
                    <p id="errorMessage" class="text-sm text-red-500"></p>
                    <button onclick="resetForm()" class="mt-4 text-sm font-semibold text-red-600 hover:text-red-800 transition-colors underline underline-offset-4">Coba NISN Lain</button>
                </div>
            </div>

            <!-- Result Card (Hidden by default) -->
            <div id="resultContainer" class="hidden animate-fade-in-up">
                <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Status Header -->
                    <div id="statusHeader" class="p-8 text-center">
                        <div id="statusIcon" class="w-20 h-20 rounded-full flex items-center justify-center text-4xl mx-auto mb-5"></div>
                        <h2 id="statusTitle" class="text-2xl font-bold mb-2"></h2>
                        <p id="statusDesc" class="text-sm max-w-sm mx-auto"></p>
                    </div>

                    <!-- Data Grid -->
                    <div class="border-t border-slate-100 p-8">
                        <div class="grid grid-cols-2 gap-6 text-left">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Nama Lengkap</p>
                                <p id="resultNama" class="font-bold text-slate-800 text-base">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">NISN</p>
                                <p id="resultNisn" class="font-bold text-slate-800 font-mono text-base tabular-nums">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Jenis Kelamin</p>
                                <p id="resultJK" class="font-semibold text-slate-600">-</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Asal Sekolah</p>
                                <p id="resultSekolah" class="font-semibold text-slate-600">-</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Tanggal Daftar</p>
                                <p id="resultTglDaftar" class="font-semibold text-slate-600">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pesan Revisi (Hidden by default) -->
                    <div id="revisiContainer" class="hidden border-t border-orange-200 bg-orange-50 p-8">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                                <i class="ph ph-note-pencil"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-orange-800 text-sm mb-2">Catatan Revisi dari Panitia:</h4>
                                <p id="resultRevisi" class="text-sm text-orange-700 leading-relaxed whitespace-pre-line"></p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Footer -->
                    <div class="border-t border-slate-100 p-6 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-xs text-slate-400 text-center sm:text-left">Butuh bantuan? Hubungi panitia PPDB di sekolah.</p>
                        <button onclick="resetForm()" class="text-sm font-semibold text-accent hover:text-accentDark transition-colors flex items-center gap-1">
                            <i class="ph ph-arrow-counter-clockwise"></i> Cek NISN Lain
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Pengumuman Section -->
    <?php if(!empty($pengumuman_list)): ?>
    <section class="max-w-3xl mx-auto px-4 md:px-6 mb-20">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center text-accent">
                <i class="ph ph-megaphone text-xl"></i>
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-800">Informasi & Pengumuman</h2>
        </div>
        
        <div class="space-y-4">
            <?php foreach($pengumuman_list as $p): ?>
            <div class="bg-white p-6 rounded-[24px] border border-slate-200 shadow-sm transition-transform hover:-translate-y-1 duration-300">
                <h3 class="text-lg font-bold text-slate-800 mb-1"><?= htmlspecialchars($p['judul']) ?></h3>
                <p class="text-xs text-slate-400 mb-4 font-medium"><i class="ph ph-calendar-blank"></i> <?= date('d M Y', strtotime($p['tgl_buat'])) ?></p>
                <div class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                    <?= htmlspecialchars($p['isi']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

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
        // Hanya izinkan angka di input NISN
        document.getElementById('nisn').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });

        function cekStatus(e) {
            e.preventDefault();

            const nisn = document.getElementById('nisn').value.trim();
            if (nisn.length !== 10) {
                showError('NISN harus terdiri dari tepat 10 digit angka.');
                return false;
            }

            // Hide previous results, show loading
            hideAll();
            document.getElementById('loadingSkeleton').classList.remove('hidden');
            document.getElementById('btnCari').disabled = true;
            document.getElementById('btnCari').innerHTML = '<i class="ph ph-circle-notch text-xl animate-spin"></i>';

            fetch('cek_status.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: 'nisn=' + encodeURIComponent(nisn)
            })
            .then(response => response.json())
            .then(res => {
                document.getElementById('loadingSkeleton').classList.add('hidden');
                resetButton();

                if (!res.success) {
                    showError(res.message);
                    return;
                }

                showResult(res.data);
            })
            .catch(err => {
                document.getElementById('loadingSkeleton').classList.add('hidden');
                resetButton();
                showError('Terjadi kesalahan jaringan. Silakan coba lagi.');
                console.error('[CekStatus]', err);
            });

            return false;
        }

        function showResult(data) {
            const container = document.getElementById('resultContainer');
            const header = document.getElementById('statusHeader');
            const icon = document.getElementById('statusIcon');
            const title = document.getElementById('statusTitle');
            const desc = document.getElementById('statusDesc');

            // Status config
            const statusConfig = {
                'diterima': {
                    bg: 'bg-emerald-50',
                    iconBg: 'bg-emerald-100 text-emerald-600 animate-pulse-glow',
                    iconClass: 'ph-check-circle',
                    title: 'Selamat! Anda Diterima 🎉',
                    desc: 'Anda resmi diterima sebagai calon peserta didik baru. Silakan lakukan daftar ulang sesuai jadwal yang telah ditentukan.',
                    titleColor: 'text-emerald-800',
                    descColor: 'text-emerald-600'
                },
                'ditolak': {
                    bg: 'bg-red-50',
                    iconBg: 'bg-red-100 text-red-500',
                    iconClass: 'ph-x-circle',
                    title: 'Mohon Maaf, Belum Diterima',
                    desc: 'Berkas pendaftaran Anda belum memenuhi kriteria yang ditetapkan. Hubungi panitia untuk informasi lebih lanjut.',
                    titleColor: 'text-red-800',
                    descColor: 'text-red-500'
                },
                'revisi': {
                    bg: 'bg-orange-50',
                    iconBg: 'bg-orange-100 text-orange-600',
                    iconClass: 'ph-note-pencil',
                    title: 'Berkas Perlu Diperbaiki',
                    desc: 'Panitia mengembalikan berkas Anda untuk diperbaiki. Silakan login dan perbaiki sesuai catatan di bawah.',
                    titleColor: 'text-orange-800',
                    descColor: 'text-orange-600'
                },
                'pending': {
                    bg: 'bg-amber-50',
                    iconBg: 'bg-amber-100 text-amber-600',
                    iconClass: 'ph-hourglass-medium',
                    title: 'Sedang Diproses',
                    desc: 'Berkas pendaftaran Anda sedang dalam antrean verifikasi oleh panitia. Mohon bersabar.',
                    titleColor: 'text-amber-800',
                    descColor: 'text-amber-600'
                }
            };

            const status = data.status || 'pending';
            const config = statusConfig[status] || statusConfig['pending'];

            // Apply status styling
            header.className = 'p-8 text-center ' + config.bg;
            icon.className = 'w-20 h-20 rounded-full flex items-center justify-center text-4xl mx-auto mb-5 ' + config.iconBg;
            icon.innerHTML = '<i class="ph ' + config.iconClass + '"></i>';
            title.className = 'text-2xl font-bold mb-2 ' + config.titleColor;
            title.textContent = config.title;
            desc.className = 'text-sm max-w-sm mx-auto ' + config.descColor;
            desc.textContent = config.desc;

            // Populate data
            document.getElementById('resultNama').textContent = data.nama || '-';
            document.getElementById('resultNisn').textContent = data.nisn || '-';

            const jk = data.jenis_kelamin;
            document.getElementById('resultJK').textContent = jk === 'L' ? 'Laki-Laki' : (jk === 'P' ? 'Perempuan' : (jk || '-'));

            document.getElementById('resultSekolah').textContent = data.asal_sekolah || '-';
            document.getElementById('resultTglDaftar').textContent = formatDate(data.tgl_daftar);

            // Revisi message
            const revisiContainer = document.getElementById('revisiContainer');
            if (status === 'revisi' && data.pesan_revisi) {
                document.getElementById('resultRevisi').textContent = data.pesan_revisi;
                revisiContainer.classList.remove('hidden');
            } else {
                revisiContainer.classList.add('hidden');
            }

            container.classList.remove('hidden');
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorContainer').classList.remove('hidden');
        }

        function hideAll() {
            document.getElementById('resultContainer').classList.add('hidden');
            document.getElementById('errorContainer').classList.add('hidden');
            document.getElementById('loadingSkeleton').classList.add('hidden');
        }

        function resetButton() {
            const btn = document.getElementById('btnCari');
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-magnifying-glass text-xl"></i><span class="hidden sm:inline">Cari</span>';
        }

        function resetForm() {
            hideAll();
            document.getElementById('nisn').value = '';
            document.getElementById('nisn').focus();
        }

        function formatDate(dateStr) {
            if (!dateStr || dateStr === '-') return '-';
            try {
                const d = new Date(dateStr);
                const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
            } catch {
                return dateStr;
            }
        }
    function toggleMobileMenu(){
        const m=document.getElementById('mobileMenu');
        const b=document.getElementById('hamburgerBtn');
        m.classList.toggle('hidden');
        b.classList.toggle('active');
    }
    </script>
</body>
</html>
