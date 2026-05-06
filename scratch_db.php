<?php
include 'config/koneksi.php';
$pdo->exec('CREATE TABLE IF NOT EXISTS ppdb_pengumuman (id INT AUTO_INCREMENT PRIMARY KEY, judul VARCHAR(255), isi TEXT, tgl_buat TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');
echo "Table created";
