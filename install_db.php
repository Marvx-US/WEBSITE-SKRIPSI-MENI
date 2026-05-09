<?php
if (php_sapi_name() !== 'cli') {
    die("Script ini hanya bisa dijalankan melalui terminal (CLI).\nGunakan: php install_db.php\n");
}

define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'ppdb');
define('DB_CHARSET', 'utf8mb4');

function out(string $message, string $type = 'info'): void {
    $colors = [
        'info'    => "\033[0;36m",
        'success' => "\033[0;32m",
        'warning' => "\033[0;33m",
        'error'   => "\033[0;31m",
        'bold'    => "\033[1;37m",
        'dim'     => "\033[2;37m",
    ];
    $reset  = "\033[0m";
    $prefix = [
        'info'    => '  ℹ ',
        'success' => '  ✔ ',
        'warning' => '  ⚠ ',
        'error'   => '  ✘ ',
        'bold'    => '    ',
        'dim'     => '    ',
    ];
    echo ($colors[$type] ?? '') . ($prefix[$type] ?? '  ') . $message . $reset . PHP_EOL;
}

function separator(): void {
    echo "\033[2;37m" . str_repeat('─', 55) . "\033[0m" . PHP_EOL;
}

function section(string $title): void {
    echo PHP_EOL . "\033[1;37m  " . strtoupper($title) . "\033[0m" . PHP_EOL;
    separator();
}

echo PHP_EOL;
echo "\033[1;32m" . "  ╔═══════════════════════════════════════════════════╗" . "\033[0m" . PHP_EOL;
echo "\033[1;32m" . "  ║     PPDB MTs Al-Barakah — Database Installer     ║" . "\033[0m" . PHP_EOL;
echo "\033[1;32m" . "  ╚═══════════════════════════════════════════════════╝" . "\033[0m" . PHP_EOL;
echo PHP_EOL;

out("Database target : " . DB_NAME, 'bold');
out("Host            : " . DB_HOST, 'bold');
out("User            : " . DB_USER, 'bold');
echo PHP_EOL;

out("Apakah Anda yakin ingin melanjutkan? (y/N) ", 'warning');
echo "\033[1;33m" . "  > " . "\033[0m";
$input = trim(fgets(STDIN));

if (strtolower($input) !== 'y') {
    out("Instalasi dibatalkan.", 'error');
    echo PHP_EOL;
    exit(0);
}

section("Step 1: Koneksi ke MySQL");

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    out("Koneksi ke MySQL server berhasil.", 'success');
} catch (PDOException $e) {
    out("Koneksi gagal: " . $e->getMessage(), 'error');
    out("Pastikan MySQL/Laragon sudah berjalan.", 'warning');
    echo PHP_EOL;
    exit(1);
}

section("Step 2: Membuat Database");

$dbExisted = (bool) $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'")->fetchColumn();

$pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$pdo->exec("USE `" . DB_NAME . "`");

if ($dbExisted) {
    $tableCount = count($pdo->query("SHOW TABLES")->fetchAll());
    out("Database '" . DB_NAME . "' sudah ada. ({$tableCount} tabel terdeteksi)", 'warning');
} else {
    out("Database '" . DB_NAME . "' berhasil dibuat.", 'success');
}

section("Step 3: Membuat Tabel");

$tables = [
    'password_resets' => "CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `token` char(64) NOT NULL,
        `expires_at` datetime NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    'pengumuman' => "CREATE TABLE IF NOT EXISTS `pengumuman` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `judul` varchar(255) NOT NULL,
        `isi` text NOT NULL,
        `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    'pendaftar' => "CREATE TABLE IF NOT EXISTS `pendaftar` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `nama_lengkap` varchar(100) DEFAULT NULL,
        `nisn` varchar(20) DEFAULT NULL,
        `tempat_lahir` varchar(50) DEFAULT NULL,
        `tgl_lahir` date DEFAULT NULL,
        `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
        `alamat_desa` text DEFAULT NULL,
        `asal_sekolah` varchar(100) DEFAULT NULL,
        `no_hp_ortu` varchar(15) DEFAULT NULL,
        `status_verifikasi` enum('Proses','Diterima','Ditolak') DEFAULT 'Proses',
        `tgl_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
        `nilai_ujian` decimal(10,2) DEFAULT NULL,
        `foto_siswa` varchar(255) DEFAULT NULL,
        `ijazah_file` varchar(255) DEFAULT NULL,
        `kk_file` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    'users_admin' => "CREATE TABLE IF NOT EXISTS `users_admin` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(50) NOT NULL,
        `password` varchar(255) DEFAULT NULL,
        `nama_lengkap` varchar(100) NOT NULL,
        `role` varchar(20) NOT NULL DEFAULT 'verifikator',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    'users_siswa' => "CREATE TABLE IF NOT EXISTS `users_siswa` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nama_lengkap` varchar(100) DEFAULT NULL,
        `nisn` varchar(20) DEFAULT NULL,
        `password` varchar(255) DEFAULT NULL,
        `role` varchar(10) DEFAULT 'siswa',
        `tgl_buat` timestamp NOT NULL DEFAULT current_timestamp(),
        `foto` varchar(255) DEFAULT NULL,
        `kk` varchar(255) DEFAULT NULL,
        `ijazah` varchar(255) DEFAULT NULL,
        `akte` varchar(255) DEFAULT NULL,
        `kip` varchar(255) DEFAULT NULL,
        `status` enum('pending','diterima','ditolak','revisi') DEFAULT 'pending',
        `tahun_ajaran` varchar(20) DEFAULT NULL,
        `pesan_revisi` text DEFAULT NULL,
        `nik` varchar(20) DEFAULT NULL,
        `jenis_kelamin` varchar(20) DEFAULT NULL,
        `tempat_lahir` varchar(50) DEFAULT NULL,
        `tanggal_lahir` date DEFAULT NULL,
        `anak_ke` int(11) DEFAULT NULL,
        `jumlah_saudara` int(11) DEFAULT NULL,
        `status_keluarga` varchar(50) DEFAULT NULL,
        `desa` varchar(50) DEFAULT NULL,
        `kecamatan` varchar(50) DEFAULT NULL,
        `kabupaten` varchar(50) DEFAULT NULL,
        `provinsi` varchar(50) DEFAULT NULL,
        `no_hp` varchar(20) DEFAULT NULL,
        `nama_sd` varchar(100) DEFAULT NULL,
        `alamat_sd` text DEFAULT NULL,
        `nama_ayah` varchar(100) DEFAULT NULL,
        `nama_ibu` varchar(100) DEFAULT NULL,
        `hp_ortu` varchar(20) DEFAULT NULL,
        `pekerjaan_ayah` varchar(50) DEFAULT NULL,
        `pekerjaan_ibu` varchar(50) DEFAULT NULL,
        `nama_wali` varchar(100) DEFAULT NULL,
        `pekerjaan_wali` varchar(50) DEFAULT NULL,
        `alamat_wali` text DEFAULT NULL,
        `ekstrakurikuler` text DEFAULT NULL,
        `prestasi` text DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `nisn` (`nisn`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    'ppdb_settings' => "CREATE TABLE IF NOT EXISTS `ppdb_settings` (
        `setting_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
        `setting_value` text COLLATE utf8mb4_general_ci,
        PRIMARY KEY (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

    'ppdb_pengumuman' => "CREATE TABLE IF NOT EXISTS `ppdb_pengumuman` (
        `id` int NOT NULL AUTO_INCREMENT,
        `judul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
        `isi` text COLLATE utf8mb4_general_ci,
        `tgl_buat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
];

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        out("Tabel [{$tableName}] siap.", 'success');
    } catch (PDOException $e) {
        out("Gagal membuat tabel [{$tableName}]: " . $e->getMessage(), 'error');
    }
}

section("Step 4: Seed Data Default");

$adminCount = (int)$pdo->query("SELECT COUNT(*) FROM `users_admin`")->fetchColumn();
if ($adminCount === 0) {
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO `users_admin` (`username`, `password`, `nama_lengkap`, `role`) VALUES (?, ?, ?, ?)")
        ->execute(['admin', $hashedPassword, 'Administrator', 'superadmin']);
    out("Akun Super Admin dibuat.  [user: admin | pass: admin123]", 'success');
} else {
    out("Akun Admin sudah ada, dilewati.", 'warning');
}

$settingsCount = (int)$pdo->query("SELECT COUNT(*) FROM `ppdb_settings`")->fetchColumn();
if ($settingsCount === 0) {
    $persyaraanJson = json_encode([
        ['icon' => 'ph-certificate', 'judul' => 'Surat Keterangan Lulus (SKL) / Ijazah',  'desc' => 'Scan dokumen asli atau fotokopi yang telah dilegalisir. Diunggah dalam format PDF.'],
        ['icon' => 'ph-users-three', 'judul' => 'Kartu Keluarga (KK)',                    'desc' => 'Scan Kartu Keluarga asli terbaru. NIK siswa dan nama orang tua harus jelas.'],
        ['icon' => 'ph-user-focus',  'judul' => 'Pas Foto Terbaru',                       'desc' => 'Foto formal latar merah atau biru. Format JPG/PNG.'],
    ], JSON_UNESCAPED_UNICODE);

    $jadwalJson = json_encode([
        ['tanggal' => '1 Mei - 30 Juni', 'nama' => 'Pendaftaran Daring',   'desc' => 'Pembuatan akun, pengisian biodata, dan pengunggahan berkas melalui portal ini.',               'style' => 'normal'],
        ['tanggal' => '5 Juli',          'nama' => 'Pengumuman Kelulusan', 'desc' => 'Hasil verifikasi berkas diperbarui secara real-time pada dashboard siswa.',                   'style' => 'accent'],
        ['tanggal' => '6 - 10 Juli',     'nama' => 'Daftar Ulang',         'desc' => 'Menyerahkan berkas fisik ke madrasah bagi peserta didik yang dinyatakan lulus.',              'style' => 'accent'],
    ], JSON_UNESCAPED_UNICODE);

    $defaultSettings = [
        ['jadwal_buka',        date('Y-m-d')],
        ['jadwal_tutup',       date('Y-m-d', strtotime('+60 days'))],
        ['jadwal_pengumuman',  date('Y-m-d 15:00:00', strtotime('+65 days'))],
        ['info_berkas',        'Siapkan: Fotocopy KK, Ijazah/SKL, Pas Foto 3x4, Akta Kelahiran.'],
        ['info_pengumuman',    ''],
        ['banner_teks',        'Penerimaan Tahun ' . date('Y') . '/' . (date('Y') + 1) . ' Dibuka'],
        ['nama_sekolah',       'MTs PP DDI Al-Barakah'],
        ['tahun_ajaran',       date('Y') . '/' . (date('Y') + 1)],
        ['kuota_pendaftar',    '100'],
        ['persyaratan_json',   $persyaraanJson],
        ['jadwal_json',        $jadwalJson],
    ];
    $stmtSetting = $pdo->prepare("INSERT IGNORE INTO `ppdb_settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmtSetting->execute($setting);
    }
    out("Pengaturan PPDB default ditambahkan.", 'success');
} else {
    out("Pengaturan sudah ada, dilewati.", 'warning');
}

echo PHP_EOL;
echo "\033[1;32m" . "  ╔═══════════════════════════════════════════════════╗" . "\033[0m" . PHP_EOL;
echo "\033[1;32m" . "  ║          ✔  Instalasi Selesai!                   ║" . "\033[0m" . PHP_EOL;
echo "\033[1;32m" . "  ╚═══════════════════════════════════════════════════╝" . "\033[0m" . PHP_EOL;
echo PHP_EOL;
out("Login Admin  : http://localhost/WEBSITE SKRIPSI MENI/auth/login.php", 'info');
out("Username     : admin", 'info');
out("Password     : admin123", 'info');
echo PHP_EOL;
out("Segera ganti password admin setelah login pertama!", 'warning');
echo PHP_EOL;
