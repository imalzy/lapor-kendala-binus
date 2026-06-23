<?php

$id_laporan = isset($_GET['id_laporan']) ? mysqli_real_escape_string($koneksi, $_GET['id_laporan']) : '';

$base_query = "SELECT l.*, p.nama_pegawai, r.nama_ruangan, k.nama_kategori 
               FROM laporan l
               JOIN pegawai p ON l.id_pegawai = p.id_pegawai
               JOIN ruangan r ON l.id_ruangan = r.id_ruangan
               JOIN kategori_kendala k ON l.id_kategori = k.id_kategori";

// PILAH DATA BERDASARKAN ROLE USER YANG SEDANG MENGAKSES
if (!empty($id_laporan)) {
    $query = $base_query . " WHERE l.id_laporan = '$id_laporan'";
} elseif ($_USER_DATA['role'] === 'pegawai') {
    $query = $base_query . " WHERE l.id_pegawai = '" . $_USER_DATA['id_pegawai'] . "' ORDER BY l.created_at DESC";
} elseif ($_USER_DATA['role'] === 'teknisi') {
    $query = $base_query . " WHERE l.assigned_to = '" . $_USER_DATA['unit_teknisi'] . "' AND l.status != 'Resolved' ORDER BY l.urgensi DESC, l.created_at ASC";
} else {
    $query = $base_query . " ORDER BY l.created_at DESC"; // Admin Utama melihat semua tanpa batas
}

$result = mysqli_query($koneksi, $query);
$data = [];
while ($row = mysqli_fetch_assoc($result)) { $data[] = $row; }

if (!empty($id_laporan) && empty($data)) {
    response_api(404, false, "Laporan tidak ditemukan.");
} else {
    response_api(200, true, "Sukses memuat data.", (!empty($id_laporan)) ? $data[0] : $data);
}