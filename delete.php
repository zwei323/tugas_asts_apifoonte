<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once "config.php";
require_once "fonnte.php";

try {

    // ==========================================
    // CEK LOGIN ADMIN
    // ==========================================

    if (!isset($_SESSION["admin_id"])) {

        http_response_code(401);

        echo json_encode([
            "status" => "error",
            "message" => "Anda belum login sebagai admin."
        ]);

        exit;
    }


    // ==========================================
    // CEK METHOD
    // ==========================================

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        http_response_code(405);

        echo json_encode([
            "status" => "error",
            "message" => "Method tidak diizinkan."
        ]);

        exit;
    }


    // ==========================================
    // AMBIL ID SISWA
    // ==========================================

    $id = (int) ($_POST["id"] ?? 0);


    if ($id <= 0) {

        throw new Exception(
            "ID siswa tidak valid."
        );
    }


    // ==========================================
    // AMBIL DATA SISWA SEBELUM DIHAPUS
    // ==========================================

    $stmt = $conn->prepare(
        "SELECT
            id,
            name,
            nisn,
            ttl,
            gender,
            email,
            no_hp,
            address
         FROM users
         WHERE id = ?
         LIMIT 1"
    );


    if (!$stmt) {

        throw new Exception(
            "Gagal menyiapkan query: " .
            $conn->error
        );
    }


    $stmt->bind_param(
        "i",
        $id
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 0) {

        $stmt->close();

        throw new Exception(
            "Data siswa tidak ditemukan."
        );
    }


    $siswa =
        $result->fetch_assoc();


    $stmt->close();


    // ==========================================
    // AMBIL DATA SISWA
    // ==========================================

    $namaSiswa =
        $siswa["name"];

    $nisnSiswa =
        $siswa["nisn"];

    $emailSiswa =
        $siswa["email"];

    $noHp =
        $siswa["no_hp"];


    // ==========================================
    // FORMAT NOMOR WHATSAPP
    // ==========================================

    $nomorWA =
        preg_replace(
            '/[^0-9]/',
            '',
            $noHp
        );


    if (
        substr($nomorWA, 0, 1) === "0"
    ) {

        $nomorWA =
            "62" .
            substr($nomorWA, 1);
    }


    // ==========================================
    // PESAN WHATSAPP
    // ==========================================

    $pesanWA =
        "🗑️ *AKUN SISWA DIHAPUS*\n\n" .

        "Halo *" . $namaSiswa . "*.\n\n" .

        "Kami memberitahukan bahwa akun siswa Anda " .
        "telah dihapus dari sistem oleh administrator.\n\n" .

        "📝 Nama: " . $namaSiswa . "\n" .
        "🆔 NISN: " . $nisnSiswa . "\n" .
        "📧 Email: " . $emailSiswa . "\n\n" .

        "Akun tersebut sudah tidak dapat digunakan " .
        "untuk login ke sistem.\n\n" .

        "Jika Anda merasa tidak melakukan permintaan " .
        "penghapusan akun ini, silakan hubungi administrator.\n\n" .

        "Terima kasih.";


    // ==========================================
    // KIRIM WHATSAPP
    // ==========================================

    $waResult = null;

    $waBerhasil = false;


    // Hanya kirim jika nomor tersedia dan valid
    if (
        !empty($nomorWA) &&
        strlen($nomorWA) >= 10 &&
        strlen($nomorWA) <= 15
    ) {

        $waResult =
            kirimWhatsApp(
                $nomorWA,
                $pesanWA
            );


        if (
            isset($waResult["status"]) &&
            $waResult["status"] === true
        ) {

            $waBerhasil = true;
        }
    }


    // ==========================================
    // HAPUS DATA SISWA
    // ==========================================

    $deleteStmt = $conn->prepare(
        "DELETE FROM users
         WHERE id = ?"
    );


    if (!$deleteStmt) {

        throw new Exception(
            "Gagal menyiapkan penghapusan: " .
            $conn->error
        );
    }


    $deleteStmt->bind_param(
        "i",
        $id
    );


    if (!$deleteStmt->execute()) {

        throw new Exception(
            "Gagal menghapus data siswa: " .
            $deleteStmt->error
        );
    }


    $deleteStmt->close();


    // ==========================================
    // RESPONSE
    // ==========================================

    if ($waBerhasil) {

        echo json_encode([
            "status" => "success",
            "message" =>
                "Akun siswa berhasil dihapus dan notifikasi WhatsApp berhasil dikirim.",
            "whatsapp" => "terkirim"
        ]);

    } else {

        echo json_encode([
            "status" => "success",
            "message" =>
                "Akun siswa berhasil dihapus, tetapi notifikasi WhatsApp gagal dikirim.",
            "whatsapp" => "gagal",
            "wa_response" => $waResult
        ]);
    }


    $conn->close();


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

?>