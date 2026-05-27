<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

$msg = "";
$rate_key = 'admin_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if (isset($_POST['login'])) {
    csrf_check(); 
    $rate_limit = rate_limit_check($rate_key, 5, 300); 
    if (!$rate_limit['allowed']) {
        $msg = $rate_limit['message'];
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users_admin WHERE username = ?");
        $stmt->execute([$username]);
        $data_admin = $stmt->fetch();

        if ($data_admin && password_verify($password, $data_admin['password'])) {
            rate_limit_clear($rate_key);
            $_SESSION['user_id'] = $data_admin['id'];
            $_SESSION['nama'] = $data_admin['nama_lengkap'] ?? $data_admin['username'];
            $_SESSION['role'] = 'admin';
            $_SESSION['role_admin'] = $data_admin['role'];
            header("Location: dashboard.php");
            exit;
        }

        rate_limit_record($rate_key);
        $msg = "Login gagal! Username atau Kata Sandi salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin | PPDB MTs Al-Barakah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <style>
    *{-webkit-tap-highlight-color:transparent}
    .input-auth{width:100%;background:#f8fafc;border:2px solid #e2e8f0;padding:.875rem 1rem .875rem 2.75rem;border-radius:16px;outline:none;font-weight:500;transition:all .3s}
    .input-auth:focus{background:#fff;border-color:#10b27c;box-shadow:0 0 0 4px rgba(16,178,124,.08)}
    .input-auth::placeholder{color:#94a3b8}
    .btn-auth{background:#0f172a;color:#fff;padding:1rem;border-radius:16px;font-weight:600;width:100%;transition:all .35s cubic-bezier(.16,1,.3,1);display:flex;align-items:center;justify-content:center;gap:.5rem}
    .btn-auth:hover{background:#1e293b;transform:translateY(-2px);box-shadow:0 12px 24px -8px rgba(15,23,42,.35)}
    .btn-auth:active{transform:scale(.98)}
    
    .btn-faceid{background:#f1f5f9;color:#0f172a;border:2px solid #e2e8f0;padding:1rem;border-radius:16px;font-weight:600;width:100%;transition:all .35s;display:flex;align-items:center;justify-content:center;gap:.5rem;cursor:pointer}
    .btn-faceid:hover{background:#e2e8f0;border-color:#cbd5e1}
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased selection:bg-accent selection:text-white">

<div class="min-h-[100svh] flex flex-col lg:flex-row">
    <!-- LEFT: IMAGE PANEL (Darker for Admin vibe) -->
    <div class="relative w-full lg:w-1/2 h-56 sm:h-72 lg:h-auto shrink-0 overflow-hidden">
        <img src="../assets/img/kelas-1.jpg" alt="Admin Portal" class="absolute inset-0 w-full h-full object-cover grayscale">
        <div class="absolute inset-0 bg-gradient-to-b lg:bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-900/40"></div>
        <div class="relative z-10 p-6 lg:p-12 flex flex-col justify-end h-full">
            <a href="../index.php" class="flex items-center gap-2.5 mb-4 lg:mb-auto">
                <img src="../assets/img/logo.png" alt="Logo" class="w-10 h-10 object-contain drop-shadow-md">
                <span class="text-white font-bold text-sm drop-shadow-md">MTs Al-Barakah</span>
            </a>
            <div class="hidden lg:block">
                <div class="inline-block px-3 py-1 bg-accent/20 border border-accent/30 text-accent rounded-full text-[10px] font-bold uppercase tracking-widest mb-4">Secured Area</div>
                <h2 class="text-3xl font-extrabold text-white leading-tight tracking-tight mb-3">Portal<br>Administrator</h2>
                <p class="text-white/60 text-sm max-w-sm leading-relaxed">Akses khusus staf dan panitia PPDB. Dilengkapi sistem keamanan biometrik FaceID AI.</p>
            </div>
        </div>
    </div>

    <!-- RIGHT: FORM PANEL -->
    <div class="flex-1 flex items-center justify-center px-6 py-10 lg:py-16 lg:px-16">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <!-- BREADCRUMB -->
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-6">
                    <span class="text-slate-900">Portal Admin</span>
                    <i class="ph ph-shield-check text-accent text-lg ml-auto"></i>
                </nav>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">Otorisasi Sistem</h1>
                <p class="text-slate-500 mt-2 text-sm">Gunakan Password atau Pemindai Wajah</p>
            </div>

            <?php if($msg): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl text-sm font-medium flex items-center gap-3">
                <i class="ph ph-warning-circle text-xl shrink-0"></i> <?= $msg ?>
            </div>
            <?php endif; ?>

            <!-- PASSWORD LOGIN FORM -->
            <form id="formPassword" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Username Admin</label>
                    <div class="relative">
                        <i class="ph ph-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                        <input type="text" name="username" id="adminUsername" required placeholder="Ketik username Anda" autocomplete="off" class="input-auth">
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
                
                <div class="pt-2">
                    <button type="submit" name="login" class="btn-auth mb-4">
                        Masuk Sistem <i class="ph ph-sign-in text-lg"></i>
                    </button>
                    
                    <div class="flex items-center gap-4 my-6">
                        <div class="h-px bg-slate-200 flex-1"></div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">ATAU</span>
                        <div class="h-px bg-slate-200 flex-1"></div>
                    </div>
                    
                    <button type="button" onclick="toggleFaceID()" class="btn-faceid">
                        <i class="ph ph-face-id text-2xl text-accent"></i> Gunakan Pemindai Wajah
                    </button>
                </div>
            </form>

            <!-- FACE ID LOGIN FORM (HIDDEN BY DEFAULT) -->
            <div id="formFaceID" class="hidden space-y-5">
                <div class="bg-slate-900 rounded-[24px] p-6 text-center relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-50"></div>
                    
                    <!-- Scanner Box -->
                    <div class="relative w-48 h-48 mx-auto mb-4 bg-black rounded-2xl overflow-hidden border-2 border-slate-700 shadow-[0_0_30px_rgba(16,178,124,0.15)] flex items-center justify-center">
                        <video id="webcam" class="absolute inset-0 w-full h-full object-cover opacity-50 scale-x-[-1]" autoplay muted playsinline></video>
                        <canvas id="overlay" class="absolute inset-0 w-full h-full"></canvas>
                        
                        <!-- Scanner Animation overlay -->
                        <div class="absolute inset-0 border-4 border-accent/50 rounded-2xl pointer-events-none z-10"></div>
                        <div class="absolute left-0 right-0 h-1 bg-accent/80 shadow-[0_0_15px_#10b27c] top-0 animate-[scan_2s_ease-in-out_infinite] z-10"></div>
                        
                        <i class="ph ph-camera text-slate-600 text-4xl absolute z-0"></i>
                    </div>
                    
                    <h3 class="text-white font-bold text-lg relative z-20">Posisikan Wajah Anda</h3>
                    <p class="text-slate-400 text-xs mt-1 relative z-20" id="faceStatus">Menunggu akses kamera...</p>
                    
                    <button type="button" id="btnStartScan" class="relative z-20 mt-4 bg-accent hover:bg-accentDark text-white px-5 py-2 rounded-xl text-sm font-semibold transition-colors">
                        Aktifkan Kamera
                    </button>
                </div>
                
                <button type="button" onclick="toggleFaceID()" class="w-full text-center text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors mt-4">
                    <i class="ph ph-arrow-left"></i> Kembali ke Password
                </button>
            </div>

            <div class="mt-10 pt-6 border-t border-slate-100 text-center">
                <a href="../auth/login.php" class="text-xs text-slate-400 hover:text-accent transition-colors inline-flex items-center gap-1.5 font-medium">
                    <i class="ph ph-student"></i> Bukan Admin? Login Siswa
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes scan {
    0% { top: 0; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
</style>

<script>
function togglePw(){
    const f=document.getElementById('passwordField');
    const i=document.getElementById('eyeIcon');
    if(f.type==='password'){f.type='text';i.className='ph ph-eye-slash text-lg'}
    else{f.type='password';i.className='ph ph-eye text-lg'}
}

let isFaceIDActive = false;
let stream = null;
let modelsLoaded = false;
let detectionLoop;
const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

async function loadModels() {
    const status = document.getElementById('faceStatus');
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
        ]);
        modelsLoaded = true;
        status.innerText = "Sistem AI Siap! Sedang mendeteksi...";
        startLoginDetection();
    } catch (e) {
        status.innerText = "Gagal memuat AI. Cek koneksi internet Anda.";
        status.classList.add('text-red-400');
    }
}

async function startLoginDetection() {
    const video = document.getElementById('webcam');
    const overlay = document.getElementById('overlay');
    const status = document.getElementById('faceStatus');
    const btnScan = document.getElementById('btnStartScan');
    
    btnScan.innerHTML = `<i class="ph ph-spinner animate-spin"></i> Memindai Titik Wajah...`;
    
    // Hide overlay line animation temporarily
    const scanLines = document.querySelectorAll('.animate-\\[scan_2s_ease-in-out_infinite\\]');
    
    detectionLoop = setInterval(async () => {
        if(!video.paused && !video.ended) {
            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            
            if (detection) {
                // Wajah ketemu! Langsung kunci kamera dan kirim ke server
                clearInterval(detectionLoop);
                status.innerText = "Wajah terdeteksi! Mengotorisasi ke Server...";
                status.classList.remove('text-red-400', 'text-orange-400');
                status.classList.add('text-accent');
                video.pause();
                
                const descriptorArray = Array.from(detection.descriptor);
                
                const formData = new URLSearchParams();
                formData.append('action', 'login_face');
                // Tidak butuh username lagi
                formData.append('descriptor', JSON.stringify(descriptorArray));

                fetch('api_faceid.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Akses Diberikan!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            background: '#0f172a',
                            color: '#fff',
                            iconColor: '#10b27c'
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        // Kalau gagal cocok
                        Swal.fire({
                            icon: 'error',
                            title: 'Ditolak!',
                            text: data.message,
                            background: '#0f172a',
                            color: '#fff'
                        }).then(() => {
                            // Resume scan
                            video.play();
                            startLoginDetection();
                        });
                    }
                })
                .catch(err => {
                    status.innerText = "Error server: " + err.message;
                    status.classList.add('text-red-400');
                });
            }
        }
    }, 500); // deteksi setiap 500ms agar CPU tidak kepanasan
}

function toggleFaceID() {
    const pwdForm = document.getElementById('formPassword');
    const faceForm = document.getElementById('formFaceID');
    const video = document.getElementById('webcam');
    const status = document.getElementById('faceStatus');
    const btnScan = document.getElementById('btnStartScan');
    
    isFaceIDActive = !isFaceIDActive;
    
    if (isFaceIDActive) {
        pwdForm.classList.add('hidden');
        faceForm.classList.remove('hidden');
        
        status.innerText = "Posisikan wajah Anda lurus ke kamera.";
        status.classList.remove('text-orange-400', 'text-red-400');
        btnScan.classList.remove('hidden');
        btnScan.innerHTML = "Aktifkan Kamera";
        
    } else {
        pwdForm.classList.remove('hidden');
        faceForm.classList.add('hidden');
        
        clearInterval(detectionLoop);
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
    }
}

document.getElementById('btnStartScan').addEventListener('click', async () => {
    const video = document.getElementById('webcam');
    const status = document.getElementById('faceStatus');
    
    status.innerText = "Mengaktifkan kamera...";
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.classList.remove('opacity-50');
        status.innerText = "Kamera aktif. Menyiapkan Mesin AI...";
        
        if (!modelsLoaded) {
            loadModels();
        } else {
            startLoginDetection();
        }
        
    } catch (err) {
        status.innerText = "Gagal mengakses kamera. " + err.message;
        status.classList.add('text-red-400');
    }
});
</script>
</body>
</html>
