<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = "localhost";
$database = "db_todo";
$username = "root";
$password = "";

// Buat koneksi database
$koneksi = mysqli_connect($host, $username, $password, $database);

if(!$koneksi){
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Internal Server Error " . mysqli_connect_error()
    ]);
    exit();
}

?>