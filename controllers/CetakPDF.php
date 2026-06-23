<?php
// controllers/CetakPDF.php
require_once 'libraries/fpdf.php';

$query = "SELECT l.*, p.nama_pegawai, r.nama_ruangan, k.nama_kategori 
          FROM laporan l
          JOIN pegawai p ON l.id_pegawai = p.id_pegawai
          JOIN ruangan r ON l.id_ruangan = r.id_ruangan
          JOIN kategori_kendala k ON l.id_kategori = k.id_kategori 
          ORDER BY l.created_at DESC";
$result = mysqli_query($koneksi, $query);

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', '14');
$pdf->Cell(0, 7, 'REKAPITULASI LAPORAN KENDALA PELAYANAN RUANGAN', 0, 1, 'C');
$pdf->Ln(5);

// Header Tabel PDF
$pdf->SetFont('Arial', 'B', '10');
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(45, 8, 'Nama Pelapor', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Ruangan', 1, 0, 'C', true);
$pdf->Cell(65, 8, 'Kategori Kendala', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'Urgensi', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'Status', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', '9');
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(10, 7, $no++, 1, 0, 'C');
    $pdf->Cell(35, 7, date('d-m-Y H:i', strtotime($row['created_at'])), 1, 0, 'C');
    $pdf->Cell(45, 7, substr($row['nama_pegawai'], 0, 22), 1, 0, 'L');
    $pdf->Cell(35, 7, substr($row['nama_ruangan'], 0, 18), 1, 0, 'L');
    $pdf->Cell(65, 7, substr($row['nama_kategori'], 0, 35), 1, 0, 'L');
    $pdf->Cell(25, 7, $row['urgensi'], 1, 0, 'C');
    $pdf->Cell(30, 7, $row['status'], 1, 1, 'C');
}

$pdf->Output('I', 'Rekap_Laporan_Kendala.pdf');
exit();