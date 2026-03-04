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

$uid   = (int)($_GET["uid"] ?? $_POST["uid"] ?? 0);
$token = $_GET["token"] ?? ($_POST["token"] ?? "");

if ($uid <= 0 || $token === "") {
    $error = "Link reset tidak valid.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $error === "") {
    $p1 = $_POST["password"] ?? "";
    $p2 = $_POST["password2"] ?? "";

    if (strlen($p1) < 8) {
        $error = "Password minimal 8 karakter.";
    } elseif ($p1 !== $p2) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        $stmt = $pdo->prepare("
            SELECT id, token_hash, expires_at, used_at
            FROM password_resets
            WHERE user_id = ? AND used_at IS NULL
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$uid]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset) {
            $error = "Token reset tidak ditemukan atau sudah dipakai.";
        } elseif (strtotime($reset["expires_at"]) < time()) {
            $error = "Token reset sudah kadaluarsa. Silakan minta link baru.";
        } elseif (!password_verify($token, $reset["token_hash"])) {
            $error = "Token reset tidak valid.";
        } else {
            // update password user
            $newHash = password_hash($p1, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")
                ->execute([$newHash, $uid]);

            // tandai token dipakai
            $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")
                ->execute([(int)$reset["id"]]);

            // redirect ke login dgn message sukses
            header("Location: auth-login.php?register=success");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Reset Password • Meta Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/META/meta logo.png">

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />

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

        .card-glass{
            border-radius: 22px;
            padding: 26px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.22);
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

                <div class="brand-title">Buat password baru dengan aman.</div>
                <div class="brand-sub">
                    Password baru akan langsung aktif setelah disimpan. Pastikan password kuat dan mudah kamu ingat.
                </div>

                <div class="footer-note">© <?= date('Y') ?> Crafted with ❤️ by Meta Edutech</div>
            </div>
        </div>

        <!-- RIGHT RESET CARD -->
        <div class="card-glass" id="resetCard">
            <div class="card-head">
                <div class="logo">
                    <img src="assets/images/META/meta logo.png" alt="logo">
                    <div class="meta">
                        <strong>Politeknik META Industri</strong>
                        <span>Meta Inventory System</span>
                    </div>
                </div>
                <div class="mini d-none d-md-block">
                    <i class="mdi mdi-lock-reset"></i> Reset Password
                </div>
            </div>

            <div class="title">Reset Password 🔑</div>
            <div class="desc">Masukkan password baru dan konfirmasi.</div>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
                <script>
                    window.addEventListener('load', () => {
                        const c = document.getElementById('resetCard');
                        c.classList.add('shake');
                        setTimeout(()=>c.classList.remove('shake'), 800);
                    });
                </script>
            <?php endif; ?>

            <form class="mt-3" method="POST" action="auth-reset.php" autocomplete="on">
                <input type="hidden" name="uid" value="<?= htmlspecialchars((string)$uid) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars((string)$token) ?>">

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="newpass" name="password" placeholder="Minimal 8 karakter" required>
                        <button class="btn btn-eye" type="button" id="toggleNew" aria-label="Toggle Password">
                            <i class="mdi mdi-eye-outline" id="eyeNew"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="newpass2" name="password2" placeholder="Ulangi password baru" required>
                        <button class="btn btn-eye" type="button" id="toggleNew2" aria-label="Toggle Password">
                            <i class="mdi mdi-eye-outline" id="eyeNew2"></i>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary w-100 mt-3" type="submit">
                    <i class="mdi mdi-content-save-outline"></i> Simpan Password
                </button>

                <div class="text-center mt-3 mini">
                    <a href="auth-login.php" class="link" style="border-bottom:none;">Kembali ke Login</a>
                </div>
            </form>

            <div class="text-center mt-4 mini" style="opacity:.85;">
                <i class="mdi mdi-information-outline"></i>
                Jika link sudah kadaluarsa, minta link reset baru.
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
  // show/hide password (new pass)
  const t1 = document.getElementById('toggleNew');
  const p1 = document.getElementById('newpass');
  const i1 = document.getElementById('eyeNew');

  t1?.addEventListener('click', () => {
    const isPass = p1.type === 'password';
    p1.type = isPass ? 'text' : 'password';
    i1.className = isPass ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
  });

  // show/hide password (confirm)
  const t2 = document.getElementById('toggleNew2');
  const p2 = document.getElementById('newpass2');
  const i2 = document.getElementById('eyeNew2');

  t2?.addEventListener('click', () => {
    const isPass = p2.type === 'password';
    p2.type = isPass ? 'text' : 'password';
    i2.className = isPass ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
  });
</script>
</body>
</html>
                