<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once "auth_check.php";
require_once "config.php";
require_once "fonnte.php";

try {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);

        echo json_encode([
            "status" => "error",
            "message" => "Method tidak diizinkan."
        ]);
        exit;
    }

    // =========================
    // AMBIL DATA FORM
    // =========================

    $name    = trim($_POST["name"] ?? "");
    $nisn    = trim($_POST["nisn"] ?? "");
    $ttl     = trim($_POST["ttl"] ?? "");
    $gender  = trim($_POST["gender"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $no_hp   = trim($_POST["no_hp"] ?? "");
    $address = trim($_POST["address"] ?? "");


    // =========================
    // VALIDASI
    // =========================

    if ($name === "") {
        echo json_encode([
            "status" => "error",
            "message" => "Nama wajib diisi."
        ]);
        exit;
    }

    if ($email === "") {
        echo json_encode([
            "status" => "error",
            "message" => "Email wajib diisi."
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "status" => "error",
            "message" => "Format email tidak valid."
        ]);
        exit;
    }

    if ($no_hp === "") {
        echo json_encode([
            "status" => "error",
            "message" => "Nomor HP / WhatsApp wajib diisi."
        ]);
        exit;
    }


    // =========================
    // FORMAT NOMOR WHATSAPP
    // =========================

    $nomorWA = preg_replace('/[^0-9]/', '', $no_hp);

    if (substr($nomorWA, 0, 1) === "0") {
        $nomorWA = "62" . substr($nomorWA, 1);
    }

    if (strlen($nomorWA) < 10 || strlen($nomorWA) > 15) {
        echo json_encode([
            "status" => "error",
            "message" => "Nomor WhatsApp tidak valid."
        ]);
        exit;
    }


    // =========================
    // CEK EMAIL
    // =========================

    $cek = $conn->prepare(
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );

    if (!$cek) {
        throw new Exception(
            "Prepare cek email gagal: " . $conn->error
        );
    }

    $cek->bind_param("s", $email);

    if (!$cek->execute()) {
        throw new Exception(
            "Eksekusi cek email gagal: " . $cek->error
        );
    }

    $resultCek = $cek->get_result();

    if ($resultCek->num_rows > 0) {

        echo json_encode([
            "status" => "error",
            "message" => "Email sudah terdaftar."
        ]);

        exit;
    }


    // =========================
    // SIMPAN DATA SISWA
    // =========================

    $stmt = $conn->prepare(
        "INSERT INTO users
        (
            name,
            nisn,
            ttl,
            gender,
            email,
            no_hp,
            wa_status,
            address
        )
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)"
    );

    if (!$stmt) {
        throw new Exception(
            "Prepare INSERT users gagal: " . $conn->error
        );
    }

    $stmt->bind_param(
        "sssssss",
        $name,
        $nisn,
        $ttl,
        $gender,
        $email,
        $nomorWA,
        $address
    );

    if (!$stmt->execute()) {
        throw new Exception(
            "Gagal menyimpan data siswa: " . $stmt->error
        );
    }

    $userId = $stmt->insert_id;


    // =========================
    // BUAT PESAN WHATSAPP
    // =========================

    $pesan = buatPesanPendaftaran(
        $name,
        $nisn,
        $ttl,
        $gender,
        $email,
        $nomorWA,
        $address
    );


    // =========================
    // KIRIM WHATSAPP
    // =========================

    $hasilWA = kirimWhatsApp(
        $nomorWA,
        $pesan
    );

    $waStatus = $hasilWA["status"] ?? "failed";

    $responseWA = $hasilWA["response"] ?? "";


    // =========================
    // UPDATE STATUS WA
    // =========================

    $update = $conn->prepare(
        "UPDATE users
         SET wa_status = ?
         WHERE id = ?"
    );

    if (!$update) {
        throw new Exception(
            "Prepare UPDATE wa_status gagal: " . $conn->error
        );
    }

    $update->bind_param(
        "si",
        $waStatus,
        $userId
    );

    if (!$update->execute()) {
        throw new Exception(
            "Gagal update status WhatsApp: " . $update->error
        );
    }


    // =========================
    // SIMPAN LOG WHATSAPP
    // =========================

    $log = $conn->prepare(
        "INSERT INTO log_whatsapp
        (
            user_id,
            no_hp,
            no_tujuan,
            pesan,
            status,
            response
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$log) {
        throw new Exception(
            "Prepare log WhatsApp gagal: " . $conn->error
        );
    }

    $log->bind_param(
        "isssss",
        $userId,
        $nomorWA,
        $nomorWA,
        $pesan,
        $waStatus,
        $responseWA
    );

    if (!$log->execute()) {
        throw new Exception(
            "Gagal menyimpan log WhatsApp: " . $log->error
        );
    }


    // =========================
    // RESPONSE
    // =========================

    if ($waStatus === "success") {

        echo json_encode([
            "status" => "success",
            "message" => "Data siswa berhasil disimpan dan WhatsApp berhasil dikirim.",
            "id" => $userId,
            "no_hp" => $nomorWA,
            "wa_status" => "terkirim"
        ]);

    } else {

        echo json_encode([
            "status" => "success",
            "message" => "Data siswa berhasil disimpan, tetapi WhatsApp gagal dikirim.",
            "id" => $userId,
            "no_hp" => $nomorWA,
            "wa_status" => "gagal",
            "fonnte_response" => $responseWA
        ]);
    }


    // =========================
    // TUTUP
    // =========================

    $cek->close();
    $stmt->close();
    $update->close();
    $log->close();
    $conn->close();


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}

?>