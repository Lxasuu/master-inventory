<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin']);
require_once __DIR__ . "/../partials/app_context.php";

$pageTitle = "Edit User";
$activeMenu = "users";

$publicId = trim((string)($_GET["u"] ?? ""));
if ($publicId === '' || !preg_match('/^[a-f0-9]{32}$/i', $publicId)) {
  header("Location: index.php");
  exit;
}

$error = $_GET["error"] ?? "";

$stmt = $pdo->prepare("
  SELECT user_id, public_id, username, email, full_name, role, is_active, photo, last_login_at
  FROM users
  WHERE public_id = ?
  LIMIT 1
");
$stmt->execute([$publicId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  header("Location: index.php");
  exit;
}

// pastikan slash aman
$photoEdit = !empty($user["photo"])
  ? rtrim($BASE_URL, "/") . "/" . ltrim($user["photo"], "/")
  : rtrim($BASE_URL, "/") . "/assets/images/default-avatar.png";

$error_map = [
  "invalid_user"       => "User tidak valid.",
  "invalid_role"       => "Role tidak valid.",
  "invalid_email"      => "Email tidak valid.",
  "email_exists"       => "Email sudah digunakan user lain.",
  "username_exists"    => "Username sudah digunakan user lain.",
  "username_invalid"   => "Username tidak valid (tanpa spasi, hanya huruf/angka/._-).",
  "upload_failed"      => "Upload foto gagal.",
  "photo_too_large"    => "Ukuran foto terlalu besar (max 2MB).",
  "invalid_photo_type" => "Format foto harus JPG/PNG/WEBP.",
  "upload_dir_missing" => "Folder upload tidak ditemukan.",
  "upload_move_failed" => "Gagal menyimpan file upload.",
  "password_too_short" => "Password minimal 6 karakter.",
  "update_failed"      => "Update gagal. Cek log untuk detail.",
];
$err_text = $error_map[$error] ?? "";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

require_once __DIR__ . "/../partials/layout_start.php";
?>

<style>
  /* ====== polish UI ====== */
  .page-hero {
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(74,108,247,.12), rgba(74,108,247,.02));
    border: 1px solid rgba(0,0,0,.05);
  }
  .card-soft {
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 10px 25px rgba(0,0,0,.04);
  }
  .profile-card {
    position: sticky;
    top: 88px;
  }
  .avatar-xxl {
    width: 96px;
    height: 96px;
    object-fit: cover;
    border-radius: 999px;
  }
  .avatar-preview {
    width: 128px;
    height: 128px;
    object-fit: cover;
    border-radius: 999px;
    border: 4px solid #fff;
    box-shadow: 0 12px 26px rgba(0,0,0,.10);
    background: #f8f9fa;
  }
  .label-hint { font-size: .85rem; color: #6c757d; }
  .divider {
    height: 1px;
    background: rgba(0,0,0,.06);
    margin: 18px 0;
  }
</style>

<div class="page-title-box">
  <div class="container-fluid">
    <div class="row align-items-center page-hero p-3 p-md-4">
      <div class="col-md-8">
        <div class="page-title">
          <h4 class="mb-1"><?= h($pageTitle) ?></h4>
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item">Meta Inventory</li>
            <li class="breadcrumb-item"><a href="index.php">Pengguna</a></li>
            <li class="breadcrumb-item active">Edit</li>
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

    <?php if ($err_text): ?>
      <div class="alert alert-danger card-soft">
        <i class="mdi mdi-alert-circle-outline me-1"></i>
        <?= h($err_text) ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- LEFT: Profile summary -->
      <div class="col-lg-4">
        <div class="card card-soft profile-card">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <img id="avatarLeft" src="<?= h($photoEdit) ?>" alt="avatar" class="avatar-xxl">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h5 class="mb-0"><?= h($user["full_name"] ?: $user["username"]) ?></h5>
                  <?php
                    $role = strtolower((string)($user["role"] ?? "user"));
                    $badgeClass = "badge bg-primary-subtle text-primary";
                    if ($role === "admin") $badgeClass = "badge bg-danger-subtle text-danger";
                    if ($role === "pic")   $badgeClass = "badge bg-success-subtle text-success";
                  ?>
                  <span class="<?= h($badgeClass) ?> text-uppercase"><?= h($role) ?></span>
                </div>
                <div class="text-muted mt-1"><?= h($user["email"]) ?></div>
              </div>
            </div>

            <div class="divider"></div>

            <div class="row g-3">
              <div class="col-6">
                <div class="label-hint">Username</div>
                <div class="fw-semibold"><?= h($user["username"] ?? "-") ?></div>
              </div>
              <div class="col-6">
                <div class="label-hint">Status</div>
                <div class="fw-semibold">
                  <?php if ((int)$user["is_active"] === 1): ?>
                    <span class="badge bg-success-subtle text-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-12">
                <div class="label-hint">Last login</div>
                <div class="fw-semibold">
                  <?= h($user["last_login_at"] ? $user["last_login_at"] : "-") ?>
                </div>
              </div>
            </div>

            <div class="divider"></div>

            <div class="text-muted small">
              Tips: upload foto square biar hasilnya rapi (JPG/PNG/WEBP, max 2MB).
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Form -->
      <div class="col-lg-8">
        <div class="card card-soft">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
              <div>
                <h4 class="card-title mb-1">Edit Data Pengguna</h4>
                <p class="card-title-desc mb-0">Ubah informasi akun, status, password, dan foto pengguna.</p>
              </div>
            </div>

            <form action="update.php" method="POST" enctype="multipart/form-data" autocomplete="off">
              <input type="hidden" name="user_id" value="<?= (int)$user['user_id'] ?>">
              <input type="hidden" name="public_id" value="<?= h($user['public_id']) ?>">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Username <span class="text-danger">*</span></label>
                  <input class="form-control" name="username" value="<?= h($user["username"] ?? "") ?>" required>
                  <div class="label-hint mt-1">Huruf, angka, titik, underscore, dash.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input class="form-control" type="email" name="email" value="<?= h($user["email"]) ?>" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Nama Lengkap</label>
                  <input class="form-control" name="full_name" value="<?= h($user["full_name"] ?? "") ?>">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Role</label>
                  <select class="form-select" name="role">
                    <?php
                      $roles = ["admin","user","pic"];
                      foreach ($roles as $r) {
                        $sel = (($user["role"] ?? "") === $r) ? "selected" : "";
                        echo "<option value=\"".h($r)."\" $sel>".h(strtoupper($r))."</option>";
                      }
                    ?>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Status</label>
                  <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-switch" dir="ltr">
                      <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                        <?= ((int)$user["is_active"]===1) ? "checked" : "" ?>>
                      <label class="form-check-label" for="is_active">Active / Inactive</label>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">New Password (optional)</label>
                  <div class="input-group">
                    <input id="new_password" type="password" class="form-control" name="new_password"
                      placeholder="Biarkan kosong jika tidak diganti" minlength="6">
                    <button class="btn btn-outline-secondary" type="button" id="togglePass">
                      <i class="mdi mdi-eye-outline"></i>
                    </button>
                  </div>
                  <div class="label-hint mt-1">Minimal 6 karakter.</div>
                </div>
              </div>

              <div class="divider"></div>

              <div class="row g-3 align-items-center">
                <div class="col-md-8">
                  <label class="form-label">Ganti Foto (optional)</label>
                  <input id="photoInput" type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/webp">
                  <div class="label-hint mt-1">Max 2MB (JPG/PNG/WEBP)</div>

                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                    <label class="form-check-label" for="remove_photo">Hapus foto saat ini</label>
                  </div>
                </div>

                <div class="col-md-4 text-center">
                  <img id="photoPreview" src="<?= h($photoEdit) ?>" alt="preview" class="avatar-preview">
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-light" href="index.php">Cancel</a>
                <button class="btn btn-primary" type="submit">
                  <i class="mdi mdi-content-save-outline me-1"></i> Update Data
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div><!-- /row -->
  </div>
</div>

<script>
  // Password toggle
  (function () {
    const btn = document.getElementById('togglePass');
    const input = document.getElementById('new_password');
    if (btn && input) {
      btn.addEventListener('click', () => {
        const isPwd = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPwd ? 'text' : 'password');
        btn.innerHTML = isPwd
          ? '<i class="mdi mdi-eye-off-outline"></i>'
          : '<i class="mdi mdi-eye-outline"></i>';
      });
    }
  })();

  // Photo preview + sync left avatar
  (function () {
    const input = document.getElementById('photoInput');
    const preview = document.getElementById('photoPreview');
    const avatarLeft = document.getElementById('avatarLeft');
    const remove = document.getElementById('remove_photo');

    const originalSrc = preview ? preview.getAttribute('src') : '';

    function setSrc(src) {
      if (preview) preview.src = src;
      if (avatarLeft) avatarLeft.src = src;
    }

    if (input) {
      input.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;

        const url = URL.createObjectURL(file);
        setSrc(url);

        if (remove) remove.checked = false;
      });
    }

    if (remove) {
      remove.addEventListener('change', () => {
        if (remove.checked) {
          if (input) input.value = '';
          setSrc(originalSrc);
        }
      });
    }
  })();
</script>

<?php require_once __DIR__ . "/../partials/layout_end.php"; ?>
