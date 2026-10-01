<?php

session_start();

require_once "config.php";


// ==========================================
// CEK LOGIN SISWA
// ==========================================

if (!isset($_SESSION["siswa_id"])) {

    header("Location: login_siswa.html");
    exit;
}


$siswaId = (int) $_SESSION["siswa_id"];


// ==========================================
// AMBIL DATA SISWA
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
        wa_status,
        address
     FROM users
     WHERE id = ?
     LIMIT 1"
);


if (!$stmt) {

    die(
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

    session_destroy();

    header("Location: login_siswa.html");
    exit;
}


$siswa =
    $result->fetch_assoc();


$stmt->close();


// ==========================================
// DATA SISWA
// ==========================================

$name =
    htmlspecialchars(
        $siswa["name"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$nisn =
    htmlspecialchars(
        $siswa["nisn"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$ttl =
    htmlspecialchars(
        $siswa["ttl"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$gender =
    htmlspecialchars(
        $siswa["gender"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$email =
    htmlspecialchars(
        $siswa["email"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$no_hp =
    htmlspecialchars(
        $siswa["no_hp"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$address =
    htmlspecialchars(
        $siswa["address"] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );

$waStatus =
    $siswa["wa_status"] ?? "pending";

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard Siswa
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                sans-serif;

            background:
                #f4f6f8;

            color:
                #333;
        }


        /* =================================
           NAVBAR
        ================================= */

        .navbar {

            background:
                #243447;

            color:
                white;

            padding:
                15px 30px;

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            flex-wrap:
                wrap;

            gap:
                10px;
        }


        .navbar h2 {

            margin:
                0;

            font-size:
                20px;
        }


        .navbar-right {

            display:
                flex;

            align-items:
                center;

            gap:
                15px;
        }


        .navbar-right span {

            font-size:
                14px;
        }


        .logout {

            color:
                white;

            background:
                #c0392b;

            padding:
                8px 13px;

            border-radius:
                6px;

            text-decoration:
                none;

            font-size:
                13px;
        }


        .logout:hover {

            background:
                #a93226;
        }


        /* =================================
           CONTAINER
        ================================= */

        .container {

            max-width:
                1000px;

            margin:
                35px auto;

            padding:
                0 20px;
        }


        .welcome {

            margin-bottom:
                25px;
        }


        .welcome h1 {

            margin:
                0 0 8px 0;

            font-size:
                28px;
        }


        .welcome p {

            margin:
                0;

            color:
                #777;
        }


        /* =================================
           GRID
        ================================= */

        .grid {

            display:
                grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap:
                20px;
        }


        .card {

            background:
                white;

            border-radius:
                12px;

            padding:
                25px;

            box-shadow:
                0 4px 15px
                rgba(0,0,0,0.08);
        }


        .card.full {

            grid-column:
                1 / -1;
        }


        .card h2 {

            margin:
                0 0 20px 0;

            font-size:
                20px;
        }


        /* =================================
           DATA PROFIL
        ================================= */

        .data-row {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                20px;

            padding:
                12px 0;

            border-bottom:
                1px solid #eee;
        }


        .data-row:last-child {

            border-bottom:
                none;
        }


        .data-label {

            color:
                #777;

            font-weight:
                bold;
        }


        .data-value {

            text-align:
                right;

            word-break:
                break-word;
        }


        /* =================================
           STATUS WA
        ================================= */

        .status {

            display:
                inline-block;

            padding:
                5px 10px;

            border-radius:
                20px;

            font-size:
                12px;

            font-weight:
                bold;
        }


        .status-terkirim {

            background:
                #dff6e4;

            color:
                #1d6b2f;
        }


        .status-gagal {

            background:
                #fde2e2;

            color:
                #9b2226;
        }


        .status-pending {

            background:
                #fff3cd;

            color:
                #856404;
        }


        /* =================================
           FORM
        ================================= */

        .form-group {

            margin-bottom:
                17px;
        }


        .form-group label {

            display:
                block;

            margin-bottom:
                7px;

            font-weight:
                bold;

            font-size:
                14px;
        }


        input,
        select,
        textarea {

            width:
                100%;

            padding:
                11px 13px;

            border:
                1px solid #ccc;

            border-radius:
                7px;

            font-size:
                14px;

            outline:
                none;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color:
                #243447;
        }


        textarea {

            min-height:
                100px;

            resize:
                vertical;
        }


        .password-info {

            margin-top:
                5px;

            color:
                #777;

            font-size:
                12px;
        }


        /* =================================
           BUTTON
        ================================= */

        .btn-simpan {

            background:
                #243447;

            color:
                white;

            border:
                none;

            padding:
                12px 20px;

            border-radius:
                7px;

            cursor:
                pointer;

            font-size:
                14px;

            font-weight:
                bold;
        }


        .btn-simpan:hover {

            background:
                #182634;
        }


        .btn-simpan:disabled {

            opacity:
                0.6;

            cursor:
                not-allowed;
        }


        /* =================================
           MESSAGE
        ================================= */

        #message {

            display:
                none;

            padding:
                12px;

            margin-bottom:
                20px;

            border-radius:
                7px;

            font-size:
                14px;
        }


        .success {

            background:
                #dff6e4;

            color:
                #1d6b2f;
        }


        .error {

            background:
                #fde2e2;

            color:
                #9b2226;
        }


        /* =================================
           RESPONSIVE
        ================================= */

        @media (max-width: 700px) {

            .grid {

                grid-template-columns:
                    1fr;
            }


            .card.full {

                grid-column:
                    auto;
            }


            .navbar {

                padding:
                    15px 20px;
            }


            .navbar-right {

                width:
                    100%;

                justify-content:
                    space-between;
            }


            .data-row {

                flex-direction:
                    column;

                gap:
                    5px;
            }


            .data-value {

                text-align:
                    left;
            }

        }

    </style>

</head>


<body>


<!-- =====================================
     NAVBAR
===================================== -->

<div class="navbar">

    <h2>
        Dashboard Siswa
    </h2>


    <div class="navbar-right">

        <span>
            <?= $name ?>
        </span>


        <a
            href="logout_siswa.php"
            class="logout"
            onclick="return confirm('Apakah Anda yakin ingin logout?')"
        >
            Logout
        </a>

    </div>

</div>


<!-- =====================================
     CONTENT
===================================== -->

<div class="container">


    <div class="welcome">

        <h1>
            Selamat Datang, <?= $name ?> 👋
        </h1>

        <p>
            Kelola data pribadi dan akun siswa Anda di halaman ini.
        </p>

    </div>


    <div id="message"></div>


    <div class="grid">


        <!-- =================================
             DATA PROFIL
        ================================== -->

        <div class="card">

            <h2>
                Data Profil
            </h2>


            <div class="data-row">

                <div class="data-label">
                    Nama
                </div>

                <div class="data-value">
                    <?= $name ?>
                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    NISN
                </div>

                <div class="data-value">
                    <?= $nisn ?>
                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    TTL
                </div>

                <div class="data-value">
                    <?= $ttl ?>
                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    Jenis Kelamin
                </div>

                <div class="data-value">
                    <?= $gender ?>
                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    Email
                </div>

                <div class="data-value">
                    <?= $email ?>
                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    WhatsApp
                </div>

                <div class="data-value">
                    <?= $no_hp ?>
                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    Status WhatsApp
                </div>

                <div class="data-value">

                    <?php if ($waStatus === "terkirim"): ?>

                        <span class="status status-terkirim">
                            Terkirim
                        </span>

                    <?php elseif ($waStatus === "gagal"): ?>

                        <span class="status status-gagal">
                            Gagal
                        </span>

                    <?php else: ?>

                        <span class="status status-pending">
                            Pending
                        </span>

                    <?php endif; ?>

                </div>

            </div>


            <div class="data-row">

                <div class="data-label">
                    Alamat
                </div>

                <div class="data-value">
                    <?= $address ?>
                </div>

            </div>

        </div>


        <!-- =================================
             EDIT DATA
        ================================== -->

        <div class="card">

            <h2>
                Edit Data Saya
            </h2>


            <form
                id="editForm"
            >


                <!-- NAMA -->

                <div class="form-group">

                    <label>
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= $name ?>"
                        required
                    >

                </div>


                <!-- NISN -->

                <div class="form-group">

                    <label>
                        NISN
                    </label>

                    <input
                        type="text"
                        name="nisn"
                        value="<?= $nisn ?>"
                        required
                    >

                </div>


                <!-- TTL -->

                <div class="form-group">

                    <label>
                        Tempat & Tanggal Lahir
                    </label>

                    <input
                        type="text"
                        name="ttl"
                        value="<?= $ttl ?>"
                        required
                    >

                </div>


                <!-- GENDER -->

                <div class="form-group">

                    <label>
                        Jenis Kelamin
                    </label>

                    <select
                        name="gender"
                        required
                    >

                        <option value="">
                            -- Pilih --
                        </option>


                        <option
                            value="Laki-laki"
                            <?= $siswa["gender"] === "Laki-laki" ? "selected" : "" ?>
                        >
                            Laki-laki
                        </option>


                        <option
                            value="Perempuan"
                            <?= $siswa["gender"] === "Perempuan" ? "selected" : "" ?>
                        >
                            Perempuan
                        </option>

                    </select>

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?= $email ?>"
                        required
                    >

                </div>


                <!-- NO HP -->

                <div class="form-group">

                    <label>
                        Nomor WhatsApp
                    </label>

                    <input
                        type="text"
                        name="no_hp"
                        value="<?= $no_hp ?>"
                        required
                    >

                </div>


                <!-- ALAMAT -->

                <div class="form-group">

                    <label>
                        Alamat
                    </label>

                    <textarea
                        name="address"
                        required
                    ><?= $address ?></textarea>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label>
                        Password Baru
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        minlength="6"
                        placeholder="Kosongkan jika tidak ingin mengganti"
                    >

                    <div class="password-info">

                        Kosongkan jika ingin tetap menggunakan
                        password lama.

                    </div>

                </div>


                <!-- KONFIRMASI PASSWORD -->

                <div class="form-group">

                    <label>
                        Konfirmasi Password Baru
                    </label>

                    <input
                        type="password"
                        name="password_confirm"
                        id="password_confirm"
                        minlength="6"
                        placeholder="Ulangi password baru"
                    >

                </div>


                <button
                    type="submit"
                    class="btn-simpan"
                    id="btnSimpan"
                >
                    Simpan Perubahan
                </button>


            </form>

        </div>


    </div>

</div>


<script>


// ==========================================
// FORM EDIT
// ==========================================

const form =
    document.getElementById(
        "editForm"
    );


const btnSimpan =
    document.getElementById(
        "btnSimpan"
    );


const message =
    document.getElementById(
        "message"
    );


// ==========================================
// TAMPILKAN PESAN
// ==========================================

function showMessage(
    text,
    type
) {

    message.textContent =
        text;

    message.className =
        type;

    message.style.display =
        "block";


// Hilangkan pesan setelah beberapa detik

    setTimeout(
        function() {

            message.style.display =
                "none";

        },
        4000
    );
}


// ==========================================
// SUBMIT FORM
// ==========================================

form.addEventListener(
    "submit",
    async function(event) {

        event.preventDefault();


        const password =
            document.getElementById(
                "password"
            ).value;


        const passwordConfirm =
            document.getElementById(
                "password_confirm"
            ).value;


        // ==============================
        // VALIDASI PASSWORD
        // ==============================

        if (
            password !== "" &&
            password.length < 6
        ) {

            showMessage(
                "Password baru minimal 6 karakter.",
                "error"
            );

            return;
        }


        if (
            password !== passwordConfirm
        ) {

            showMessage(
                "Konfirmasi password tidak sama.",
                "error"
            );

            return;
        }


        // ==============================
        // FORM DATA
        // ==============================

        const formData =
            new FormData(form);


        btnSimpan.disabled =
            true;


        btnSimpan.textContent =
            "Menyimpan...";


        try {


            const response =
                await fetch(
                    "update_siswa.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );


            const result =
                await response.json();


            // ==========================
            // BERHASIL
            // ==========================

            if (
                result.status === "success"
            ) {

                showMessage(
                    result.message,
                    "success"
                );


                // Kosongkan password

                document.getElementById(
                    "password"
                ).value = "";


                document.getElementById(
                    "password_confirm"
                ).value = "";


                // Reload dashboard

                setTimeout(
                    function() {

                        window.location.reload();

                    },
                    1000
                );


            } else {


                showMessage(
                    result.message,
                    "error"
                );

            }


        } catch (error) {


            console.error(
                error
            );


            showMessage(
                "Terjadi kesalahan saat menyimpan data.",
                "error"
            );


        } finally {


            btnSimpan.disabled =
                false;


            btnSimpan.textContent =
                "Simpan Perubahan";

        }

    }

);

</script>


</body>

</html>