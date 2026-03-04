<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false, // ubah true kalau sudah HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$sent = isset($_GET['sent']) && $_GET['sent'] == '1';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Lupa Password • Meta Inventory</title>
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

    .card-glass{
      width: 100%;
      max-width: 520px;
      border-radius: 22px;
      padding: 26px;
      background: var(--card);
      border: 1px solid var(--card-border);
      backdrop-filter: blur(14px);
      box-shadow: var(--shadow);
    }

    .logo{
      display:flex;
      align-items:center;
      gap: 12px;
      margin-bottom: 8px;
    }
    .logo img{ height: 44px; }
    .logo .meta{ color:#fff; line-height:1.1; }
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
      margin-bottom: 16px;
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

    .btn-primary{
      border-radius: 14px;
      padding: 12px 14px;
      font-weight: 700;
      border: 0;
      background: linear-gradient(135deg, rgba(255,255,255,.22), rgba(255,255,255,.10));
      color:#fff;
    }

    .btn-ghost{
      border-radius: 14px;
      padding: 12px 14px;
      font-weight: 700;
      border: 1px solid rgba(255,255,255,.22);
      background: rgba(255,255,255,.08);
      color:#fff;
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

    .actions{ display:flex; gap: 10px; margin-top: 14px; }
    .actions .btn{ flex:1; }
  </style>
</head>

<body>
<div class="blob one"></div>
<div class="blob two"></div>
<div class="blob three"></div>

<div class="auth-wrap">
  <div class="card-glass">
    <div class="logo">
      <img src="assets/images/META/meta logo.png" alt="logo">
      <div class="meta">
        <strong>Politeknik META Industri</strong>
        <span>Meta Inventory System</span>
      </div>
    </div>

    <div class="title">Lupa Password 🔒</div>
    <div class="desc">Masukkan email/username kamu. Kami akan kirim link reset password.</div>

    <?php if ($sent): ?>
      <div class="alert alert-success mt-3">
        <i class="mdi mdi-check-circle-outline"></i>
        Jika akun ditemukan, link reset password sudah dikirim ke email.
      </div>
    <?php endif; ?>

    <form class="mt-3" method="POST" action="auth-forgot-handle.php" autocomplete="on">
      <div class="mb-3">
        <label for="login" class="form-label">Username / Email</label>
        <input type="text" class="form-control" id="login" name="login"
               placeholder="contoh: ilhamdwi / ilham@email.com" required autofocus>
        <div class="mini mt-1">Akan tetap menampilkan sukses meskipun akun tidak ditemukan (demi keamanan).</div>
      </div>

      <div class="actions">
        <a class="btn btn-ghost" href="auth-login.php">
          <i class="mdi mdi-arrow-left"></i> Kembali
        </a>
        <button class="btn btn-primary" type="submit">
          <i class="mdi mdi-email-outline"></i> Kirim Link
        </button>
      </div>

      <div class="text-center mt-3 mini">
        Ingat password?
        <a href="auth-login.php" class="link">Login</a>
      </div>
    </form>

    <div class="text-center mt-4 mini" style="opacity:.85;">
      © <?= date('Y') ?> Crafted with ❤️ by Meta Edutech
    </div>
  </div>
</div>

<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
