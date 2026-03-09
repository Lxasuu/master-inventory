<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin']);

$pageTitle = "Tambah User";

if (!isset($_SESSION["user"]["user_id"])) { header("Location: ../auth-login.php"); exit; }

$error = "";
$success = "";

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/../partials/app_context.php";

// =========================
// NOTIFICATIONS
// =========================
$stmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$notifUnread = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT log_id, action, entity, entity_id, title, detail, is_read, created_at
  FROM activity_logs
  WHERE user_id = ?
  ORDER BY log_id DESC
  LIMIT 5
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// helper
// =========================
function uploadPhoto(int $userId, array $file): array {
  if (empty($file["name"])) return [null, ""];
  if (($file["error"] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return [null, "Upload gagal."];

  $allowed = ["image/jpeg"=>"jpg", "image/png"=>"png", "image/webp"=>"webp"];
  $mime = @mime_content_type($file["tmp_name"]);
  if (!isset($allowed[$mime])) return [null, "Format foto harus JPG/PNG/WEBP."];
  if (($file["size"] ?? 0) > 2 * 1024 * 1024) return [null, "Ukuran foto max 2MB."];

  $ext = $allowed[$mime];

  $root = realpath(__DIR__ . "/..");
  $dirAbs = $root . "/uploads/users";
  if (!is_dir($dirAbs)) @mkdir($dirAbs, 0777, true);

  $fileName = "user_" . $userId . "_" . time() . "." . $ext;
  $destAbs = $dirAbs . "/" . $fileName;

  $dbPath = "uploads/users/" . $fileName;

  if (!move_uploaded_file($file["tmp_name"], $destAbs)) return [null, "Gagal menyimpan file."];

  return [$dbPath, ""];
}

function generate_public_id(PDO $pdo): string {
  for ($i = 0; $i < 5; $i++) {
    $token = bin2hex(random_bytes(16)); // 32 hex
    $cek = $pdo->prepare("SELECT 1 FROM users WHERE public_id = ? LIMIT 1");
    $cek->execute([$token]);
    if (!$cek->fetchColumn()) return $token;
  }
  return bin2hex(random_bytes(16));
}

// =========================
// POST
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username  = trim($_POST["username"] ?? "");
  $emailNew  = trim($_POST["email"] ?? "");
  $full_name = trim($_POST["full_name"] ?? "");
  $roleNew   = trim($_POST["role"] ?? "user");
  $is_active = isset($_POST["is_active"]) ? 1 : 0;
  $password  = (string)($_POST["password"] ?? "");

  if ($username === "" || $emailNew === "" || $password === "") {
    $error = "Username, email, dan password wajib diisi.";
  } elseif (preg_match('/\s/', $username)) {
    $error = "Username tidak boleh mengandung spasi.";
  } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
    $error = "Username hanya boleh huruf/angka/titik/underscore/dash. (min 3 karakter)";
  } elseif (!filter_var($emailNew, FILTER_VALIDATE_EMAIL)) {
    $error = "Format email tidak valid.";
  } elseif (strlen($password) < 6) {
    $error = "Password minimal 6 karakter.";
  } else {
    $cek = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
    $cek->execute([$emailNew, $username]);
    if ((int)$cek->fetchColumn() > 0) {
      $error = "Email atau username sudah digunakan.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $public_id_new = generate_public_id($pdo);

      $stmt = $pdo->prepare("
        INSERT INTO users (public_id, username, email, password_hash, full_name, role, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
      ");
      $stmt->execute([
        $public_id_new,
        $username,
        $emailNew,
        $hash,
        ($full_name ?: null),
        ($roleNew ?: null),
        $is_active
      ]);

      $newId = (int)$pdo->lastInsertId();

      [$dbPhoto, $upErr] = uploadPhoto($newId, $_FILES["photo"] ?? []);
      if ($upErr) {
        $error = $upErr;
      } else {
        if ($dbPhoto) {
          $upd = $pdo->prepare("UPDATE users SET photo = ?, updated_at = NOW() WHERE user_id = ?");
          $upd->execute([$dbPhoto, $newId]);
        }
        header("Location: index.php?success=created");
        exit;
      }
    }
  }
}

// helper escape
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<?php include __DIR__ . '/../partials/head.php'; ?>

<style>
  .page-hero {
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(74,108,247,.14), rgba(74,108,247,.03));
    border: 1px solid rgba(0,0,0,.05);
  }
  .card-soft {
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 10px 25px rgba(0,0,0,.04);
    overflow: hidden;
  }
  .form-card .card-header {
    background: #fff;
    border-bottom: 1px solid rgba(0,0,0,.06);
    padding: 16px 18px;
  }
  .form-card .card-body { padding: 18px; }

  .section-title {
    font-weight: 700;
    font-size: 16px;
    margin: 0;
  }
  .section-subtitle {
    font-size: 13px;
    color: #6c757d;
    margin-top: 3px;
  }
  .label-req::after {
    content: " *";
    color: #dc3545;
    font-weight: 700;
  }
  .helper {
    font-size: 12.5px;
    color: #6c757d;
    margin-top: 6px;
  }

  .input-icon { position: relative; }
  .input-icon > i {
    position: absolute;
    top: 50%;
    left: 12px;
    transform: translateY(-50%);
    color: rgba(0,0,0,.45);
    font-size: 18px;
    pointer-events: none;
  }
  .input-icon .form-control {
    padding-left: 42px;
    border-radius: 12px;
  }

  .form-control, .form-select {
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,.12);
    padding: 10px 12px;
  }
  .form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 .2rem rgba(74,108,247,.16);
    border-color: rgba(74,108,247,.55);
  }

  .actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid rgba(0,0,0,.06);
  }

  .btn-premium {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 14px;
    background: #4a6cf7;
    color: #fff;
    box-shadow: 0 10px 20px rgba(74,108,247,.25);
    transition: transform .12s ease, box-shadow .12s ease;
  }
  .btn-premium:hover {
    transform: translateY(-1px);
    color: #fff;
    box-shadow: 0 14px 26px rgba(74,108,247,.28);
  }
  .btn-premium .btn-ic {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: rgba(255,255,255,.16);
  }

  .profile-card { position: sticky; top: 88px; }

  .photo-box {
    border-radius: 16px;
    border: 1px dashed rgba(0,0,0,.18);
    padding: 14px;
    text-align: center;
    background: rgba(0,0,0,.02);
  }
  .avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 999px;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 12px 26px rgba(0,0,0,.10);
    background: #f8f9fa;
  }

  .badge-role {
    font-size: 11px;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 800;
    letter-spacing: .35px;
    text-transform: uppercase;
    border: 1px solid rgba(0,0,0,.06);
  }
  .badge-user  { background: rgba(74,108,247,.12); color: #4a6cf7; }
  .badge-admin { background: rgba(220,53,69,.12); color: #dc3545; }
  .badge-pic   { background: rgba(25,135,84,.12); color: #198754; }

  @media (max-width: 576px) {
    .form-card .card-body { padding: 14px; }
  }
</style>

<body>
<div id="layout-wrapper">

  <!-- ================= TOPBAR ================= -->
  <?php include __DIR__ . '/../partials/topbar.php'; ?>

  <!-- ================= SIDEBAR ================= -->
  <?php $activeMenu = 'users'; ?>
  <?php include __DIR__ . '/../partials/sidebar.php'; ?>

  <!-- ================= CONTENT ================= -->
  <div class="main-content">
    <div class="page-content">

      <div class="page-title-box">
        <div class="container-fluid">
          <div class="row align-items-center page-hero p-3 p-md-4">
            <div class="col-md-8">
              <div class="page-title">
                <h4 class="mb-1"><?= h($pageTitle) ?></h4>
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">Meta Inventory</li>
                  <li class="breadcrumb-item"><a href="index.php">Pengguna</a></li>
                  <li class="breadcrumb-item active">Tambah</li>
                </ol>
              </div>
            </div>
            <div class="col-md-4 text-end mt-3 mt-md-0">
              <a href="index.php" class="btn btn-light">
                <i class="mdi mdi-arrow-left"></i> Kembali
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="container-fluid">
        <div class="page-content-wrapper">

          <?php if ($error): ?>
            <div class="alert alert-danger card-soft">
              <i class="mdi mdi-alert-circle-outline me-1"></i>
              <?= h($error) ?>
            </div>
          <?php endif; ?>

          <div class="row g-4">
            <!-- LEFT: preview -->
            <div class="col-lg-4">
              <div class="card card-soft profile-card">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <div class="section-title">Preview Pengguna</div>
                      <div class="section-subtitle">Cek data sebelum disimpan.</div>
                    </div>
                    <span id="roleBadge" class="badge-role badge-user">USER</span>
                  </div>

                  <div class="mt-3 photo-box">
                    <img
                      id="photoPreview"
                      class="avatar-preview"
                      src="<?= h(get_user_photo("")) ?>"
                      alt="preview"
                    >
                    <div class="helper mt-3">Upload foto untuk sidebar/topbar.</div>
                  </div>

                  <div class="mt-3">
                    <div class="helper mb-1">Nama</div>
                    <div class="fw-semibold" id="prevName">-</div>

                    <div class="helper mb-1 mt-3">Email</div>
                    <div class="fw-semibold" id="prevEmail">-</div>

                    <div class="helper mb-1 mt-3">Username</div>
                    <div class="fw-semibold" id="prevUsername">-</div>

                    <div class="helper mb-1 mt-3">Status</div>
                    <div class="fw-semibold" id="prevStatus">Active</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- RIGHT: form -->
            <div class="col-lg-8">
              <div class="card form-card card-soft">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div>
                    <h5 class="section-title mb-0">Tambah Pengguna</h5>
                    <div class="section-subtitle">Isi data dengan benar.</div>
                  </div>
                </div>

                <div class="card-body">
                  <form method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="row g-3">

                      <div class="col-md-6">
                        <label class="form-label label-req">Username</label>
                        <div class="input-icon">
                          <i class="mdi mdi-account"></i>
                          <input
                            id="username"
                            class="form-control"
                            name="username"
                            placeholder="contoh: ilhamdwi"
                            value="<?= h($_POST["username"] ?? "") ?>"
                            required
                          >
                        </div>
                        <div class="helper">Huruf/angka/titik/underscore/dash (min 3 karakter).</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label label-req">Email</label>
                        <div class="input-icon">
                          <i class="mdi mdi-email-outline"></i>
                          <input
                            id="email"
                            type="email"
                            class="form-control"
                            name="email"
                            placeholder="contoh@domain.com"
                            value="<?= h($_POST["email"] ?? "") ?>"
                            required
                          >
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="input-icon">
                          <i class="mdi mdi-card-account-details-outline"></i>
                          <input
                            id="full_name"
                            class="form-control"
                            name="full_name"
                            placeholder="Masukkan nama lengkap"
                            value="<?= h($_POST["full_name"] ?? "") ?>"
                          >
                        </div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="role">
                          <?php
                            $roles = ["admin","pic","user"];
                            $cur = $_POST["role"] ?? "user";
                            foreach ($roles as $r) {
                              $sel = ($cur === $r) ? "selected" : "";
                              echo "<option value=\"".h($r)."\" $sel>".h(strtoupper($r))."</option>";
                            }
                          ?>
                        </select>
                        <div class="helper">Admin kelola semua menu. PIC kelola data PC.</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label label-req">Password</label>
                        <div class="input-group">
                          <span class="input-group-text" style="border-radius:12px 0 0 12px;">
                            <i class="mdi mdi-lock-outline"></i>
                          </span>
                          <input
                            id="password"
                            type="password"
                            class="form-control"
                            name="password"
                            placeholder="minimal 6 karakter"
                            required
                            minlength="6"
                            style="border-radius:0 12px 12px 0;"
                          >
                          <button class="btn btn-outline-secondary" type="button" id="togglePass" style="border-radius:12px;">
                            <i class="mdi mdi-eye-outline"></i>
                          </button>
                        </div>
                        <div class="helper">Minimal 6 karakter.</div>
                      </div>

                      <div class="col-md-6">
                        <label class="form-label">Photo (opsional)</label>
                        <input id="photoInput" type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/webp">
                        <div class="helper">JPG/PNG/WEBP max 2MB.</div>
                      </div>

                      <div class="col-12">
                        <div class="form-check mt-1">
                          <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                            <?= isset($_POST["is_active"]) ? "checked" : "checked" ?>>
                          <label class="form-check-label" for="is_active">Active</label>
                        </div>
                      </div>

                    </div>

                    <div class="actions-bar">
                      <a class="btn btn-light" href="index.php">
                        <i class="mdi mdi-close me-1"></i> Cancel
                      </a>
                      <button class="btn-premium" type="submit" style="border:none;">
                        <div class="btn-ic"><i class="mdi mdi-content-save-outline"></i></div>
                        <span>Save Data</span>
                      </button>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div><!-- /row -->

        </div>
      </div>

    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../partials/footer.php'; ?>

  </div>
</div>

<div class="rightbar-overlay"></div>

<?php include __DIR__ . '/../partials/scripts.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const username = document.getElementById('username');
  const email = document.getElementById('email');
  const fullName = document.getElementById('full_name');
  const role = document.getElementById('role');
  const isActive = document.getElementById('is_active');

  const prevUsername = document.getElementById('prevUsername');
  const prevEmail = document.getElementById('prevEmail');
  const prevName = document.getElementById('prevName');
  const prevStatus = document.getElementById('prevStatus');
  const roleBadge = document.getElementById('roleBadge');

  const photoInput = document.getElementById('photoInput');
  const photoPreview = document.getElementById('photoPreview');

  // username: remove spaces + preview
  function syncUsername(){
    if (!username) return;
    username.value = username.value.replace(/\s+/g,'');
    if (prevUsername) prevUsername.textContent = username.value || '-';
  }

  // email preview
  function syncEmail(){
    if (!email) return;
    if (prevEmail) prevEmail.textContent = email.value || '-';
  }

  // name preview: full_name fallback to username
  function syncName(){
    const nameVal = (fullName && fullName.value.trim()) ? fullName.value.trim() : (username ? username.value : '');
    if (prevName) prevName.textContent = nameVal || '-';
  }

  // status preview
  function syncStatus(){
    if (!prevStatus || !isActive) return;
    prevStatus.textContent = isActive.checked ? 'Active' : 'Inactive';
  }

  // role badge color
  function syncRole(){
    if (!roleBadge || !role) return;
    const r = (role.value || 'user').toLowerCase();

    roleBadge.classList.remove('badge-user','badge-admin','badge-pic');

    if (r === 'admin') roleBadge.classList.add('badge-admin');
    else if (r === 'pic') roleBadge.classList.add('badge-pic');
    else roleBadge.classList.add('badge-user');

    roleBadge.textContent = r.toUpperCase();
  }

  if (username) {
    username.addEventListener("input", function(){
      syncUsername();
      syncName();
    });
  }

  if (email) email.addEventListener("input", syncEmail);
  if (fullName) fullName.addEventListener("input", syncName);
  if (role) role.addEventListener("change", syncRole);
  if (isActive) isActive.addEventListener("change", syncStatus);

  // initial sync
  syncUsername();
  syncEmail();
  syncName();
  syncRole();
  syncStatus();

  // photo preview
  if (photoInput && photoPreview) {
    photoInput.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      photoPreview.src = url;
    });
  }

  // password toggle
  const togglePass = document.getElementById('togglePass');
  const password = document.getElementById('password');
  if (togglePass && password) {
    togglePass.addEventListener('click', () => {
      const isPwd = password.type === 'password';
      password.type = isPwd ? 'text' : 'password';
      togglePass.innerHTML = isPwd
        ? '<i class="mdi mdi-eye-off-outline"></i>'
        : '<i class="mdi mdi-eye-outline"></i>';
    });
  }
});
</script>

</body>
</html>
