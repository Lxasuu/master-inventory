<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin']);

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/../partials/app_context.php";

$pageTitle  = "Detail Pengguna";
$activeMenu = "users";


$public_id = (string)($_GET["u"] ?? "");
if (!preg_match('/^[a-f0-9]{32}$/i', $public_id)) {
  header("Location: index.php");
  exit;
}

// ambil user target by public_id
$stmt = $pdo->prepare("
  SELECT user_id, public_id, username, email, full_name, role, is_active, photo, last_login_at
  FROM users
  WHERE public_id = ?
  LIMIT 1
");
$stmt->execute([$public_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
  header("Location: index.php?error=" . urlencode("User tidak ditemukan."));
  exit;
}

// helper avatar url (pakai default yang konsisten)
function user_avatar_url(array $row, string $BASE_URL): string {
  $default = $BASE_URL . "assets/images/default-avatar.png";

  $photoDb = $row["photo"] ?? "";
  if (!$photoDb) return $default;

  return $BASE_URL . ltrim($photoDb, "/");
}

$avatar = user_avatar_url($u, $BASE_URL);

require_once __DIR__ . "/../partials/layout_start.php";
?>

<div class="page-title-box">
  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-md-6">
        <div class="page-title">
          <h4><?= htmlspecialchars($pageTitle) ?></h4>
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item">Meta Inventory</li>
            <li class="breadcrumb-item"><a href="<?= $BASE_URL ?>users/index.php">Pengguna</a></li>
            <li class="breadcrumb-item active">Detail</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="page-content-wrapper">
    <div class="row g-3">

      <div class="col-lg-4">
        <div class="card">
          <div class="card-body text-center">
            <img src="<?= htmlspecialchars($avatar) ?>"
                 alt="avatar"
                 style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:4px solid #fff;box-shadow:0 8px 24px rgba(0,0,0,.08);">

            <h5 class="mt-3 mb-0"><?= htmlspecialchars($u["full_name"] ?: "-") ?></h5>
            <div class="text-muted"><?= htmlspecialchars($u["email"]) ?></div>

            <div class="mt-2">
              <span class="badge bg-primary-subtle text-primary">
                <?= htmlspecialchars($u["role"]) ?>
              </span>

              <?php $active = ((int)$u["is_active"] === 1); ?>
              <span class="badge <?= $active ? "bg-success" : "bg-danger" ?> ms-1">
                <?= $active ? "Active" : "Inactive" ?>
              </span>
            </div>

            <hr class="my-4">

            <div class="text-start">
              <div class="mb-2">
                <div class="text-muted" style="font-size:12px;">Username</div>
                <div class="fw-semibold"><?= htmlspecialchars($u["username"] ?: "-") ?></div>
              </div>

              <div class="mb-2">
                <div class="text-muted" style="font-size:12px;">User ID (internal)</div>
                <div class="fw-semibold"><?= (int)$u["user_id"] ?></div>
              </div>

              <div class="mb-2">
                <div class="text-muted" style="font-size:12px;">Login Terakhir</div>
                <div class="fw-semibold"><?= htmlspecialchars($u["last_login_at"] ?? "-") ?></div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card">
          <div class="card-body">
            <h5 class="mb-1">Informasi Pengguna</h5>
            <div class="text-muted mb-3">Detail lengkap data pengguna.</div>

            <div class="table-responsive">
              <table class="table table-bordered">
                <tr>
                  <th style="width:220px;">Username</th>
                  <td><?= htmlspecialchars($u["username"] ?: "-") ?></td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td><?= htmlspecialchars($u["email"]) ?></td>
                </tr>
                <tr>
                  <th>Nama Lengkap</th>
                  <td><?= htmlspecialchars($u["full_name"] ?: "-") ?></td>
                </tr>
                <tr>
                  <th>Role</th>
                  <td><?= htmlspecialchars($u["role"]) ?></td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><?= ((int)$u["is_active"] === 1) ? "Active" : "Inactive" ?></td>
                </tr>
                <tr>
                  <th>Login Terakhir</th>
                  <td><?= htmlspecialchars($u["last_login_at"] ?? "-") ?></td>
                </tr>
              </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="<?= $BASE_URL ?>users/index.php" class="btn btn-light">Kembali</a>

              <a href="<?= $BASE_URL ?>users/edit.php?u=<?= htmlspecialchars($u["public_id"]) ?>" class="btn btn-primary">
                <i class="mdi mdi-pencil-outline me-1"></i> Edit
              </a>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../partials/layout_end.php"; ?>
