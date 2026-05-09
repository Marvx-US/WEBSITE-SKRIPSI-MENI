<?php
session_start();
include '../config/helpers.php';
include '../config/koneksi.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require '../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$status_filter = $_GET['status'] ?? 'diterima';
$tahun_filter  = $_GET['tahun'] ?? '';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$title_suffix = '';
if ($tahun_filter !== '') {
    $title_suffix = ' TA ' . $tahun_filter;
}

if ($status_filter === 'all') {
    $sheet->setTitle('Data Siswa (Semua)');
    $sheet->setCellValue('A1', 'DATA CALON SISWA (SEMUA STATUS)' . $title_suffix);
} else {
    $sheet->setTitle('Data Siswa ' . ucfirst($status_filter));
    $sheet->setCellValue('A1', 'DATA CALON SISWA (' . strtoupper($status_filter) . ')' . $title_suffix);
}

$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$sheet->setCellValue('A3', 'NO');
$sheet->setCellValue('B3', 'NISN');
$sheet->setCellValue('C3', 'NAMA LENGKAP');
$sheet->setCellValue('D3', 'ASAL SEKOLAH');
$sheet->setCellValue('E3', 'TEMPAT, TANGGAL LAHIR');
$sheet->setCellValue('F3', 'JENIS KELAMIN');
$sheet->setCellValue('G3', 'NAMA AYAH');
$sheet->setCellValue('H3', 'STATUS');


$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF10B27C']], 
];
$sheet->getStyle('A3:H3')->applyFromArray($headerStyle);


if ($status_filter === 'all') {
    if ($tahun_filter !== '') {
        $stmt = $pdo->prepare("SELECT * FROM users_siswa WHERE tahun_ajaran = ? ORDER BY nama_lengkap ASC");
        $stmt->execute([$tahun_filter]);
    } else {
        $stmt = $pdo->query("SELECT * FROM users_siswa ORDER BY nama_lengkap ASC");
    }
} else {
    if ($tahun_filter !== '') {
        $stmt = $pdo->prepare("SELECT * FROM users_siswa WHERE status = ? AND tahun_ajaran = ? ORDER BY nama_lengkap ASC");
        $stmt->execute([$status_filter, $tahun_filter]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users_siswa WHERE status = ? ORDER BY nama_lengkap ASC");
        $stmt->execute([$status_filter]);
    }
}

$rowIdx = 4;
$no = 1;

while ($row = $stmt->fetch()) {
    $ttl = ($row['tempat_lahir'] ?? '-') . ', ' . ($row['tanggal_lahir'] ?? '-');
    $jk = ($row['jenis_kelamin'] ?? '') == 'L' ? 'Laki-laki' : (($row['jenis_kelamin'] ?? '') == 'P' ? 'Perempuan' : '-');

    $sheet->setCellValue('A' . $rowIdx, $no++);
    
    $sheet->setCellValueExplicit('B' . $rowIdx, $row['nisn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowIdx, $row['nama_lengkap']);
    $sheet->setCellValue('D' . $rowIdx, $row['nama_sd'] ?? '-');
    $sheet->setCellValue('E' . $rowIdx, $ttl);
    $sheet->setCellValue('F' . $rowIdx, $jk);
    $sheet->setCellValue('G' . $rowIdx, $row['nama_ayah'] ?? '-');
    $sheet->setCellValue('H' . $rowIdx, ucfirst($row['status'] ?: 'Pending'));

    $rowIdx++;
}


$bodyStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle('A4:H' . ($rowIdx - 1))->applyFromArray($bodyStyle);


foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}


$tahun_str = $tahun_filter !== '' ? '_' . str_replace('/', '-', $tahun_filter) : '';
$filename = 'Data_Siswa_' . ucfirst($status_filter) . $tahun_str . '_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
