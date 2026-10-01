<?php

session_start();

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
    // AMBIL DATA LOGIN
    // ==========================================

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    // ==========================================
    // VALIDASI
    // ==========================================

    if ($email === "") {

        throw new Exception(
            "Email wajib diisi."
        );
    }


    if ($password === "") {

        throw new Exception(
            "Password wajib diisi."
        );
    }


    // ==========================================
    // CARI SISWA
    // ==========================================

    $stmt = $conn->prepare(
        "SELECT
            id,
            name,
            nisn,
            ttl,
            gender,
            email,
            password,
            no_hp,
            address
         FROM users
         WHERE email = ?
         LIMIT 1"
    );


    if (!$stmt) {

        throw new Exception(
            "Database error: " .
            $conn->error
        );
    }


    $stmt->bind_param(
        "s",
        $email
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 0) {

        throw new Exception(
            "Email atau password salah."
        );
    }


    $user =
        $result->fetch_assoc();


    // ==========================================
    // CEK PASSWORD
    // ==========================================

    if (
        empty($user["password"]) ||
        !password_verify(
            $password,
            $user["password"]
        )
    ) {

        throw new Exception(
            "Email atau password salah."
        );
    }


    // ==========================================
    // SIMPAN SESSION
    // ==========================================

    $_SESSION["siswa_id"] =
        $user["id"];

    $_SESSION["siswa_name"] =
        $user["name"];

    $_SESSION["siswa_email"] =
        $user["email"];

    $_SESSION["siswa_nisn"] =
        $user["nisn"];


    // ==========================================
    // KIRIM NOTIFIKASI WHATSAPP
    // ==========================================

    $nomorWA =
        $user["no_hp"] ?? "";


    if ($nomorWA !== "") {

        $pesanWA =
            "🔐 *LOGIN AKUN SISWA*\n\n" .

            "Halo *" .
            $user["name"] .
            "*.\n\n" .

            "Kami mendeteksi bahwa akun siswa Anda " .
            "baru saja digunakan untuk login.\n\n" .

            "📧 Email: " .
            $user["email"] .
            "\n" .

            "🆔 NISN: " .
            $user["nisn"] .
            "\n\n" .

            "Jika login ini dilakukan oleh Anda, " .
            "silakan abaikan pesan ini.\n\n" .

            "⚠️ Jika Anda tidak merasa melakukan login, " .
            "segera hubungi administrator.\n\n" .

            "Terima kasih.";


        $hasilWA =
            kirimWhatsApp(
                $nomorWA,
                $pesanWA
            );
    }


    // ==========================================
    // RESPONSE
    // ==========================================

    echo json_encode([
        "status" => "success",
        "message" =>
            "Login berhasil."
    ]);


    $stmt->close();

    $conn->close();


} catch (Throwable $e) {

    http_response_code(401);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

?>