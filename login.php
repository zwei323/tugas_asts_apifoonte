<?php
// =========================================================
// login.php
// Menampilkan form login & memproses autentikasi via session
// =========================================================
session_start();
require_once "config.php";

// Jika sudah login, langsung lempar ke halaman utama
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin  = $result->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerasi session id untuk mencegah session fixation
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Registri Data Siswa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root{
        --navy:      #1E2A44;
        --navy-dark: #141D30;
        --paper:     #EEF0F4;
        --ink:       #1C2333;
        --muted:     #6B7280;
        --border:    #DCE1EA;
        --gold:      #B8862E;
        --gold-dark: #93691F;
        --gold-tint: #F6ECD9;
        --rose:      #A73C4B;
        --rose-tint: #F3E3E5;
    }

    *{ box-sizing:border-box; }

    body{
        margin:0;
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
        font-family:'Inter', Helvetica, Arial, sans-serif;
        background:
            radial-gradient(circle at 15% 20%, rgba(184,134,46,0.10), transparent 45%),
            linear-gradient(155deg, var(--navy) 0%, var(--navy-dark) 100%);
        color:var(--ink);
        padding:20px;
    }

    .login-card{
        background:#fff;
        width:380px;
        max-width:100%;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 24px 60px rgba(0,0,0,0.35);
    }

    .login-head{
        padding:30px 32px 22px;
        text-align:center;
        border-bottom:1px solid var(--border);
    }

    .login-head .seal{
        width:46px;
        height:46px;
        margin:0 auto 12px;
        border-radius:50%;
        background:var(--gold-tint);
        border:2px solid var(--gold);
        display:flex;
        align-items:center;
        justify-content:center;
        font-family:'Fraunces', Georgia, serif;
        font-weight:700;
        color:var(--gold-dark);
        font-size:1.2rem;
    }

    .login-head h1{
        font-family:'Fraunces', Georgia, serif;
        font-weight:700;
        font-size:1.4rem;
        margin:0 0 4px;
    }

    .login-head p{
        margin:0;
        font-size:0.85rem;
        color:var(--muted);
    }

    .login-body{ padding:26px 32px 32px; }

    .form-group{ margin-bottom:16px; }

    .form-group label{
        display:block;
        font-size:0.78rem;
        font-weight:600;
        color:var(--muted);
        margin-bottom:6px;
    }

    .form-group input{
        width:100%;
        padding:10px 12px;
        border:1px solid var(--border);
        border-radius:7px;
        font-size:0.92rem;
        font-family:inherit;
        color:var(--ink);
    }

    .form-group input:focus{
        outline:none;
        border-color:var(--gold);
        box-shadow:0 0 0 3px var(--gold-tint);
    }

    .btn-login{
        width:100%;
        border:none;
        background:var(--navy);
        color:#fff;
        padding:11px;
        border-radius:7px;
        font-size:0.92rem;
        font-weight:600;
        font-family:inherit;
        cursor:pointer;
        margin-top:6px;
    }

    .btn-login:hover{ background:var(--navy-dark); }

    .alert-error{
        background:var(--rose-tint);
        color:var(--rose);
        border-radius:7px;
        padding:10px 14px;
        font-size:0.84rem;
        font-weight:600;
        margin-bottom:16px;
    }

    .hint{
        text-align:center;
        margin-top:18px;
        font-size:0.78rem;
        color:var(--muted);
    }
</style>
</head>
<body>

<div class="login-card">
    <div class="login-head">
        <div class="seal">RS</div>
        <h1>Registri Data Siswa</h1>
        <p>Silakan login untuk mengakses halaman data</p>
    </div>

    <div class="login-body">
        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>

        <div class="hint">Akun default: <strong>admin</strong> / <strong>admin123</strong></div>
    </div>
</div>

</body>
</html>
