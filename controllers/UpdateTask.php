<?php
// controllers/UpdateTask.php

$data = json_decode(file_get_contents("php://input"), true);

if ($data && $_USER_DATA['role'] === 'admin') {
    $id_laporan  = mysqli_real_escape_string($koneksi, $data['id_laporan'] ?? '');
    $assigned_to = mysqli_real_escape_string($koneksi, $data['assigned_to'] ?? '');

    if (empty($id_laporan) || empty($assigned_to)) response_api(400, false, "Parameter tidak lengkap.");
    
    $query = "UPDATE laporan SET assigned_to = '$assigned_to' WHERE id_laporan = '$id_laporan'";
    if (mysqli_query($koneksi, $query)) response_api(200, true, "Laporan didelegasikan ulang ke unit $assigned_to.");
    response_api(500, false, "Gagal memperbarui database.");
} else {
    // SKENARIO B: TEKNISI UPDATE STATUS & UPLOAD BUKTI FOTO SELESAI (FORM DATA)
    $id_laporan = isset($_POST['id_laporan']) ? mysqli_real_escape_string($koneksi, $_POST['id_laporan']) : '';
    $status     = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : '';
    $catatan    = isset($_POST['catatan_perbaikan']) ? mysqli_real_escape_string($koneksi, $_POST['catatan_perbaikan']) : '';

    if (empty($id_laporan) || empty($status)) response_api(400, false, "ID Laporan dan Status wajib diisi.");

    $query_upload = "";
    if (isset($_FILES['foto_selesai']) && $_FILES['foto_selesai']['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['foto_selesai']['name'], PATHINFO_EXTENSION));
        $foto_name = time() . "_selesai_" . uniqid() . "." . $file_ext;
        move_uploaded_file($_FILES['foto_selesai']['tmp_name'], "uploads/" . $foto_name);
        $query_upload = ", foto_selesai = '$foto_name'";
    }

    $query = "UPDATE laporan SET status = '$status', catatan_perbaikan = '$catatan' $query_upload WHERE id_laporan = '$id_laporan'";
    if (mysqli_query($koneksi, $query)) response_api(200, true, "Status laporan berhasil diubah menjadi $status.");
    response_api(500, false, "Gagal memperbarui status teknisi.");
}