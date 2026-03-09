<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_once __DIR__ . "/../partials/csrf.php";
require_role(['user','pic','admin']);

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/../partials/app_context.php";

$pageTitle  = "Profile";
$activeMenu = "profile";

$success = $_GET["success"] ?? "";
$error   = $_GET["error"] ?? "";

$stmt = $pdo->prepare("SELECT user_id, username, email, full_name, role, photo FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . "/../partials/layout_start.php";
?>

<style>
  .profile-cover {
    height: 120px;
    background: linear-gradient(135deg, rgba(59,93,231,.15), rgba(120,140,255,.10));
    border-radius: 16px;
  }
  .avatar-xl2{
    width: 110px; height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
    margin-top: -55px;
  }
  .helper-text{ font-size: 12px; color: #6c757d; }
</style>

<div class="page-title-box">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-md-6">
        <div class="page-title">
          <h4><?= htmlspecialchars($pageTitle) ?></h4>
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item">Meta Inventory</li>
            <li class="breadcrumb-item active">Profile</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($success): ?>
  <div class="alert alert-success">
    <i class="mdi mdi-check-circle-outline me-1"></i>
    Perubahan berhasil disimpan.
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="mdi mdi-alert-circle-outline me-1"></i>
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<div class="row g-3">

  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <div class="profile-cover"></div>

        <div class="text-center">
          <img id="avatarPreview" src="<?= htmlspecialchars($photo) ?>" class="avatar-xl2" alt="avatar">
          <h5 class="mt-3 mb-0"><?= htmlspecialchars($fullName) ?></h5>
          <div class="text-muted"><?= htmlspecialchars($email) ?></div>
          <span class="badge bg-primary-subtle text-primary mt-2"><?= htmlspecialchars($role) ?></span>
        </div>

        <hr class="my-4">

        <div class="mt-2 helper-text">
          
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-1">Edit Profil</h5>
        <div class="text-muted mb-3">Lengkapi data di bawah lalu klik Simpan.</div>

        <form method="post" action="<?= $BASE_URL ?>profile/profile-update.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input class="form-control" name="full_name"
                     value="<?= htmlspecialchars($u["full_name"] ?? "") ?>"
                     placeholder="Nama lengkap">
            </div>

            <div class="col-md-6">
              <label class="form-label">Username *</label>
              <input class="form-control" name="username"
                    value="<?= htmlspecialchars($u["username"] ?? "") ?>"
                    placeholder="username" required>
              <div class="helper-text mt-1">Boleh: huruf, angka, titik, underscore, dash, 3–20 karakter.</div>
            </div>


            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input class="form-control" name="email"
                     value="<?= htmlspecialchars($u["email"] ?? $email) ?>" required>
            </div>

            <div class="col-12">
              <label class="form-label">Ganti Foto (opsional)</label>
              <input id="photoInput" type="file" class="form-control" name="photo" accept="image/png,image/jpeg,image/webp">
              <div class="helper-text mt-1">Format JPG/PNG/WEBP, maksimal 2MB.</div>

              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                <label class="form-check-label" for="remove_photo">Hapus foto sekarang</label>
              </div>
            </div>

            <div class="col-12">
              <hr class="my-2">
              <h6 class="mb-2">Ganti Password</h6>
              <div class="helper-text mb-2">
                Jika ingin ganti password, isi Password Lama dan Password Baru.
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Password Lama</label>
              <input type="password" class="form-control" name="old_password" placeholder="Wajib jika mau ganti password">
            </div>

            <div class="col-md-6">
              <label class="form-label">Password Baru</label>
              <input type="password" class="form-control" name="new_password" placeholder="Minimal 6 karakter">
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end mt-2">
              <a href="<?= $BASE_URL ?>index.php" class="btn btn-light">Batal</a>
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-content-save-outline me-1"></i> Simpan Perubahan
              </button>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>

</div>

<script>
(function () {
  const input  = document.getElementById('photoInput');
  const avatar = document.getElementById('avatarPreview');
  const remove = document.getElementById('remove_photo');

  if (!avatar) return;

  const originalSrc = avatar.getAttribute('src');

  function setSrc(src) {
    avatar.src = src;
  }

  if (input) {
    input.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;

      // preview instan tanpa save
      const url = URL.createObjectURL(file);
      setSrc(url);

      // kalau user pilih file baru, otomatis uncheck remove
      if (remove) remove.checked = false;
    });
  }

  if (remove) {
    remove.addEventListener('change', () => {
      if (remove.checked) {
        // kosongkan file input & balikkan preview ke foto lama
        if (input) input.value = '';
        setSrc(originalSrc);
      }
    });
  }
})();
</script>

<?php require_once __DIR__ . "/../partials/layout_end.php"; ?>