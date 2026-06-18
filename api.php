<?php
require_once 'koneksi.php';
require_once 'helpers/response_helper.php';
require_once 'helpers/jwt_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET["action"]) ? $_GET["action"] : "";

switch ($action) {
    case 'login':
       if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $nik = mysqli_real_escape_string($koneksi, $data['nik'] ?? '');
            $password = $data['password'] ?? '';

            $query = "SELECT * FROM pegawai WHERE nik = '$nik'";
            $result = mysqli_query($koneksi, $query);

            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    $payload = [
                        "id_pegawai" => $user['id_pegawai'],
                        "nik" => $user['nik'],
                        "nama_pegawai" => $user['nama_pegawai'],
                        "role" => $user['role'],
                        "unit_teknisi" => $user['unit_teknisi'],
                        "exp" => time() + (2*60*60) // Aktif 2 Jam
                    ];
                    $token = generate_jwt($payload, $jwt_secret);
                    response_api(200, true, "Login Berhasil", ["token" => $token, "role" => $user['role']]);
                } else {
                    response_api(401, false, "Password salah.");
                }
            } else {
                response_api(404, false, "NIK tidak terdaftar.");
            }
        } else {
            response_api(405, false, "Method Not Allowed");
        }
        break;
    case 'register':
        if ($method === 'POST') {
            $headers = getallheaders();

            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $_USER_DATA = validate_jwt($matches[1], $jwt_secret);
                if (!$_USER_DATA || $_USER_DATA['role'] !== 'admin') {
                    response_api(403, false, "Akses Ditolak: Hanya admin yang boleh mendaftarkan pegawai");
                }
            } else {
                response_api(401, false, "Akses ditolak: Token Tidak Valid");
            }

            $data = json_decode((file_get_contents("php://input")), true);
            $nik = mysqli_real_escape_string($koneksi, $data['nik'] ?? '');
            $nama_pegawai = mysqli_real_escape_string($koneksi, $data['nama_pegawai'] ?? '');
            $password = mysqli_real_escape_string($koneksi, $data['password'] ?? '');
            $role = mysqli_real_escape_string($koneksi, $data['role'] ?? '');
            $unit_teknisi = mysqli_real_escape_string($koneksi, $data['unit_teknisi'] ?? '');

            if (empty($nik) || empty($nama_pegawai) || empty($password)) {
                response_api(400, false, "Data tidak lengkap. NIK, Nama Pegawai dan Password Wajib Diisi");
            }

            $allow_roles = ['pegawai', 'admin', 'teknisi'];
            if (!in_array($role, $allow_roles)) {
                response_api(400, false, "Role tidak valid. Pilih: pegawai, admin atau teknisi");
            }

            $check_nik = mysqli_query($koneksi, "SELECT nik FROM pegawai WHERE nik='$nik'");
            if (mysqli_num_rows($check_nik) > 0) {
                response_api(400, false, "Daftar Gagal. NIK sudah terdaftar!");
            }

            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $query = "INSERT INTO pegawai (nik, nama_pegawai, password, role, unit_teknisi) VALUES ('$nik', '$nama_pegawai', '$password_hash', '$role', '$unit_teknisi')";

            if (mysqli_query($koneksi, $query)) {
                response_api(201, true, "Akun pegawai baru berhasil didaftarkan");
            } else {
                response_api(500, false, "Gagal menyimpan data ke database", mysqli_error($koneksi));
            }
        } else {
            response_api(405, false, "Method Not Allowed");
        }
        break;
    default:
        # code...
        break;
}
