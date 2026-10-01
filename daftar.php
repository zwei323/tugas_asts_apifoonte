<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "config.php";
require_once "fonnte.php";


try {

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
    // AMBIL DATA
    // ==========================================

    $name = trim(
        $_POST["name"] ?? ""
    );

    $nisn = trim(
        $_POST["nisn"] ?? ""
    );

    $ttl = trim(
        $_POST["ttl"] ?? ""
    );

    $gender = trim(
        $_POST["gender"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    // PASSWORD YANG DIBUAT SISWA SAAT DAFTAR
    $password = $_POST["password"] ?? "";

    $no_hp = trim(
        $_POST["no_hp"] ?? ""
    );

    $address = trim(
        $_POST["address"] ?? ""
    );


    // ==========================================
    // VALIDASI
    // ==========================================

    if ($name === "") {

        throw new Exception(
            "Nama wajib diisi."
        );
    }


    if ($nisn === "") {

        throw new Exception(
            "NISN wajib diisi."
        );
    }


    if ($ttl === "") {

        throw new Exception(
            "Tempat dan tanggal lahir wajib diisi."
        );
    }


    if ($gender === "") {

        throw new Exception(
            "Jenis kelamin wajib dipilih."
        );
    }


    if ($email === "") {

        throw new Exception(
            "Email wajib diisi."
        );
    }


    if (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        throw new Exception(
            "Format email tidak valid."
        );
    }


    // ==========================================
    // VALIDASI PASSWORD
    // ==========================================

    if ($password === "") {

        throw new Exception(
            "Password wajib diisi."
        );
    }


    if (strlen($password) < 6) {

        throw new Exception(
            "Password minimal 6 karakter."
        );
    }


    if ($no_hp === "") {

        throw new Exception(
            "Nomor WhatsApp wajib diisi."
        );
    }


    if ($address === "") {

        throw new Exception(
            "Alamat wajib diisi."
        );
    }


    // ==========================================
    // FORMAT NOMOR WHATSAPP
    // ==========================================

    $nomorWA = preg_replace(
        '/[^0-9]/',
        '',
        $no_hp
    );


    if (
        substr($nomorWA, 0, 1) === "0"
    ) {

        $nomorWA =
            "62" .
            substr($nomorWA, 1);
    }


    if (
        strlen($nomorWA) < 10 ||
        strlen($nomorWA) > 15
    ) {

        throw new Exception(
            "Nomor WhatsApp tidak valid."
        );
    }


    // ==========================================
    // HASH PASSWORD
    // ==========================================

    /*
     * Password asli dari siswa TIDAK disimpan
     * langsung ke database.
     *
     * Contoh:
     * Password siswa:
     * Budi12345
     *
     * Yang disimpan ke database:
     * $2y$12$....................
     */

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );


    if ($passwordHash === false) {

        throw new Exception(
            "Gagal membuat hash password."
        );
    }


    // ==========================================
    // CEK EMAIL
    // ==========================================

    $cekEmail = $conn->prepare(
        "SELECT id
         FROM users
         WHERE email = ?
         LIMIT 1"
    );


    if (!$cekEmail) {

        throw new Exception(
            "Gagal memeriksa email: " .
            $conn->error
        );
    }


    $cekEmail->bind_param(
        "s",
        $email
    );


    $cekEmail->execute();


    $hasilEmail =
        $cekEmail->get_result();


    if (
        $hasilEmail->num_rows > 0
    ) {

        $cekEmail->close();

        throw new Exception(
            "Email tersebut sudah terdaftar."
        );
    }


    $cekEmail->close();


    // ==========================================
    // SIMPAN DATA SISWA
    // ==========================================

    $stmt = $conn->prepare(
        "INSERT INTO users
        (
            name,
            nisn,
            ttl,
            gender,
            email,
            password,
            no_hp,
            wa_status,
            address
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'pending',
            ?
        )"
    );


    if (!$stmt) {

        throw new Exception(
            "Gagal menyiapkan data siswa: " .
            $conn->error
        );
    }


    /*
     * Ada 8 nilai:
     *
     * name
     * nisn
     * ttl
     * gender
     * email
     * passwordHash
     * nomorWA
     * address
     */

    $stmt->bind_param(
        "ssssssss",
        $name,
        $nisn,
        $ttl,
        $gender,
        $email,
        $passwordHash,
        $nomorWA,
        $address
    );


    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal menyimpan data siswa: " .
            $stmt->error
        );
    }


    $userId =
        $stmt->insert_id;


    $stmt->close();


    // ==========================================
    // BUAT PESAN WHATSAPP
    // ==========================================

    $pesan =
        buatPesanPendaftaran(
            $name,
            $nisn,
            $ttl,
            $gender,
            $email,
            $nomorWA,
            $address
        );


    // ==========================================
    // KIRIM WHATSAPP
    // ==========================================

    $hasilWA =
        kirimWhatsApp(
            $nomorWA,
            $pesan
        );


    $waStatus =
        $hasilWA["status"]
        ?? "failed";


    $responseWA =
        $hasilWA["response"]
        ?? "";


    // ==========================================
    // KONVERSI STATUS WA
    // ==========================================
    /*
     * users.wa_status:
     * pending
     * terkirim
     * gagal
     *
     * Sedangkan hasil Fonnte:
     * success
     * failed
     */


    if ($waStatus === "success") {

        $dbWaStatus = "terkirim";

    } else {

        $dbWaStatus = "gagal";
    }


    // ==========================================
    // UPDATE STATUS WA DI USERS
    // ==========================================

    $update =
        $conn->prepare(
            "UPDATE users
             SET wa_status = ?
             WHERE id = ?"
        );


    if ($update) {

        $update->bind_param(
            "si",
            $dbWaStatus,
            $userId
        );

        $update->execute();

        $update->close();
    }


    // ==========================================
    // STATUS UNTUK LOG WHATSAPP
    // ==========================================

    if ($waStatus === "success") {

        $logStatus = "success";

    } else {

        $logStatus = "failed";
    }


    // ==========================================
    // SIMPAN LOG WHATSAPP
    // ==========================================

    $log =
        $conn->prepare(
            "INSERT INTO log_whatsapp
            (
                user_id,
                no_hp,
                no_tujuan,
                pesan,
                status,
                response
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )"
        );


    if ($log) {

        $log->bind_param(
            "isssss",
            $userId,
            $nomorWA,
            $nomorWA,
            $pesan,
            $logStatus,
            $responseWA
        );

        $log->execute();

        $log->close();
    }


    // ==========================================
    // RESPONSE
    // ==========================================

    if ($waStatus === "success") {

        echo json_encode([
            "status" => "success",
            "message" =>
                "Pendaftaran berhasil. " .
                "Password berhasil disimpan dan " .
                "notifikasi WhatsApp berhasil dikirim.",
            "id" => $userId,
            "no_hp" => $nomorWA,
            "wa_status" => "terkirim"
        ]);

    } else {

        echo json_encode([
            "status" => "success",
            "message" =>
                "Pendaftaran berhasil. " .
                "Password berhasil disimpan, tetapi " .
                "notifikasi WhatsApp belum berhasil dikirim.",
            "id" => $userId,
            "no_hp" => $nomorWA,
            "wa_status" => "gagal"
        ]);
    }


    // ==========================================
    // TUTUP DATABASE
    // ==========================================

    $conn->close();


} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);

}

?>