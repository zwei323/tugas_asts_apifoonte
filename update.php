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
            "message" => "Anda belum login sebagai siswa."
        ]);

        exit;
    }


    $siswaId =
        (int) $_SESSION["siswa_id"];


    // ==========================================
    // GET = AMBIL DATA
    // ==========================================

    if ($_SERVER["REQUEST_METHOD"] === "GET") {

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


        echo json_encode([
            "status" => "success",
            "data" => $siswa
        ]);


        $conn->close();

        exit;
    }


    // ==========================================
    // POST = UPDATE
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

    $name =
        trim($_POST["name"] ?? "");

    $nisn =
        trim($_POST["nisn"] ?? "");

    $ttl =
        trim($_POST["ttl"] ?? "");

    $gender =
        trim($_POST["gender"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $no_hp =
        trim($_POST["no_hp"] ?? "");

    $address =
        trim($_POST["address"] ?? "");

    $password =
        $_POST["password"] ?? "";

    $passwordConfirm =
        $_POST["password_confirm"] ?? "";


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

    $nomorWA =
        preg_replace(
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
    // CEK EMAIL MILIK USER LAIN
    // ==========================================

    $cekEmail =
        $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?
             LIMIT 1"
        );


    if (!$cekEmail) {

        throw new Exception(
            "Gagal memeriksa email: " .
            $conn->error
        );
    }


    $cekEmail->bind_param(
        "si",
        $email,
        $siswaId
    );


    $cekEmail->execute();


    $hasilEmail =
        $cekEmail->get_result();


    if ($hasilEmail->num_rows > 0) {

        $cekEmail->close();

        throw new Exception(
            "Email tersebut sudah digunakan siswa lain."
        );
    }


    $cekEmail->close();


    // ==========================================
    // AMBIL NOMOR WA LAMA
    // ==========================================

    $cekData =
        $conn->prepare(
            "SELECT
                no_hp,
                name
             FROM users
             WHERE id = ?
             LIMIT 1"
        );


    $cekData->bind_param(
        "i",
        $siswaId
    );


    $cekData->execute();


    $dataLama =
        $cekData
        ->get_result()
        ->fetch_assoc();


    $cekData->close();


    // ==========================================
    // UPDATE DATA
    // ==========================================

    if ($password !== "") {


        if (strlen($password) < 6) {

            throw new Exception(
                "Password baru minimal 6 karakter."
            );
        }


        if (
            $password !== $passwordConfirm
        ) {

            throw new Exception(
                "Konfirmasi password tidak sama."
            );
        }


        $passwordHash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        if ($passwordHash === false) {

            throw new Exception(
                "Gagal membuat hash password."
            );
        }


        $stmt =
            $conn->prepare(
                "UPDATE users
                 SET
                    name = ?,
                    nisn = ?,
                    ttl = ?,
                    gender = ?,
                    email = ?,
                    password = ?,
                    no_hp = ?,
                    address = ?
                 WHERE id = ?"
            );


        $stmt->bind_param(
            "ssssssssi",
            $name,
            $nisn,
            $ttl,
            $gender,
            $email,
            $passwordHash,
            $nomorWA,
            $address,
            $siswaId
        );


    } else {


        $stmt =
            $conn->prepare(
                "UPDATE users
                 SET
                    name = ?,
                    nisn = ?,
                    ttl = ?,
                    gender = ?,
                    email = ?,
                    no_hp = ?,
                    address = ?
                 WHERE id = ?"
            );


        $stmt->bind_param(
            "sssssssi",
            $name,
            $nisn,
            $ttl,
            $gender,
            $email,
            $nomorWA,
            $address,
            $siswaId
        );
    }


    // ==========================================
    // EKSEKUSI UPDATE
    // ==========================================

    if (!$stmt->execute()) {

        throw new Exception(
            "Gagal memperbarui data: " .
            $stmt->error
        );
    }


    $stmt->close();


    // ==========================================
    // UPDATE SESSION
    // ==========================================

    $_SESSION["siswa_name"] =
        $name;

    $_SESSION["siswa_email"] =
        $email;

    $_SESSION["siswa_nisn"] =
        $nisn;


    // ==========================================
    // NOTIFIKASI WHATSAPP
    // ==========================================

    $pesanWA =
        "✏️ *DATA AKUN SISWA DIPERBARUI*\n\n" .

        "Halo *" .
        $name .
        "*.\n\n" .

        "Data akun siswa Anda telah berhasil diperbarui.\n\n" .

        "📝 Nama: " .
        $name .
        "\n" .

        "🆔 NISN: " .
        $nisn .
        "\n" .

        "📧 Email: " .
        $email .
        "\n" .

        "📱 WhatsApp: " .
        $nomorWA .
        "\n\n" .

        "Perubahan dilakukan melalui dashboard siswa.\n\n" .

        "⚠️ Jika Anda tidak melakukan perubahan ini, " .
        "segera hubungi administrator.";


    $hasilWA =
        kirimWhatsApp(
            $nomorWA,
            $pesanWA
        );


    echo json_encode([
        "status" => "success",
        "message" =>
            "Data berhasil diperbarui dan " .
            "notifikasi WhatsApp telah diproses.",
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