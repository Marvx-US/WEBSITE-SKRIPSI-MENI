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
INSERT INTO `users_admin` (`username`, `password`, `nama_lengkap`, `role`) VALUES ('admin','$2y$10$b0VdGPJg6rm6BBQ0Q4UF4e/09oWark5AX.vHiUGWTW5Rf6k4y8H0q','Administrator','superadmin');
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
  `jalur_pendaftaran` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Reguler',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
