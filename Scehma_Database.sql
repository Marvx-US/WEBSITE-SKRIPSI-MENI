-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: ppdb
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` char(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftar`
--

DROP TABLE IF EXISTS `pendaftar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_desa` text COLLATE utf8mb4_general_ci,
  `asal_sekolah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp_ortu` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` enum('Proses','Diterima','Ditolak') COLLATE utf8mb4_general_ci DEFAULT 'Proses',
  `tgl_daftar` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nilai_ujian` decimal(10,2) DEFAULT NULL,
  `foto_siswa` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ijazah_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kk_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftar`
--

LOCK TABLES `pendaftar` WRITE;
/*!40000 ALTER TABLE `pendaftar` DISABLE KEYS */;
INSERT INTO `pendaftar` VALUES (1,1,'Citra',NULL,NULL,NULL,NULL,'sereang',NULL,'082393282702','Diterima','2026-04-15 03:01:37',890.00,'1776222097_Screenshot 2026-04-14 142853.png','1776222097_gambar-kartun-anak-sekolah-smp-keren-1.png','1776222097_Screenshot 2026-01-20 235326.png');
/*!40000 ALTER TABLE `pendaftar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftaran`
--

DROP TABLE IF EXISTS `pendaftaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `asal_sekolah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verifikasi` enum('pending','diterima','ditolak') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran`
--

LOCK TABLES `pendaftaran` WRITE;
/*!40000 ALTER TABLE `pendaftaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `pendaftaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengumuman`
--

DROP TABLE IF EXISTS `pengumuman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengumuman` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `isi` text COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengumuman`
--

LOCK TABLES `pengumuman` WRITE;
/*!40000 ALTER TABLE `pengumuman` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengumuman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ppdb_pengumuman`
--

DROP TABLE IF EXISTS `ppdb_pengumuman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ppdb_pengumuman` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) DEFAULT NULL,
  `isi` text,
  `tgl_buat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ppdb_pengumuman`
--

LOCK TABLES `ppdb_pengumuman` WRITE;
/*!40000 ALTER TABLE `ppdb_pengumuman` DISABLE KEYS */;
/*!40000 ALTER TABLE `ppdb_pengumuman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ppdb_settings`
--

DROP TABLE IF EXISTS `ppdb_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ppdb_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_label` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ppdb_settings`
--

LOCK TABLES `ppdb_settings` WRITE;
/*!40000 ALTER TABLE `ppdb_settings` DISABLE KEYS */;
INSERT INTO `ppdb_settings` VALUES (1,'jadwal_buka','2026-04-01','Tanggal Pembukaan Pendaftaran','2026-05-12 14:02:54'),(2,'jadwal_tutup','2026-05-11','Tanggal Penutupan Pendaftaran','2026-05-12 14:08:23'),(3,'info_berkas','Siapkan dokumen berikut: (1) Fotocopy Kartu Keluarga, (2) Ijazah/SKL SD/MI, (3) Pas Foto 3x4 terbaru, (4) Akte Kelahiran. Semua dokumen dalam format JPG atau PDF.','Informasi Syarat Berkas','2026-05-02 13:30:55'),(4,'info_pengumuman','Pendaftaran Peserta Didik Baru (PPDB) MTs Al-Barakah sedang dibuka. Lengkapi data Anda sebelum batas waktu yang ditentukan.','Teks Pengumuman Utama','2026-05-02 13:30:55'),(5,'form_config','{\"nik\":{\"aktif\":true,\"label\":\"Nomor Induk Kependudukan (NIK)\"},\"jenis_kelamin\":{\"aktif\":true,\"label\":\"Jenis Kelamin\"},\"tempat_lahir\":{\"aktif\":true,\"label\":\"Tempat Lahir\"},\"tgl_lahir\":{\"aktif\":true,\"label\":\"Tanggal Lahir\"},\"nama_ayah\":{\"aktif\":true,\"label\":\"Nama Lengkap Ayah\"},\"pekerjaan_ayah\":{\"aktif\":true,\"label\":\"Pekerjaan Ayah\"},\"nama_ibu\":{\"aktif\":true,\"label\":\"Nama Lengkap Ibu\"},\"pekerjaan_ibu\":{\"aktif\":true,\"label\":\"Pekerjaan Ibu\"},\"hp_ortu\":{\"aktif\":true,\"label\":\"Nomor WhatsApp Aktif\"},\"nama_sd\":{\"aktif\":true,\"label\":\"Nama SD\\/MI Asal\"},\"anak_ke\":{\"aktif\":false,\"label\":\"Anak Ke\"},\"jumlah_saudara\":{\"aktif\":false,\"label\":\"Jumlah Saudara\"},\"nama_wali\":{\"aktif\":false,\"label\":\"Nama Wali\"},\"pekerjaan_wali\":{\"aktif\":false,\"label\":\"Pekerjaan Wali\"},\"ekstrakurikuler\":{\"aktif\":false,\"label\":\"Minat Ekstrakurikuler\"},\"prestasi\":{\"aktif\":false,\"label\":\"Prestasi yang Dimiliki\"}}','Konfigurasi Field Formulir Siswa','2026-05-02 13:30:55'),(14,'jadwal_json','[{\"tanggal\":\"1 Mei - 30 Juni\",\"nama\":\"Pendaftaran Daring\",\"desc\":\"Pembuatan akun, pengisian data biodata, dan pengunggahan berkas persyaratan melalui portal ini.\",\"style\":\"normal\"},{\"tanggal\":\"5 Juli\",\"nama\":\"Pengumuman Kelulusan\",\"desc\":\"Hasil verifikasi berkas dan pengumuman diterima akan diperbarui secara real-time pada dashboard siswa.\",\"style\":\"accent\"},{\"tanggal\":\"6 - 10 Juli\",\"nama\":\"Daftar Ulang\",\"desc\":\"Proses pendaftaran ulang dengan menyerahkan berkas fisik ke madrasah bagi peserta didik yang dinyatakan lulus.\",\"style\":\"accent\"},{\"tanggal\":\"11 - 20 Juli\",\"nama\":\"Testing Tahap\",\"desc\":\"Testing Tahap Higlight\",\"style\":\"accent\"},{\"tanggal\":\"21 - 25 Juli \",\"nama\":\"Testing Tahap\",\"desc\":\"Testing Tahap Normal\",\"style\":\"normal\"}]',NULL,'2026-05-12 15:12:21'),(15,'jadwal_pengumuman','2026-05-12T22:14',NULL,'2026-05-12 14:12:44'),(34,'tahun_ajaran','2026/2027',NULL,'2026-05-12 13:50:32'),(83,'persyaratan_json','[{\"icon\":\"ph-certificate\",\"judul\":\"Surat Keterangan Lulus (SKL) \\/ Ijazah\",\"desc\":\"Scan dokumen asli atau fotokopi yang telah dilegalisir dari sekolah dasar\\/sederajat asal. Diunggah dalam format PDF.\"},{\"icon\":\"ph-users-three\",\"judul\":\"Kartu Keluarga (KK)\",\"desc\":\"Scan Kartu Keluarga asli terbaru. Pastikan Nomor Induk Kependudukan (NIK) siswa dan nama orang tua tercantum dengan jelas.\"},{\"icon\":\"ph-user-focus\",\"judul\":\"Pas Foto Terbaru\",\"desc\":\"Foto setengah badan dengan pakaian formal (seragam asal), latar belakang warna merah atau biru. Format yang diterima adalah gambar (JPG\\/PNG).\"},{\"icon\":\"ph ph-align-center-vertical-simple\",\"judul\":\"Testing Dokumen\",\"desc\":\"Ini merupakan Testing Dokumen\"}]',NULL,'2026-05-12 15:11:00'),(86,'banner_teks','Penerimaan Tahun Testing Dibuka',NULL,'2026-05-12 15:13:30');
/*!40000 ALTER TABLE `ppdb_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_admin`
--

DROP TABLE IF EXISTS `users_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('superadmin','verifikator') COLLATE utf8mb4_general_ci DEFAULT 'verifikator',
  `face_descriptor` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=123456790 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_admin`
--

LOCK TABLES `users_admin` WRITE;
/*!40000 ALTER TABLE `users_admin` DISABLE KEYS */;
INSERT INTO `users_admin` VALUES (1,'admin','$2y$10$eFDgMaHukGer93WbnYBEuuFzgZrRLR0GbHLcud4JqBW4gZGXqKZAy','Super Admin','superadmin','[-0.14056262373924255,0.06636202335357666,0.0867825597524643,-0.04565996676683426,-0.0329434871673584,-0.04706030339002609,-0.010440223850309849,-0.11248400807380676,0.19541198015213013,-0.0940723568201065,0.3111959397792816,0.007875805720686913,-0.20638498663902283,-0.11739788204431534,0.02396388351917267,0.13755270838737488,-0.2268618941307068,-0.0880017802119255,-0.03295980021357536,-0.11393129080533981,0.01915661059319973,-0.07119382917881012,0.0924108475446701,0.044286098331213,-0.09123765677213669,-0.34048017859458923,-0.09704858064651489,-0.10692635923624039,0.0696181207895279,-0.05738097429275513,-0.02752736210823059,-0.02224770374596119,-0.23408721387386322,0.019825631752610207,-0.06505142897367477,0.018795229494571686,0.03880354389548302,-0.06058594956994057,0.1814839243888855,-0.004533800296485424,-0.17122426629066467,-0.07574653625488281,-0.08272548019886017,0.203452005982399,0.1433926820755005,-0.03280201181769371,0.043782737106084824,-0.06356361508369446,0.0467783659696579,-0.16316989064216614,0.10934329032897949,0.15380170941352844,0.08766535669565201,-0.020084388554096222,-0.04709542170166969,-0.1332882195711136,-0.06293322890996933,0.10954193025827408,-0.13238942623138428,0.06520845741033554,0.0758446753025055,-0.11313062161207199,-0.08364725112915039,-0.04129098728299141,0.25091519951820374,0.21004587411880493,-0.16516315937042236,-0.20071738958358765,0.120278000831604,-0.09736169874668121,-0.017266234382987022,0.053302276879549026,-0.1712867170572281,-0.18256527185440063,-0.3487108051776886,0.08144685626029968,0.4315735995769501,0.052239980548620224,-0.20500890910625458,-0.03318633884191513,-0.23117315769195557,0.02496335655450821,0.08445239067077637,0.1616835594177246,-0.059740688651800156,0.05100628361105919,-0.1351793259382248,0.04001711308956146,0.14014771580696106,-0.05652813985943794,-0.13236820697784424,0.2054576873779297,-0.06364564597606659,0.053138021379709244,-0.04295843839645386,0.06344523280858994,-0.03818052262067795,-0.004805989563465118,-0.06366901099681854,-0.017763985320925713,0.030634354799985886,-0.04465252906084061,-0.03169960901141167,0.09464313089847565,-0.17444947361946106,0.0816543698310852,0.044250234961509705,-0.008194394409656525,0.03283240646123886,0.018455656245350838,-0.0887739360332489,-0.12978684902191162,0.10222012549638748,-0.27871569991111755,0.21792711317539215,0.21349230408668518,0.08898445218801498,0.2004098743200302,0.050585128366947174,0.11044669896364212,-0.004191878717392683,-0.04139786958694458,-0.08669308573007584,0.02962379716336727,0.0003002710291184485,0.04121767729520798,0.06184491142630577,0.033132217824459076]'),(2,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9l9e5r2zG7sJd2zj6hW8y6','admin','verifikator',NULL),(123456789,'admin123','$2y$10$SdrI3VJVNBorELUHficMoesvWM.7epjol77VKnJHpRwFGDIMC0Qqe','admin123','verifikator',NULL);
/*!40000 ALTER TABLE `users_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_siswa`
--

DROP TABLE IF EXISTS `users_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_siswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nisn` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'siswa',
  `tgl_buat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kk` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ijazah` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `akte` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','diterima','ditolak','revisi') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `tahun_ajaran` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pesan_revisi` text COLLATE utf8mb4_general_ci,
  `nik` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `anak_ke` int DEFAULT NULL,
  `jumlah_saudara` int DEFAULT NULL,
  `status_keluarga` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `desa` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kecamatan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kabupaten` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provinsi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_sd` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_sd` text COLLATE utf8mb4_general_ci,
  `nama_ayah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_ibu` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hp_ortu` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_ayah` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_ibu` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_wali` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan_wali` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_wali` text COLLATE utf8mb4_general_ci,
  `ekstrakurikuler` text COLLATE utf8mb4_general_ci,
  `prestasi` text COLLATE utf8mb4_general_ci,
  `kip` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jalur_masuk` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Reguler',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_siswa`
--

LOCK TABLES `users_siswa` WRITE;
/*!40000 ALTER TABLE `users_siswa` DISABLE KEYS */;
INSERT INTO `users_siswa` VALUES (10,'Muh.Amar','123123123123','$2y$10$Gri7hHuk5mx1aSJpdNxzBemTY4Bmtpzd0YtPgVjnj3JIDkR3T0JtW','siswa','2026-05-02 14:22:37','1777732657_81e695ec.jpg','1777732657_f9bed0a1.jpg','1777732657_3ff79eb5.jpg',NULL,'revisi','2026/2027','sadasd','21321312313123123123','L','Pare pare','2001-06-12',0,0,'','','','','','','dsfsdf','','saad','asd','0208324324234','asd','asd','','','','','',NULL,'Reguler'),(11,'Uliana Safitri','1707200400','$2y$10$9swbpmvN4joAq.VbnhwqnO6WYJ4yrR.aCdGJU2O0CXTbMYtD1PykK','siswa','2026-05-03 03:52:15','1778339626_e6eb89ca.jpg','1778339626_7c2c66b2.jpg','1778339626_378fa26d.jpg','1778568648_ca2c6b7b.jpg','diterima','2026/2027',NULL,'83883828388','L','Pare','2004-07-17',2,2,'Anak Kandung','dsfkjsdjkf','sdfsdf','sdfnnsf','sdfnsfj','324900230423','sdkfjksf','sdjfjsdf','dsfnjndsjsd','sdfnjsdfj','30940923040','sdbfbsfbs','sdjfjsdj','','','','sdfkjsf','sdfknksndf','1778568648_e4ae78ad.jpg','Reguler'),(12,'Test Student','1234567890','$2y$10$V1xOZO/1v525askYADgEluX03WxnsR4W5nMkD8cCd7r9AK3AZ1SwS','siswa','2026-05-09 14:12:19',NULL,NULL,NULL,NULL,'diterima','2026/2027',NULL,'1234567890123456','L','Jakarta','2000-01-01',1,2,'Anak Kandung','Cilandak','','','','','SDN 01 Jakarta','Jl. Kebon Jeruk No. 1','Budi Ayah','','','','','','','','','',NULL,'Reguler'),(13,'Ansar','8888888888','$2y$10$UBfdYn6ccFFbDajoWRzoRuybdY3dr/FhQ0TIDawAg4YIW8K9MBoya','siswa','2026-05-09 15:37:39',NULL,NULL,NULL,NULL,'diterima','2026/2027',NULL,'dsfsdf','P','',NULL,0,0,'','','','','','','','','','','','','','','','','','',NULL,'Reguler'),(14,'tesLagi','9999999999','$2y$10$QKPsWYHbqVv51aPrJ3KuaeQXzvCwUUp5hHbVPuX7BF9nuxtfOwpLW','siswa','2026-05-12 06:51:57','1778568917_55df989c.jpg','1778568917_de20af9f.jpg','1778568917_dc7db341.jpg','1778568917_bce024cd.jpg','pending','2026/2027',NULL,'sadasdasd','','sdad','2000-05-02',2,3,'Anak Kandung','dsfsdf','sdfsdfsdf','dsfdsf','sdfsdf','432984892894892','sfsafaf','saasfasf','sdfjjsdf','sdf','3248932879784','sdf','sdfsfdr','','','','safasfasf','asfasf','1778568917_6e84f3bb.jpg','Reguler'),(15,'TestingArsip','0102030405','$2y$10$RpVvAHUdhVIJjUxfPFJk0eypZR.61eeASSpVdPq/IVQqgb8/68FW2','siswa','2026-05-12 13:24:02',NULL,NULL,NULL,NULL,'diterima','2027/2028',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Reguler'),(16,'TestingLagiArsip','0908070600','$2y$10$WRY8V9OpKRGKpnDCwgrRSOUkReWulLs8PkEQwSRu7wHrk5BF0HGFC','siswa','2026-05-12 13:25:24',NULL,NULL,NULL,NULL,'diterima','2027/2028',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Reguler');
/*!40000 ALTER TABLE `users_siswa` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-12 23:21:04
