<?php
require_once __DIR__ . "/config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email     = trim($_POST["email"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $username  = trim($_POST["username"] ?? "");
    $password  = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";
    $public_id = bin2hex(random_bytes(16));

    if ($email === "" || $full_name === "" || $username === "" || $password === "" || $password2 === "") {
        $error = "Semua field wajib diisi.";
    }
    elseif (preg_match('/\s/', $username)) {
        $error = "Username tidak boleh mengandung spasi. Contoh: ilhamdwi";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    }
    elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
        $error = "Username hanya boleh huruf, angka, titik, underscore, dan strip (3–50 karakter).";
    }
    elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";
    }
    elseif ($password !== $password2) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = ? OR username = ?
            LIMIT 1
        ");
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            $error = "Email atau username sudah terdaftar.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("
                INSERT INTO users (public_id, username, email, password_hash, full_name, role, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 'user', 1, NOW())
            ");
            $stmt->execute([$public_id, $username, $email, $hash, $full_name]);

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
    <title>Register • Meta Inventory</title>
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
            max-width: 560px;
            border-radius: 22px;
            padding: 26px;
            background: var(--card);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(14px);
            box-shadow: var(--shadow);
            position: relative;
        }

        .progress-chip{
            position:absolute;
            right: 16px;
            top: 16px;
            padding: 8px 10px;
            border-radius: 14px;
            background: rgba(0,0,0,.18);
            border: 1px solid rgba(255,255,255,.18);
            color:#fff;
            font-size: 12px;
            font-weight: 700;
            display:flex;
            gap: 8px;
            align-items:center;
        }
        .dot{
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,.7);
        }

        .logo{
            display:flex;
            align-items:center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .logo img{ height: 44px; }
        .logo .meta{ color:#fff; line-height:1.1; }
        .logo .meta strong{ display:block; font-size: 15px; }
        .logo .meta span{ display:block; font-size: 12.5px; opacity:.8; }

        .title{
            color:#fff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -.01em;
            margin-bottom: 2px;
        }
        .desc{
            color: var(--muted);
            font-size: 13.5px;
            margin-bottom: 14px;
        }

        .stepper{
            display:flex;
            gap: 10px;
            align-items:center;
            margin: 12px 0 14px;
        }
        .step{
            flex:1;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.18);
            background: rgba(255,255,255,.10);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 10px;
            opacity:.75;
        }
        .step.active{ opacity:1; border-color: rgba(255,255,255,.28); background: rgba(255,255,255,.14); }
        .step small{ opacity:.8; }
        .badge-step{
            width: 28px; height: 28px;
            border-radius: 10px;
            display:flex; align-items:center; justify-content:center;
            background: rgba(0,0,0,.18);
            border: 1px solid rgba(255,255,255,.18);
            font-weight:800;
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

        .btn-ghost{
            border-radius: 14px;
            padding: 12px 14px;
            font-weight: 700;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.08);
            color:#fff;
        }

        .alert{
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,.20);
            background: rgba(0,0,0,.18);
            color: #fff;
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

        .hint{
            margin-top: 6px;
            display:flex;
            align-items:center;
            gap: 8px;
        }
        .hint i{ font-size: 16px; }
        .hint small{ opacity:.85; }

        .spinner{
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: rgba(255,255,255,.95);
            animation: spin .8s linear infinite;
            display:inline-block;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .strength{
            margin-top: 10px;
            display:flex;
            gap: 8px;
            align-items:center;
        }
        .bar{
            height: 8px;
            flex: 1;
            border-radius: 999px;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            overflow:hidden;
            position: relative;
        }
        .bar > span{
            position:absolute;
            left:0; top:0; bottom:0;
            width:0%;
            border-radius: 999px;
            background: rgba(255,255,255,.55);
            transition: width .25s ease;
        }
        .strength-label{ min-width: 84px; text-align:right; }

        .hide{ display:none; }
        .actions{ display:flex; gap: 10px; margin-top: 14px; }
        .actions .btn{ flex:1; }

        .shake { animation: shake .28s linear 2; }
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
    <div class="card-glass" id="regCard">
        <div class="progress-chip" id="progressChip">
            <span class="dot"></span>
            <span id="progressText">Step 1/2</span>
        </div>

        <div class="logo">
            <img src="assets/images/META/meta logo.png" alt="logo">
            <div class="meta">
                <strong>Politeknik META Industri</strong>
                <span>Meta Inventory System</span>
            </div>
        </div>

        <div class="title">Buat Akun Baru ✨</div>
        <div class="desc">Isi data akun, lalu atur password yang aman.</div>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-3">
                <i class="mdi mdi-alert-circle-outline"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <script>
                window.addEventListener('load', () => {
                    const c = document.getElementById('regCard');
                    c.classList.add('shake');
                    setTimeout(()=>c.classList.remove('shake'), 800);
                });
            </script>
        <?php endif; ?>

        <div class="stepper">
            <div class="step active" id="stepA">
                <div>
                    <div style="font-weight:800;">Data Akun</div>
                    <small class="mini">Email, Username, Nama</small>
                </div>
                <div class="badge-step">1</div>
            </div>
            <div class="step" id="stepB">
                <div>
                    <div style="font-weight:800;">Password</div>
                    <small class="mini">Kuat & cocok</small>
                </div>
                <div class="badge-step">2</div>
            </div>
        </div>

        <form method="POST" action="auth-register.php" autocomplete="on" id="regForm">
            <!-- STEP 1 -->
            <div id="panel1">
                <div class="mb-3">
                    <label for="useremail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="useremail" name="email"
                           placeholder="Masukkan email aktif"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <div class="hint mini" id="emailHint"><i class="mdi mdi-information-outline"></i> <small>—</small></div>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="contoh: JohnDoe"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <div class="mini mt-1">Tanpa spasi. Hanya huruf/angka/titik/underscore/strip.</div>
                    <div class="hint mini" id="userHint"><i class="mdi mdi-information-outline"></i> <small>—</small></div>
                </div>

                <div class="mb-3">
                    <label for="fullname" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="fullname" name="full_name"
                           placeholder="Masukkan nama lengkap"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    <div class="hint mini" id="nameHint"><i class="mdi mdi-information-outline"></i> <small>Isi sesuai nama asli.</small></div>
                </div>

                <div class="actions">
                    <a class="btn btn-ghost" href="auth-login.php">
                        <i class="mdi mdi-arrow-left"></i> Kembali
                    </a>
                    <button class="btn btn-primary" type="button" id="nextBtn" disabled>
                        Lanjut <i class="mdi mdi-arrow-right"></i>
                    </button>
                </div>

                <div class="text-center mt-3 mini">
                    Sudah punya akun? <a href="auth-login.php" class="link">Masuk sekarang</a>
                </div>
            </div>

            <!-- STEP 2 -->
            <div id="panel2" class="hide">
                <div class="mb-2">
                    <label for="userpassword" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="userpassword" name="password"
                               placeholder="Minimal 8 karakter" required>
                        <button class="btn btn-eye" type="button" id="togglePass" aria-label="Toggle Password">
                            <i class="mdi mdi-eye-outline" id="eyeIcon"></i>
                        </button>
                    </div>

                    <div class="strength">
                        <div class="bar"><span id="strengthBar"></span></div>
                        <div class="mini strength-label" id="strengthText">—</div>
                    </div>
                    <div class="mini mt-1" id="strengthHint">Gunakan kombinasi huruf besar, kecil, angka, dan simbol.</div>
                </div>

               <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="userpassword2" class="form-label mb-0">
                        Konfirmasi Password
                    </label>
                    <button type="button" class="btn btn-sm btn-ghost" id="copyToConfirm">
                        Salin dari Password
                    </button>
                </div>

                <div class="input-group">
                    <input type="password" class="form-control" id="userpassword2" name="password2"
                        placeholder="Ulangi password" required>
                    <button class="btn btn-eye" type="button" id="togglePass2" aria-label="Toggle Password Confirm">
                        <i class="mdi mdi-eye-outline" id="eyeIcon2"></i>
                    </button>
                </div>

                <div class="mini mt-1" id="matchText">—</div>
            </div>


                <div class="actions">
                    <button class="btn btn-ghost" type="button" id="backBtn">
                        <i class="mdi mdi-arrow-left"></i> Kembali
                    </button>
                    <button class="btn btn-primary" type="submit" id="submitBtn" disabled>
                        <i class="mdi mdi-account-plus-outline"></i> Buat Akun
                    </button>
                </div>

                <div class="text-center mt-3 mini" style="opacity:.85;">
                    © <?= date('Y') ?> Crafted with ❤️ by Meta Edutech
                </div>
            </div>
        </form>
    </div>
</div>

<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/metismenu/metisMenu.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/js/app.js"></script>

<script>
  // ===== Step Wizard + Progress =====
  const panel1 = document.getElementById('panel1');
  const panel2 = document.getElementById('panel2');
  const stepA  = document.getElementById('stepA');
  const stepB  = document.getElementById('stepB');
  const nextBtn = document.getElementById('nextBtn');
  const backBtn = document.getElementById('backBtn');
  const progressText = document.getElementById('progressText');

  function gotoStep(n){
    if (n === 1){
      panel1.classList.remove('hide');
      panel2.classList.add('hide');
      stepA.classList.add('active');
      stepB.classList.remove('active');
      progressText.textContent = 'Step 1/2';
    } else {
      panel1.classList.add('hide');
      panel2.classList.remove('hide');
      stepA.classList.remove('active');
      stepB.classList.add('active');
      progressText.textContent = 'Step 2/2';
    }
    window.scrollTo({top:0, behavior:'smooth'});
  }

  nextBtn.addEventListener('click', () => gotoStep(2));
  backBtn.addEventListener('click', () => gotoStep(1));

  // Enter to Next (step 1)
  panel1.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (!nextBtn.disabled) gotoStep(2);
    }
  });

  // ===== Realtime Validation (email + username) with loader =====
  const emailEl = document.getElementById('useremail');
  const userEl  = document.getElementById('username');
  const nameEl  = document.getElementById('fullname');

  const emailHint = document.getElementById('emailHint');
  const userHint  = document.getElementById('userHint');
  const nameHint  = document.getElementById('nameHint');

  let emailOk = false;
  let userOk = false;
  let nameOk = false;

  function setHint(el, state, text){
    // state: 'idle' | 'loading' | 'ok' | 'bad'
    if (state === 'loading'){
      el.innerHTML = `<span class="spinner"></span> <small>${text}</small>`;
      return;
    }
    if (state === 'ok'){
      el.innerHTML = `<i class="mdi mdi-check-circle-outline"></i> <small>${text}</small>`;
      return;
    }
    if (state === 'bad'){
      el.innerHTML = `<i class="mdi mdi-alert-circle-outline"></i> <small>${text}</small>`;
      return;
    }
    el.innerHTML = `<i class="mdi mdi-information-outline"></i> <small>${text}</small>`;
  }

  function updateNext(){
    nextBtn.disabled = !(emailOk && userOk && nameOk);
  }

  let tEmail = null, tUser = null;

  async function check(type, value){
    const url = `auth-check.php?type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}`;
    const res = await fetch(url, {method:'GET'});
    return await res.json();
  }

  function validateName(){
    const v = (nameEl.value || '').trim();
    nameOk = v.length >= 3;
    setHint(nameHint, nameOk ? 'ok' : 'bad', nameOk ? 'Nama lengkap OK.' : 'Nama minimal 3 karakter.');
    updateNext();
  }

  function validateEmail(){
    const v = (emailEl.value || '').trim();
    emailOk = false;
    updateNext();

    if (!v){
      setHint(emailHint, 'bad', 'Email wajib diisi.');
      return;
    }

    clearTimeout(tEmail);
    setHint(emailHint, 'loading', 'Mengecek email…');
    tEmail = setTimeout(async () => {
      try{
        const j = await check('email', v);
        emailOk = !!(j.ok && j.available);
        setHint(emailHint, emailOk ? 'ok' : 'bad', j.message || (emailOk ? 'Email tersedia.' : 'Email tidak valid.'));
        updateNext();
      }catch(e){
        emailOk = false;
        setHint(emailHint, 'bad', 'Gagal cek email. Coba lagi.');
        updateNext();
      }
    }, 350);
  }

  function validateUsername(){
    const v = (userEl.value || '').trim();
    userOk = false;
    updateNext();

    if (!v){
      setHint(userHint, 'bad', 'Username wajib diisi.');
      return;
    }

    clearTimeout(tUser);
    setHint(userHint, 'loading', 'Mengecek username…');
    tUser = setTimeout(async () => {
      try{
        const j = await check('username', v);
        userOk = !!(j.ok && j.available);
        setHint(userHint, userOk ? 'ok' : 'bad', j.message || (userOk ? 'Username tersedia.' : 'Username tidak valid.'));
        updateNext();
      }catch(e){
        userOk = false;
        setHint(userHint, 'bad', 'Gagal cek username. Coba lagi.');
        updateNext();
      }
    }, 350);
  }

  emailEl.addEventListener('input', validateEmail);
  userEl.addEventListener('input', validateUsername);
  nameEl.addEventListener('input', validateName);

  // init
  setHint(emailHint, 'idle', '—');
  setHint(userHint, 'idle', '—');
  setHint(nameHint, 'idle', 'Isi sesuai nama asli.');

  // ===== Password Step =====
  const p1 = document.getElementById('userpassword');
  const p2 = document.getElementById('userpassword2');
  // === Salin Password ke Konfirmasi  === \\
const copyBtn = document.getElementById('copyToConfirm');

copyBtn?.addEventListener('click', () => {
    p2.value = p1.value;   
    renderPassword();     
    p2.focus();
});

  const submitBtn = document.getElementById('submitBtn');

  const bar = document.getElementById('strengthBar');
  const txt = document.getElementById('strengthText');
  const hint = document.getElementById('strengthHint');
  const matchText = document.getElementById('matchText');

  function scorePassword(p){
    let s = 0;
    if (!p) return 0;
    if (p.length >= 8) s += 1;
    if (p.length >= 12) s += 1;
    if (/[a-z]/.test(p)) s += 1;
    if (/[A-Z]/.test(p)) s += 1;
    if (/[0-9]/.test(p)) s += 1;
    if (/[^A-Za-z0-9]/.test(p)) s += 1;
    return s; // 0..6
  }

  function renderPassword(){
    const pass = p1.value || "";
    const s = scorePassword(pass);
    const percent = Math.min(100, (s/6)*100);
    bar.style.width = percent + "%";

    let label = "Lemah";
    if (s >= 5) label = "Kuat";
    else if (s >= 3) label = "Sedang";
    txt.textContent = pass.length ? label : "—";

    if (!pass.length) hint.textContent = "Gunakan kombinasi huruf besar, kecil, angka, dan simbol.";
    else if (label === "Lemah") hint.textContent = "Tambahkan panjang/simbol/angka agar lebih kuat.";
    else if (label === "Sedang") hint.textContent = "Sudah lumayan. Tambah simbol & panjang untuk lebih aman.";
    else hint.textContent = "Mantap! Password sudah kuat.";

    const match = p2.value.length ? (p1.value === p2.value) : false;
    if (!p2.value.length) matchText.textContent = "—";
    else matchText.textContent = match ? "✅ Password cocok" : "❌ Password tidak sama";

    submitBtn.disabled = !(pass.length >= 8 && match);
  }

  p1.addEventListener('input', renderPassword);
  p2.addEventListener('input', renderPassword);
  renderPassword();

  // toggle password
  const tg1 = document.getElementById('togglePass');
  const tg2 = document.getElementById('togglePass2');
  const ic1 = document.getElementById('eyeIcon');
  const ic2 = document.getElementById('eyeIcon2');

  tg1?.addEventListener('click', () => {
    const isPass = p1.type === 'password';
    p1.type = isPass ? 'text' : 'password';
    ic1.className = isPass ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
  });

  tg2?.addEventListener('click', () => {
    const isPass = p2.type === 'password';
    p2.type = isPass ? 'text' : 'password';
    ic2.className = isPass ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
  });

  // Prefill-safe: validate on load
  validateName();
  validateEmail();
  validateUsername();
</script>
</body>
</html>
