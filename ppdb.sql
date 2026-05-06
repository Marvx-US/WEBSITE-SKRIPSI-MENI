-- ============================================================
-- PPDB MTs Al-Barakah — Database Schema
-- Generated: 2026-05-06
-- Disesuaikan dengan struktur project aktif
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- Database: `ppdb`
-- ============================================================

-- --------------------------------------------------------
-- Tabel: password_resets
-- Dipakai oleh: fitur reset password siswa
-- --------------------------------------------------------

CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` char(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabel: ppdb_pengumuman
-- Dipakai oleh: pengumuman.php, admin/kelola_pengumuman.php
-- --------------------------------------------------------

CREATE TABLE `ppdb_pengumuman` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `isi` text COLLATE utf8mb4_general_ci,
  `tgl_buat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabel: ppdb_settings
-- Dipakai oleh: admin/pengaturan.php, index.php, siswa/dashboard.php
-- Primary key: setting_key (bukan id auto increment)
-- --------------------------------------------------------

CREATE TABLE `ppdb_settings` (
  `setting_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data default untuk ppdb_settings
--

INSERT INTO `ppdb_settings` (`setting_key`, `setting_value`) VALUES
('jadwal_buka',      '2025-06-01'),
('jadwal_tutup',     '2027-01-31'),
('info_berkas',      'Siapkan dokumen berikut: (1) Fotocopy Kartu Keluarga, (2) Ijazah/SKL SD/MI, (3) Pas Foto 3x4 terbaru, (4) Akte Kelahiran. Semua dokumen dalam format JPG atau PDF.'),
('info_pengumuman',  'Pendaftaran Peserta Didik Baru (PPDB) MTs Al-Barakah sedang dibuka. Lengkapi data Anda sebelum batas waktu yang ditentukan.'),
('banner_teks',      'Penerimaan Tahun 2026/2027 Dibuka'),
('nama_sekolah',     'MTs PP DDI Al-Barakah'),
('tahun_ajaran',     '2026/2027'),
('kuota_pendaftar',  '100'),
('persyaratan_json', '[{"icon":"ph-certificate","judul":"Surat Keterangan Lulus (SKL) / Ijazah","desc":"Scan dokumen asli atau fotokopi yang telah dilegalisir dari sekolah dasar/sederajat asal. Diunggah dalam format PDF."},{"icon":"ph-users-three","judul":"Kartu Keluarga (KK)","desc":"Scan Kartu Keluarga asli terbaru. Pastikan Nomor Induk Kependudukan (NIK) siswa dan nama orang tua tercantum dengan jelas."},{"icon":"ph-user-focus","judul":"Pas Foto Terbaru","desc":"Foto setengah badan dengan pakaian formal (seragam asal), latar belakang warna merah atau biru. Format yang diterima adalah gambar (JPG/PNG)."}]'),
('jadwal_json',      '[{"tanggal":"1 Mei - 30 Juni","nama":"Pendaftaran Daring","desc":"Pembuatan akun, pengisian data biodata, dan pengunggahan berkas persyaratan melalui portal ini.","style":"normal"},{"tanggal":"5 Juli","nama":"Pengumuman Kelulusan","desc":"Hasil verifikasi berkas dan pengumuman diterima akan diperbarui secara real-time pada dashboard siswa.","style":"accent"},{"tanggal":"6 - 10 Juli","nama":"Daftar Ulang","desc":"Proses pendaftaran ulang dengan menyerahkan berkas fisik ke madrasah bagi peserta didik yang dinyatakan lulus.","style":"accent"}]');

-- --------------------------------------------------------
-- Tabel: users_admin
-- Dipakai oleh: auth/login.php, admin/kelola_users.php
-- --------------------------------------------------------

CREATE TABLE `users_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'verifikator',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data default: akun admin (password: admin123)
--

INSERT INTO `users_admin` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '$2y$10$eFDgMaHukGer93WbnYBEuuFzgZrRLR0GbHLcud4JqBW4gZGXqKZAy', 'Super Admin', 'superadmin');

-- --------------------------------------------------------
-- Tabel: users_siswa
-- Dipakai oleh: auth/login.php, siswa/dashboard.php, admin/dashboard.php,
--               admin/detail_siswa.php, admin/export_excel.php,
--               admin/verifikasi_aksi.php, admin/batch_aksi.php,
--               admin/reset_password_siswa.php
-- --------------------------------------------------------

CREATE TABLE `users_siswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'siswa',
  `tgl_buat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Berkas unggahan
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kk` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ijazah` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `akte` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kip` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  -- Status verifikasi
  `status` enum('pending','diterima','ditolak','revisi') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `pesan_revisi` text COLLATE utf8mb4_general_ci,
  -- Data pribadi
  `nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `anak_ke` int DEFAULT NULL,
  `jumlah_saudara` int DEFAULT NULL,
  `status_keluarga` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  -- Alamat
  `desa` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kecamatan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kabupaten` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provinsi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  -- Asal sekolah
  `nama_sd` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_sd` text COLLATE utf8mb4_general_ci,
  -- Data orang tua / wali
  `nama_ayah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_ibu` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hp_ortu` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_ayah` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_ibu` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_wali` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_wali` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_wali` text COLLATE utf8mb4_general_ci,
  -- Minat & prestasi
  `ekstrakurikuler` text COLLATE utf8mb4_general_ci,
  `prestasi` text COLLATE utf8mb4_general_ci,
  -- Jalur masuk
  `jalur_masuk` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Reguler',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- AUTO_INCREMENT initial values
-- ============================================================

ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `ppdb_pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `users_admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
