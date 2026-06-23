<?php 
//  Tangkap id_laporan // 1
// validasi, apakah id_laporan ada ?? 
// jika tidak (error response)
/// jika y (hapus data pada table)

// konsep hapus.
// 1. hard delete -> hapus permanen, dan umumnya tidak diperbolehkan untuk data penting (data pasien, riwayat penyakit)
// 2. soft delete ->  tidak hapus, melainkan di sembunyikan (show / hide pada table)

$data = json_decode(file_get_contents("php://input"), true);
$id_laporan = mysqli_real_escape_string($koneksi, $data['id_laporan'] ?? '');

if (empty($id_laporan)) kirim_respon(400, false, "ID Laporan wajib disertakan.");

$check = mysqli_query($koneksi, "SELECT id_laporan FROM laporan WHERE id_laporan = '$id_laporan'");
if (mysqli_num_rows($check) === 0) kirim_respon(404, false, "Data tidak ditemukan.");

if (mysqli_query($koneksi, "DELETE FROM laporan WHERE id_laporan = '$id_laporan'")) {
    kirim_respon(200, true, "Data laporan kendala berhasil dihapus.");
} else {
    kirim_respon(500, false, "Gagal mengeksekusi penghapusan data.");
}

?>