<?php
// =========================================================
// data_json.php
// REST API: menampilkan seluruh data users dari database lokal
// Akses: http://localhost/ujian_asts/data_json.php
// =========================================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "auth_check.php";
require_once "config.php";

$sql    = "SELECT id, name, nisn, ttl, gender, email, address FROM users ORDER BY id ASC";
$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
