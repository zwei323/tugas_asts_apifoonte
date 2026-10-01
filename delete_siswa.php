<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "config.php";
require_once "fonnte.php";


try {

    // ==========================================
    // CEK LOGIN
    // ==========================================

    if (!isset($_SESSION["siswa_id"])) {

        http_response_code(401);

        echo json_encode([
            "status" => "error",
            "message" => "Anda belum login."
        ]);

        exit;
    }


    $siswaId =
        (int) $_SESSION["siswa_id"];


    // ==========================================
    // AMBIL DATA SISWA
    // ==========================================

    $stmt =
        $conn->prepare(
            "SELECT
                name,
                email,
                nisn,
                no_hp
             FROM users
             WHERE id = ?
             LIMIT 1"
        );


    $stmt->bind_param(
        "i",
        $siswaId
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 0) {

        throw new Exception(
            "Data siswa tidak ditemukan."
        );
    }


    $siswa =
        $result->fetch_assoc();


    $stmt->close();


    // ==========================================
    // SIAPKAN WHATSAPP
    // ==========================================

    $nomorWA =
        $siswa["no_hp"] ?? "";


    $pesanWA =
        "🗑️ *AKUN SISWA DIHAPUS*\n\n" .

        "Halo *" .
        $siswa["name"] .
        "*.\n\n" .

        "Akun siswa Anda telah dihapus " .
        "dari sistem.\n\n" .

        "🆔 NISN: " .
        $siswa["nisn"] .
        "\n" .

        "📧 Email: " .
        $siswa["email"] .
        "\n\n" .

        "Data akun Anda sudah tidak dapat digunakan " .
        "untuk login ke sistem.\n\n" .

        "Terima kasih.";


    // ==========================================
    // KIRIM WHATSAPP SEBELUM DELETE
    // ==========================================

    $hasilWA = null;


    if ($nomorWA !== "") {

        $hasilWA =
            kirimWhatsApp(
                $nomorWA,
                $pesanWA
            );
    }


    // ==========================================
    // HAPUS USER
    // ==========================================

    $hapus =
        $conn->prepare(
            "DELETE FROM users
             WHERE id = ?"
        );


    if (!$hapus) {

        throw new Exception(
            "Gagal menyiapkan proses hapus: " .
            $conn->error
        );
    }


    $hapus->bind_param(
        "i",
        $siswaId
    );


    if (!$hapus->execute()) {

        throw new Exception(
            "Gagal menghapus akun: " .
            $hapus->error
        );
    }


    $hapus->close();


    // ==========================================
    // HAPUS SESSION
    // ==========================================

    $_SESSION = [];

    session_destroy();


    // ==========================================
    // RESPONSE
    // ==========================================

    echo json_encode([
        "status" => "success",
        "message" =>
            "Akun siswa berhasil dihapus.",
        "wa_status" =>
            $hasilWA["status"] ?? "failed"
    ]);


    $conn->close();


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

?>