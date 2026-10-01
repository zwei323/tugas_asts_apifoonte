<?php
// =========================================================
// auth_check.php
// Include file ini di setiap endpoint API agar hanya bisa
// diakses oleh user yang sudah login (session aktif)
// =========================================================
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(401);
    echo json_encode([
        "status"  => "error",
        "message" => "Anda belum login. Silakan login terlebih dahulu."
    ]);
    exit;
}
?>
