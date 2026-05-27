<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek apakah admin sudah memiliki face descriptor
$stmt = $pdo->prepare("SELECT face_descriptor FROM users_admin WHERE id = ?");
$stmt->execute([$user_id]);
$has_face = !empty($stmt->fetchColumn());

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan FaceID | Admin PPDB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Load face-api.js dari CDN untuk pendaftaran -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

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
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        .card-lift { transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1); }
        .card-lift:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(16,178,124, 0.15); }
    </style>
</head>
<body class="bg-surface text-slate-800 antialiased font-sans flex h-screen overflow-hidden">

    <div id="mobileOverlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden md:hidden transition-opacity duration-500 opacity-0" onclick="toggleSidebar()"></div>

    <?php $active_menu = 'faceid'; include 'layout/sidebar.php'; ?>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        <header class="h-20 bg-panel border-b border-slate-100 flex items-center justify-between px-6 md:px-8 shrink-0 z-10 relative">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-slate-500 hover:text-slate-800" onclick="toggleSidebar()">
                    <i class="ph ph-list text-2xl"></i>
                </button>
                <h2 class="text-xl font-extrabold tracking-tight">Biometrik Sistem</h2>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-full text-sm font-bold border border-slate-100">
                    <i class="ph ph-user-circle text-lg text-accent"></i>
                    <span><?= htmlspecialchars($_SESSION['nama']) ?></span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            <nav class="flex items-center gap-2 text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">
                <a href="dashboard.php" class="hover:text-accent transition-colors">Admin</a>
                <i class="ph ph-caret-right text-[10px]"></i>
                <span class="text-slate-900">Pengaturan FaceID</span>
            </nav>

            <div class="max-w-2xl mx-auto">
                <div class="bg-panel rounded-[24px] border border-slate-200 shadow-sm overflow-hidden card-lift">
                    <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-extrabold tracking-tight text-slate-900">Perekaman Pola Wajah</h3>
                            <p class="text-slate-500 text-sm mt-1">Daftarkan wajah Anda untuk login ke sistem tanpa password.</p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl <?= $has_face ? 'bg-accent/10 text-accent' : 'bg-slate-100 text-slate-400' ?> flex items-center justify-center shrink-0">
                            <i class="ph ph-face-id text-3xl"></i>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <?php if($has_face): ?>
                        <div class="mb-6 p-4 bg-accent/10 border border-accent/20 rounded-xl flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-accent text-white flex items-center justify-center shrink-0">
                                <i class="ph ph-check-circle text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-accentDark font-bold mb-1">FaceID Sudah Aktif</h4>
                                <p class="text-sm text-accent/80 font-medium">Anda sudah merekam wajah. Namun Anda masih bisa memperbarui rekaman wajah jika posisi atau pencahayaan saat direkam sebelumnya kurang optimal.</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Kamera Area -->
                        <div class="relative w-full aspect-video bg-slate-900 rounded-2xl overflow-hidden mb-6 flex items-center justify-center shadow-inner">
                            <video id="webcam" class="absolute inset-0 w-full h-full object-cover scale-x-[-1] opacity-50" autoplay muted playsinline></video>
                            <canvas id="overlay" class="absolute inset-0 w-full h-full z-10 pointer-events-none"></canvas>
                            
                            <div id="loaderUI" class="relative z-20 text-center">
                                <i class="ph ph-camera text-4xl text-slate-500 mb-2"></i>
                                <p class="text-slate-400 text-sm font-semibold" id="statusText">Kamera belum menyala</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <button id="btnStart" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-bold py-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                                <i class="ph ph-power"></i> Nyalakan Kamera
                            </button>
                            <button id="btnCapture" disabled class="flex-1 bg-slate-200 text-slate-400 font-bold py-4 rounded-xl transition-colors flex items-center justify-center gap-2 cursor-not-allowed">
                                <i class="ph ph-scan"></i> Ekstrak & Simpan Wajah
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            
        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('mobileOverlay');
        const isClosed = sb.classList.contains('-translate-x-full');
        if (isClosed) {
            sb.classList.remove('-translate-x-full');
            ov.classList.remove('hidden');
            setTimeout(() => ov.classList.remove('opacity-0'), 10);
        } else {
            sb.classList.add('-translate-x-full');
            ov.classList.add('opacity-0');
            setTimeout(() => ov.classList.add('hidden'), 500);
        }
    }

    const video = document.getElementById('webcam');
    const btnStart = document.getElementById('btnStart');
    const btnCapture = document.getElementById('btnCapture');
    const statusText = document.getElementById('statusText');
    const overlay = document.getElementById('overlay');
    let stream = null;
    let modelsLoaded = false;
    let detectionLoop;

    // Pastikan model dimuat dari CDN jsdelivr yang disediakan oleh vladmandic
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

    async function loadModels() {
        statusText.innerText = "Memuat model AI (Harap tunggu)...";
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            modelsLoaded = true;
            statusText.innerText = "Sistem AI Siap! Posisikan wajah Anda.";
            btnCapture.disabled = false;
            btnCapture.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
            btnCapture.classList.add('bg-accent', 'hover:bg-accentDark', 'text-white', 'shadow-lg');
            
            // Start detection overlay
            startDetectionLoop();
        } catch (e) {
            statusText.innerText = "Gagal memuat AI. Cek koneksi internet.";
            console.error(e);
        }
    }

    btnStart.addEventListener('click', async () => {
        if (!stream) {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;
                video.classList.remove('opacity-50');
                
                // Hide icon camera
                document.querySelector('#loaderUI i').style.display = 'none';
                
                if (!modelsLoaded) {
                    loadModels();
                } else {
                    statusText.innerText = "Sistem AI Siap! Posisikan wajah Anda.";
                    startDetectionLoop();
                }
            } catch (err) {
                statusText.innerText = "Gagal mengakses kamera!";
                statusText.classList.add('text-red-400');
            }
        }
    });

    async function startDetectionLoop() {
        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
        faceapi.matchDimensions(overlay, displaySize);
        
        detectionLoop = setInterval(async () => {
            if(!video.paused && !video.ended) {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                
                const ctx = overlay.getContext('2d');
                ctx.clearRect(0, 0, overlay.width, overlay.height);
                
                // Kita mirror koordinat agar pas karena video kita scale-x-[-1]
                ctx.save();
                ctx.scale(-1, 1);
                ctx.translate(-overlay.width, 0);
                faceapi.draw.drawDetections(overlay, resizedDetections);
                faceapi.draw.drawFaceLandmarks(overlay, resizedDetections);
                ctx.restore();
            }
        }, 100);
    }

    btnCapture.addEventListener('click', async () => {
        statusText.innerText = "Mengekstrak geometri wajah...";
        btnCapture.innerHTML = `<i class="ph ph-spinner animate-spin"></i> Memproses...`;
        btnCapture.disabled = true;

        // Ambil fitur wajah menggunakan face-api
        const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();

        if (detection) {
            // Wajah terdeteksi! Array 128 angka float.
            const descriptorArray = Array.from(detection.descriptor);
            
            // Hentikan kamera dan loop agar canvas beku
            clearInterval(detectionLoop);
            if(stream) {
                stream.getTracks().forEach(t => t.stop());
                video.pause();
            }

            // Kirim ke backend
            const formData = new URLSearchParams();
            formData.append('action', 'save_face');
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
                        title: 'Terekam!',
                        text: 'Wajah Anda berhasil didaftarkan ke sistem!',
                        confirmButtonColor: '#10b27c'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                    btnCapture.disabled = false;
                    btnCapture.innerHTML = `<i class="ph ph-scan"></i> Coba Lagi`;
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                btnCapture.disabled = false;
            });

        } else {
            statusText.innerText = "Posisikan wajah Anda lebih jelas ke kamera.";
            Swal.fire('Gagal', 'Wajah tidak terdeteksi dengan jelas. Pastikan cahaya cukup.', 'warning');
            btnCapture.innerHTML = `<i class="ph ph-scan"></i> Ekstrak & Simpan Wajah`;
            btnCapture.disabled = false;
        }
    });

    // Menangani perubahan ukuran video untuk canvas
    video.addEventListener('loadedmetadata', () => {
        overlay.width = video.videoWidth;
        overlay.height = video.videoHeight;
    });
</script>
</body>
</html>
