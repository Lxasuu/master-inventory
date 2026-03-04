<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false, // ubah true kalau sudah HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . "/config/db.php";

$error = "";
$success = "";
if (isset($_GET["register"]) && $_GET["register"] === "success") {
    $success = "Registrasi berhasil! Silakan login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login === "" || $password === "") {
        $error = "Email/Username dan password wajib diisi.";
    } else {

        $stmt = $pdo->prepare("
            SELECT user_id, username, email, password_hash, full_name, role, is_active
            FROM users
            WHERE email = ? OR username = ?
            LIMIT 1
        ");
        $stmt->execute([$login, $login]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Akun tidak ditemukan.";
        } elseif ((int)$user["is_active"] !== 1) {
            $error = "Akun tidak aktif.";
        } elseif (!password_verify($password, $user["password_hash"])) {
            $error = "Password salah.";
        } else {
            session_regenerate_id(true);
            $_SESSION["user"] = [
                "user_id"   => (int)$user["user_id"],
                "username"  => $user["username"],
                "email"     => $user["email"],
                "full_name" => $user["full_name"],
                "role"      => $user["role"],
            ];

            try {
                $upd = $pdo->prepare("UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE user_id = ?");
                $upd->execute([(int)$user["user_id"]]);
            } catch (PDOException $e) {
                error_log("Last login update failed: " . $e->getMessage());
            }

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Login • Meta Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/META/meta logo.png">

    <!-- Bootstrap (existing) -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />

    <!-- Optional: Google Font (modern) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg1:#4b5cff;
            --bg2:#7b42ff;
            --bg3:#28c7fa;
            --card: rgba(255,255,255,.14);
            --card-border: rgba(255,255,255,.22);
            --text: #0b1220;
            --muted: rgba(255,255,255,.75);
            --shadow: 0 20px 60px rgba(0,0,0,.25);
        }

        body{
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            background: radial-gradient(1200px 700px at 10% 10%, rgba(40,199,250,.35), transparent 60%),
                        radial-gradient(900px 600px at 85% 20%, rgba(123,66,255,.40), transparent 60%),
                        linear-gradient(135deg, var(--bg1), var(--bg2));
        }

        /* decorative blobs */
        .blob{
            position: fixed;
            width: 520px; height: 520px;
            filter: blur(55px);
            opacity: .35;
            z-index: 0;
            border-radius: 50%;
            pointer-events: none;
            animation: float 8s ease-in-out infinite;
        }
        .blob.one{ left: -120px; top: -120px; background: var(--bg3); }
        .blob.two{ right: -160px; top: 60px; background: #ff6bd6; animation-delay: 1.2s; }
        .blob.three{ left: 10%; bottom: -240px; background: #ffd166; animation-delay: 2.1s; }

        @keyframes float{
            0%,100%{ transform: translateY(0px) translateX(0px); }
            50%{ transform: translateY(18px) translateX(10px); }
        }

        .auth-wrap{
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
        }

        .auth-shell{
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 18px;
        }

        @media (max-width: 992px){
            .auth-shell{ grid-template-columns: 1fr; max-width: 520px; }
        }

        .brand-panel{
            position: relative;
            border-radius: 22px;
            padding: 28px;
            color: #fff;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.18);
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .brand-panel::after{
            content:"";
            position:absolute;
            inset:-2px;
            background: radial-gradient(600px 300px at 20% 10%, rgba(255,255,255,.25), transparent 55%),
                        radial-gradient(400px 220px at 80% 40%, rgba(255,255,255,.18), transparent 60%);
            pointer-events:none;
        }
        .brand-panel .content{ position: relative; z-index: 2; }
        .brand-badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(0,0,0,.18);
            border: 1px solid rgba(255,255,255,.18);
        }
        .brand-badge img{ height: 34px; width: auto; }
        .brand-title{
            margin-top: 16px;
            font-weight: 700;
            letter-spacing: -.02em;
            font-size: 28px;
            line-height: 1.2;
        }
        .brand-sub{
            margin-top: 10px;
            color: var(--muted);
            font-size: 14.5px;
            line-height: 1.55;
        }
        .brand-points{
            margin-top: 18px;
            display: grid;
            gap: 10px;
        }
        .point{
            display:flex;
            gap: 10px;
            align-items:flex-start;
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.16);
        }
        .point i{ font-size: 18px; margin-top: 2px; }

        .card-glass{
            border-radius: 22px;
            padding: 26px;
            background: var(--card);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(14px);
            box-shadow: var(--shadow);
        }

        .card-head{
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 8px;
        }
        .logo{
            display:flex;
            align-items:center;
            gap: 12px;
        }
        .logo img{ height: 44px; }
        .logo .meta{
            color:#fff;
            line-height:1.1;
        }
        .logo .meta strong{ display:block; font-size: 15px; }
        .logo .meta span{ display:block; font-size: 12.5px; opacity:.8; }

        .title{
            color:#fff;
            margin-top: 10px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -.01em;
        }
        .desc{
            color: var(--muted);
            font-size: 13.5px;
            margin-bottom: 18px;
        }

        .form-label{ color: rgba(255,255,255,.85); font-weight: 600; font-size: 13px; }
        .form-control{
            border-radius: 14px;
            padding: 12px 12px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.22);
            color:#fff;
        }
        .form-control::placeholder{ color: rgba(255,255,255,.65); }
        .form-control:focus{
            box-shadow: 0 0 0 .2rem rgba(255,255,255,.12);
            border-color: rgba(255,255,255,.4);
        }

        .input-group .btn-eye{
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.10);
            color: rgba(255,255,255,.9);
        }

        .btn-primary{
            border-radius: 14px;
            padding: 12px 14px;
            font-weight: 700;
            border: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.22), rgba(255,255,255,.10));
            color:#fff;
        }
        .btn-primary:hover{
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .mini{
            color: rgba(255,255,255,.75);
            font-size: 12.5px;
        }
        .link{
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            border-bottom: 1px dashed rgba(255,255,255,.55);
        }
        .link:hover{ opacity:.9; }

        .alert{
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,.20);
            background: rgba(0,0,0,.18);
            color: #fff;
        }

        .footer-note{
            margin-top: 14px;
            text-align:center;
            color: rgba(255,255,255,.75);
            font-size: 12px;
        }
        .shake {
            animation: shake .28s linear 2;
        }
        @keyframes shake {
            0%{ transform: translateX(0); }
            25%{ transform: translateX(-6px); }
            50%{ transform: translateX(6px); }
            75%{ transform: translateX(-4px); }
            100%{ transform: translateX(0); }
        }
    </style>
</head>

<body>
<div class="blob one"></div>
<div class="blob two"></div>
<div class="blob three"></div>

<div class="auth-wrap">
    <div class="auth-shell">

        <!-- LEFT BRAND PANEL -->
        <div class="brand-panel">
            <div class="content">
                <div class="brand-badge">
                    <img src="assets/images/META/meta logo.png" alt="logo">
                    <div>
                        <div style="font-weight:800; letter-spacing:-.01em;">Meta Inventory</div>
                        <div style="opacity:.85; font-size:12.5px;">Politeknik META Industri</div>
                    </div>
                </div>

                <div class="brand-title">Kelola aset & inventory jadi lebih cepat.</div>
                <div class="brand-sub">
                    Login untuk melanjutkan. Sistem ini dibuat untuk memastikan pencatatan inventaris lebih rapi,
                    akurat, dan mudah dilacak.
                </div>

                <div class="brand-points">
                    <div class="point">
                        <i class="mdi mdi-shield-check-outline"></i>
                        <div>
                            <div style="font-weight:700;">Akses berbasis role</div>
                            <div style="opacity:.85; font-size:12.5px;">Admin, PIC, dan User dengan kontrol terpisah.</div>
                        </div>
                    </div>
                    <div class="point">
                        <i class="mdi mdi-bell-ring-outline"></i>
                        <div>
                            <div style="font-weight:700;">Notifikasi aktivitas</div>
                            <div style="opacity:.85; font-size:12.5px;">Pantau perubahan & riwayat dengan cepat.</div>
                        </div>
                    </div>
                    <div class="point">
                        <i class="mdi mdi-database-check-outline"></i>
                        <div>
                            <div style="font-weight:700;">Data terstruktur</div>
                            <div style="opacity:.85; font-size:12.5px;">Lebih mudah audit & laporan.</div>
                        </div>
                    </div>
                </div>

                <div class="footer-note">© <?= date('Y') ?> Crafted with ❤️ by Meta Edutech</div>
            </div>
        </div>

        <!-- RIGHT LOGIN CARD -->
        <div class="card-glass" id="loginCard">
            <div class="card-head">
                <div class="logo">
                    <img src="assets/images/META/meta logo.png" alt="logo">
                    <div class="meta">
                        <strong>Politeknik META Industri</strong>
                        <span>Meta Inventory System</span>
                    </div>
                </div>
                <div class="mini d-none d-md-block">
                    <i class="mdi mdi-lock-outline"></i> Secure Login
                </div>
            </div>

            <div class="title">Selamat Datang 👋</div>
            <div class="desc">Masukkan Username/Email dan Password untuk masuk.</div>

            <?php if ($success): ?>
                <div class="alert alert-success mt-3">
                    <i class="mdi mdi-check-circle-outline"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <script>
                    // animasi shake ketika error
                    window.addEventListener('load', () => {
                        const c = document.getElementById('loginCard');
                        c.classList.add('shake');
                        setTimeout(()=>c.classList.remove('shake'), 800);
                    });
                </script>
            <?php endif; ?>

            <form class="mt-3" method="POST" action="auth-login.php" autocomplete="on">
                <div class="mb-3">
                    <label for="login" class="form-label">Username / Email</label>
                    <input
                        type="text"
                        class="form-control"
                        id="login"
                        name="login"
                        placeholder="John Doe / John@example.com"
                        value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                        required
                        autofocus
                    >
                    <div class="mini mt-1">Gunakan username atau email yang sudah terdaftar.</div>
                </div>

                <div class="mb-2">
                    <label for="userpassword" class="form-label">Password</label>

                    <div class="input-group">
                        <input
                            type="password"
                            class="form-control"
                            id="userpassword"
                            name="password"
                            placeholder="Masukkan password"
                            required
                        >
                        <button class="btn btn-eye" type="button" id="togglePass" aria-label="Toggle Password">
                            <i class="mdi mdi-eye-outline" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="rememberMe">
                        <label class="form-check-label mini" for="rememberMe">Remember me</label>
                    </div>
                    <a href="auth-forgot.php" class="mini link" style="border-bottom:none;">Lupa password?</a>
                </div>

                <button class="btn btn-primary w-100" type="submit">
                    <i class="mdi mdi-login-variant"></i> Masuk
                </button>

                <div class="text-center mt-3 mini">
                    Belum punya akun?
                    <a href="auth-register.php" class="link">Daftar disini</a>
                </div>
            </form>

            <div class="text-center mt-4 mini" style="opacity:.85;">
                <i class="mdi mdi-information-outline"></i>
                Pastikan kamu login di jaringan yang aman.
            </div>
        </div>
                
    </div>
</div>

<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
  // show/hide password
  const toggle = document.getElementById('togglePass');
  const pass   = document.getElementById('userpassword');
  const icon   = document.getElementById('eyeIcon');

  toggle?.addEventListener('click', () => {
    const isPass = pass.type === 'password';
    pass.type = isPass ? 'text' : 'password';
    icon.className = isPass ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
  });
</script>
</body>
</html>