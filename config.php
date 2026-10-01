<?php
// =========================================================
// KONEKSI DATABASE
// Sesuaikan $user dan $pass jika MySQL/XAMPP Anda memakainya
// =========================================================

$host   = "localhost";
$user   = "root";
$pass   = "zwei1";
$dbname = "ujian_asts";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    header("Content-Type: application/json; charset=UTF-8");
    die(json_encode([
        "status"  => "error",
        "message" => "Koneksi database gagal: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");
?>
