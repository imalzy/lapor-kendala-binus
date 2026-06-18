<?php
// controllers/CreateTask.php

$id_pegawai  = $_USER_DATA['id_pegawai']; // Ambil aman langsung dari data token JWT login
$id_ruangan  = isset($_POST['id_ruangan']) ? mysqli_real_escape_string($koneksi, $_POST['id_ruangan']) : '';
$id_kategori = isset($_POST['id_kategori']) ? mysqli_real_escape_string($koneksi, $_POST['id_kategori']) : '';
$deskripsi   = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : '';
$urgensi     = isset($_POST['urgensi']) ? mysqli_real_escape_string($koneksi, $_POST['urgensi']) : 'Sedang';

if (empty($id_ruangan) || empty($id_kategori) || empty($deskripsi)) {
    response_api(400, false, "Data pelaporan tidak lengkap.");
}

// Ambil otomatisasi unit penanggung jawab berdasarkan kategori
$q_kat = mysqli_query($koneksi, "SELECT default_unit FROM kategori_kendala WHERE id_kategori = '$id_kategori'");
if (mysqli_num_rows($q_kat) > 0) {
    $kat = mysqli_fetch_assoc($q_kat);
    $assigned_to = $kat['default_unit'];
} else {
    response_api(404, false, "Kategori tidak valid.");
}
$foto_name = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, ['jpg', 'jpeg', 'png'])) response_api(400, false, "Format foto wajib JPG/PNG.");
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) response_api(400, false, "Ukuran foto maksimal 5MB.");
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $foto_name = time() . "_kendala_" . uniqid() . "." . $file_ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_name);
}

$query = "INSERT INTO laporan (id_pegawai, id_ruangan, id_kategori, deskripsi, foto_kendala, urgensi, assigned_to, status) 
          VALUES ('$id_pegawai', '$id_ruangan', '$id_kategori', '$deskripsi', " . ($foto_name ? "'$foto_name'" : "NULL") . ", '$urgensi', '$assigned_to', 'Open')";

if (mysqli_query($koneksi, $query)) {
    response_api(201, true, "Laporan berhasil terkirim otomatis ke unit: $assigned_to");
} else {
    response_api(500, false, "Database error: " . mysqli_error($koneksi));
}
?>