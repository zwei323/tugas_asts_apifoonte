<?php

function kirimWhatsApp($target, $message)
{
    // =========================
    // TOKEN FONNTE
    // =========================
    $token = "moWypGEDrhru5asoSZyY";


    // =========================
    // FORMAT NOMOR
    // =========================

    $target = preg_replace('/[^0-9]/', '', $target);

    if (substr($target, 0, 1) === "0") {
        $target = "62" . substr($target, 1);
    }


    // =========================
    // DATA KIRIM
    // =========================

    $data = [
        "target"  => $target,
        "message" => $message
    ];


    // =========================
    // CURL
    // =========================

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "Authorization: " . $token
        ],
        CURLOPT_TIMEOUT => 30
    ]);


    // =========================
    // EKSEKUSI
    // =========================

    $response = curl_exec($curl);


    // =========================
    // CEK CURL ERROR
    // =========================

    if ($response === false) {

        $error = curl_error($curl);

        curl_close($curl);

        return [
            "status" => "failed",
            "target" => $target,
            "response" => "CURL ERROR: " . $error
        ];
    }


    // =========================
    // HTTP CODE
    // =========================

    $httpCode = curl_getinfo(
        $curl,
        CURLINFO_HTTP_CODE
    );

    curl_close($curl);


    // =========================
    // RESPONSE FONNTE
    // =========================

    $result = json_decode(
        $response,
        true
    );


    $success = false;

    if (
        $httpCode >= 200 &&
        $httpCode < 300 &&
        isset($result["status"]) &&
        $result["status"] === true
    ) {
        $success = true;
    }


    return [
        "status" => $success ? "success" : "failed",
        "target" => $target,
        "response" => $response
    ];
}


// ==================================================
// MEMBUAT PESAN PENDAFTARAN
// ==================================================

function buatPesanPendaftaran(
    $name,
    $nisn,
    $ttl,
    $gender,
    $email,
    $no_hp,
    $address
) {

    $pesan =
"🎓 *PENDAFTARAN SISWA*

Halo *{$name}* 👋

Pendaftaran kamu telah berhasil diterima.

📋 *DATA PENDAFTARAN*

👤 Nama      : {$name}
🆔 NISN      : {$nisn}
🎂 TTL       : {$ttl}
⚧ Gender     : {$gender}
📧 Email      : {$email}
📱 WhatsApp  : {$no_hp}
🏠 Alamat    : {$address}

✅ *Status: Pendaftaran berhasil*

Terima kasih telah melakukan pendaftaran.

Silakan menunggu informasi selanjutnya dari pihak sekolah.

_Pesan ini dikirim secara otomatis._";

    return $pesan;
}

?>