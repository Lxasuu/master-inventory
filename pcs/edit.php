<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin','pic']);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$pageTitle = "Edit Data PC";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header("Location: index.php");
  exit;
}

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/../partials/app_context.php";

// =======================
// DATA PC
// =======================
$stmt = $pdo->prepare("SELECT * FROM pcs WHERE pc_id = ? LIMIT 1");
$stmt->execute([$id]);
$pc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$pc) {
  header("Location: index.php");
  exit;
}

// dropdown master
$locations  = $pdo->query("SELECT location_id, location_name FROM locations ORDER BY location_name")->fetchAll();
$conditions = $pdo->query("SELECT condition_id, condition_name FROM conditions ORDER BY condition_name")->fetchAll();
$statuses   = $pdo->query("SELECT check_status_id, status_name FROM check_statuses ORDER BY status_name")->fetchAll();

// apps terpasang
$stmt = $pdo->prepare("
  SELECT a.app_name
  FROM pc_applications pa
  JOIN applications a ON a.app_id = pa.app_id
  WHERE pa.pc_id = ?
  ORDER BY a.app_name
");
$stmt->execute([$id]);
$appNames = array_map(fn($r) => $r['app_name'], $stmt->fetchAll(PDO::FETCH_ASSOC));
$appsText = implode(", ", $appNames);

// NOTE TERAKHIR (EXCEL-LIKE)
$stmt = $pdo->prepare("
  SELECT change_note
  FROM pc_updates
  WHERE pc_id = ?
  ORDER BY updated_at DESC, update_id DESC
  LIMIT 1
");
$stmt->execute([$id]);
$lastNote = (string)($stmt->fetchColumn() ?? '');

$error_note = '';
$internet_note = '';
if ($lastNote !== '') {
  $parts = array_map('trim', explode('|', $lastNote));
  foreach ($parts as $p) {
    if (stripos($p, 'Error:') === 0) $error_note = trim(substr($p, 6));
    if (stripos($p, 'InternetNote:') === 0) $internet_note = trim(substr($p, 13));
  }
}

// AUTO PIC: selalu user login
$pic_name = $fullName ?: $email;
if (!$pic_name) $pic_name = "Unknown User";

// badge kecil status
$isReady = (int)($pc['is_ready'] ?? 0) === 1;
$hasInet = (int)($pc['internet'] ?? 0) === 1;

$readyBadgeClass = $isReady ? "bg-success" : "bg-secondary";
$readyBadgeText  = $isReady ? "Ready" : "Not Ready";

$inetBadgeClass = $hasInet ? "bg-primary" : "bg-warning";
$inetBadgeText  = $hasInet ? "Internet OK" : "No Internet";
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?></title>
  <link rel="shortcut icon" href="../assets/images/META/meta logo.png">

  <!-- Morvin CSS -->
  <link href="../assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
  <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <link href="../assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
  <link href="../assets/css/app.custom.css" rel="stylesheet" />

  <style>
    .card-elev { border: 0; border-radius: 16px; box-shadow: 0 10px 30px rgba(31,45,61,.08); }
    .card-head {
      border-radius: 16px 16px 0 0;
      background: linear-gradient(135deg, rgba(59,93,231,.10), rgba(59,93,231,.04));
      border-bottom: 1px solid rgba(0,0,0,.04);
    }
    .section-title {
      font-weight: 700;
      font-size: 12px;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #6c757d;
      margin: 18px 0 10px;
    }
    .form-label { font-weight: 600; }
    .form-control, .form-select { border-radius: 12px; }
    .input-group-text { border-radius: 12px; }
    .soft-box { background: #fff; border: 1px dashed rgba(0,0,0,.10); border-radius: 14px; padding: 12px; }
    .sticky-actions {
      position: sticky; bottom: 14px; z-index: 20;
      background: rgba(245,247,251,.9);
      backdrop-filter: blur(6px);
      padding-top: 10px;
    }
    .btn { border-radius: 12px; }
    .kbd-hint { font-size: 12px; color: #6c757d; }

    .readonly-pill {
      background: #f1f3f9;
      border: 1px solid rgba(0,0,0,.06);
      border-radius: 12px;
      padding: 10px 12px;
    }

    .pc-meta-badges { display:flex; flex-wrap:wrap; gap:8px; margin-top:6px; }
    .pc-meta-badges .badge { font-weight: 700; }
  </style>
</head>

<body>
<div id="layout-wrapper">

  <!-- ================= TOPBAR ================= -->
  <header id="page-topbar">
                <div class="navbar-header">
                    <div class="d-flex">
                        
                        <!-- LOGO -->

                    <div class="navbar-brand-box d-flex align-items-center gap-2">

                    <!-- Logo -->
                    <a href="index.php" class="topbar-logo-wrap d-flex align-items-center">
                        <img src="/HTML/assets/images/logo-meta copy.png"
                            alt="Meta Inventory"
                            class="topbar-logo-img">
                        
                    </a>

                    <!-- Hamburger -->
                    <button type="button"
                            class="btn header-item waves-effect topbar-hamburger"
                            id="vertical-menu-btn"
                            aria-label="Toggle menu">
                        <i class="mdi mdi-menu"></i>
                    </button>

                    </div>

                    </div>

                    <div class="d-flex">

                        <div class="dropdown d-none d-lg-inline-block ms-1">
                            <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                                <i class="mdi mdi-fullscreen"></i>
                            </button>
                        </div>

                        <div class="dropdown d-inline-block">
                            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-bell-outline bx-tada"></i>
                                <?php if ($notifUnread > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $notifUnread ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-notifications-dropdown">
                                <div class="p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0"> Notikasi </h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="small js-read-all-notif"> Baca semua pesan</a>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 230px;">
                                    <?php if (empty($notifications)): ?>
                                        <div class="p-3 text-muted">Belum ada notifikasi.</div>
                                    <?php else: ?>
                                    <?php foreach ($notifications as $n): ?>
                                    <?php
                                        // icon
                                        $icon = "mdi-information-outline";
                                        $bg   = "bg-primary";

                                        if ($n['action'] === 'CREATE_PC') { $icon = "mdi-plus-circle-outline"; $bg = "bg-success"; }
                                        if ($n['action'] === 'UPDATE_PC') { $icon = "mdi-pencil-outline";      $bg = "bg-warning"; }
                                        if ($n['action'] === 'DELETE_PC') { $icon = "mdi-delete-outline";      $bg = "bg-danger"; }

                                        // link (kalau entity pcs, arahkan ke edit)
                                        $link = "javascript:void(0)";
                                        if ($n['entity'] === 'pcs' && !empty($n['entity_id'])) {
                                        $target = "pcs/edit.php?id=" . (int)$n['entity_id'];
                                        $link = "notification/read.php?id=".(int)$n['log_id']."&to=".urlencode($target);

                                        }

                                        $isUnread = ((int)$n['is_read'] === 0);
                                    ?>

                                    <a href="<?= htmlspecialchars($link) ?>"
                                        class="text-reset notification-item <?= $isUnread ? 'bg-light' : '' ?>">
                                        <div class="media">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title <?= $bg ?> rounded-circle font-size-16">
                                            <i class="mdi <?= $icon ?> text-white"></i>
                                            </span>
                                        </div>
                                        <div class="media-body">
                                            <h6 class="mt-0 mb-1"><?= htmlspecialchars($n['title']) ?></h6>
                                            <div class="font-size-13 text-muted">
                                            <?php if (!empty($n['created_at'])): ?>
                                                <p class="mb-0">
                                                <i class="mdi mdi-clock-outline"></i>
                                                <?= htmlspecialchars($n['created_at']) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($isUnread): ?>
                                                <span class="badge bg-danger mt-1">New</span>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="p-2 border-top">
                                <a class="btn btn-sm btn-link font-size-14 w-100 text-center js-read-all-notif" href="#">
                                    <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                                </a>
                            </div>
                        </div>
                    </div>

                     <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user"
                            src="<?= htmlspecialchars($photo) ?>"
                            alt="Header Avatar">

                            <span class="d-none d-xl-inline-block ms-1">
                                <?= htmlspecialchars($fullName) ?>
                            </span>

                            <small class="d-none d-xl-inline-block text-muted ms-1">
                                <?= htmlspecialchars($email) ?>
                            </small>

                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>

                    <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                      <a class="dropdown-item" href="/HTML/profile/profile.php"><i class="mdi mdi-account-circle-outline font-size-16 align-middle me-1"></i> Profil</a>
                        <!-- <a class="dropdown-item" href="#"><i class="mdi mdi-wallet-outline font-size-16 align-middle me-1"></i> My Wallet</a> -->
                      <a class="dropdown-item d-block" href="/HTML/profile/profile.php"><span class="badge badge-success float-end">11</span><i class="mdi mdi-cog-outline font-size-16 align-middle me-1"></i> Pengaturan</a>
                         <!-- <a class="dropdown-item" href="#"><i class="mdi mdi-lock-open-outline font-size-16 align-middle me-1"></i> Lock screen</a> -->
                        <div class="dropdown-divider"></div>
                          <a class="dropdown-item text-danger" href="../logout.php">
                            <i class="mdi mdi-power font-size-16 align-middle me-1 text-danger"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            </header>

  <!-- ================= SIDEBAR ================= -->
  <div class="vertical-menu">
    <div data-simplebar class="h-100">

      <div class="user-sidebar text-center">
        <div class="dropdown">
          <div class="user-img">
            <img src="<?= h($photo) ?>" alt="" class="rounded-circle">
            <span class="avatar-online bg-success"></span>
          </div>
          <div class="user-info">
            <h5 class="mt-3 font-size-16 text-white"><?= h($fullName) ?></h5>
            <span class="font-size-13 text-white-50"><?= h($role) ?></span>
          </div>
        </div>
      </div>

      <div id="sidebar-menu">
        <ul class="metismenu list-unstyled" id="side-menu">
          <li class="menu-title">Menu</li>

          <li>
            <a href="../index.php" class="waves-effect">
              <i class="mdi mdi-view-dashboard-outline"></i>
              <span>Dashboard</span>
            </a>
          </li>

          <?php if (can(['admin'])): ?>
            <li>
              <a href="../users/index.php" class="waves-effect">
                <i class="dripicons-user"></i>
                <span>Pengguna</span>
              </a>
            </li>
          <?php endif; ?>

          <?php if (can(['pic','admin'])): ?>
            <li class="mm-active">
              <a href="index.php" class="waves-effect active">
                <i class="mdi mdi-desktop-classic"></i>
                <span>Data PC</span>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>

    </div>
  </div>

  <!-- ================= CONTENT ================= -->
  <div class="main-content">
    <div class="page-content">

      <!-- page title -->
      <div class="page-title-box">
        <div class="container-fluid">
          <div class="row align-items-center">

            <div class="col-md-8">
              <div class="page-title">
                <h4><?= h($pageTitle) ?></h4>
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">Meta Inventory</li>
                  <li class="breadcrumb-item"><a href="index.php">Data PC</a></li>
                  <li class="breadcrumb-item active">Edit</li>
                </ol>

                <div class="pc-meta-badges">
                  <span class="badge bg-dark">PC ID: #<?= (int)$pc['pc_id'] ?></span>
                  <span class="badge <?= h($readyBadgeClass) ?>"><?= h($readyBadgeText) ?></span>
                  <span class="badge <?= h($inetBadgeClass) ?>"><?= h($inetBadgeText) ?></span>
                </div>
              </div>
            </div>

            <div class="col-md-4 text-end">
              <a href="index.php" class="btn btn-light">
                <i class="mdi mdi-arrow-left"></i> Kembali
              </a>
            </div>

          </div>
        </div>
      </div>
      <!-- end page title -->

      <div class="container-fluid">
        <div class="page-content-wrapper">

          <div class="card card-elev">
            <div class="card-head p-3 p-md-4">
              <div class="d-flex align-items-start justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar-sm rounded-circle bg-soft-primary d-inline-flex align-items-center justify-content-center">
                    <i class="mdi mdi-desktop-classic text-primary font-size-18"></i>
                  </span>
                  <div>
                    <div class="fw-bold">Form Edit PC</div>
                    <div class="text-muted">Edit data PC dengan baik dan benar</div>
                  </div>
                </div>
                <div class="text-end">
                  <div class="kbd-hint">Tip: <b>Ctrl + S</b> untuk update</div>
                </div>
              </div>
            </div>

            <div class="card-body p-3 p-md-4">
              <form method="post" action="update.php" id="pcForm">
                <input type="hidden" name="pc_id" value="<?= (int)$pc['pc_id'] ?>">
                <input type="hidden" name="updated_by" value="<?= (int)$userId ?>">

                <div class="section-title">Identitas PC</div>
                <div class="row g-3">

                  <div class="col-md-4">
                    <label class="form-label">Kode Unik</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="mdi mdi-identifier"></i></span>
                      <input name="unique_code" class="form-control" required
                             value="<?= h($pc['unique_code']) ?>" placeholder="PC-001">
                    </div>
                  </div>

                  <div class="col-md-8">
                    <label class="form-label">Nama Unik</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="mdi mdi-tag-text-outline"></i></span>
                      <input name="unique_name" class="form-control" required
                             value="<?= h($pc['unique_name']) ?>" placeholder="PC LAB A 01">
                    </div>
                  </div>

                </div>

                <div class="section-title">Status & Lokasi</div>
                <div class="row g-3">

                  <div class="col-md-4">
                    <label class="form-label">Kondisi</label>
                    <select name="condition_id" class="form-select" required>
                      <?php foreach ($conditions as $c): ?>
                        <option value="<?= (int)$c['condition_id'] ?>"
                          <?= ((int)$pc['condition_id'] === (int)$c['condition_id']) ? 'selected' : '' ?>>
                          <?= h($c['condition_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Pengecekan Aplikasi</label>
                    <select name="check_status_id" class="form-select" required>
                      <?php foreach ($statuses as $s): ?>
                        <option value="<?= (int)$s['check_status_id'] ?>"
                          <?= ((int)$pc['check_status_id'] === (int)$s['check_status_id']) ? 'selected' : '' ?>>
                          <?= h($s['status_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Lokasi</label>
                    <select name="location_id" class="form-select" required>
                      <?php foreach ($locations as $l): ?>
                        <option value="<?= (int)$l['location_id'] ?>"
                          <?= ((int)$pc['location_id'] === (int)$l['location_id']) ? 'selected' : '' ?>>
                          <?= h($l['location_name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Siap Digunakan</label>
                    <select name="is_ready" class="form-select">
                      <option value="0" <?= !$isReady ? 'selected' : '' ?>>Tidak</option>
                      <option value="1" <?= $isReady ? 'selected' : '' ?>>Ya</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label class="form-label">Internet</label>
                    <select name="internet" class="form-select">
                      <option value="0" <?= !$hasInet ? 'selected' : '' ?>>Tidak</option>
                      <option value="1" <?= $hasInet ? 'selected' : '' ?>>Ya</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">NAMA PIC</label>
                    <div class="readonly-pill d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center gap-2">
                        <i class="mdi mdi-account-circle-outline text-primary"></i>
                        <div>
                          <div class="fw-semibold"><?= h($pic_name) ?></div>
                          <div class="text-muted" style="font-size:12px;">Otomatis dari user login</div>
                        </div>
                      </div>
                      <span class="badge bg-light text-dark">Auto</span>
                    </div>
                    <input type="hidden" name="pic_name" value="<?= h($pic_name) ?>">
                  </div>

                </div>

                <div class="section-title">Aplikasi Terpasang</div>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Aplikasi</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="mdi mdi-application"></i></span>
                      <input name="apps" class="form-control"
                             value="<?= h($appsText) ?>"
                             placeholder="contoh: Chrome, VSCode, Zoom">
                    </div>
                    <div class="form-text">Pisahkan dengan koma nama aplikasinya.</div>
                  </div>
                </div>

                <div class="section-title">Catatan & Kendala</div>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Keterangan Error</label>
                    <div class="soft-box">
                      <textarea name="error_note" class="form-control" rows="4"
                        placeholder="contoh: keyboard rusak / layar flicker"><?= h($error_note) ?></textarea>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Keterangan Internet</label>
                    <div class="soft-box">
                      <textarea name="internet_note" class="form-control" rows="4"
                        placeholder="contoh: wifi putus-putus / LAN tidak terbaca"><?= h($internet_note) ?></textarea>
                    </div>
                  </div>
                </div>

                <div class="sticky-actions mt-4">
                  <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-light">
                      <i class="mdi mdi-close"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4" id="btnSave">
                      <i class="mdi mdi-content-save"></i> Update
                    </button>
                  </div>
                </div>

              </form>
            </div>
          </div>

        </div>
      </div>

    </div>

    <footer class="footer">
      <div class="container-fluid">
        <div class="row">
          <div class="col-sm-6">
            <script>document.write(new Date().getFullYear())</script> © Meta Edutech.
          </div>
          <div class="col-sm-6">
            <div class="text-sm-end d-none d-sm-block">
              Crafted with <i class="mdi mdi-heart text-danger"></i> by Meta Edutech
            </div>
          </div>
        </div>
      </div>
    </footer>

  </div>
</div>

<div class="rightbar-overlay"></div>

<!-- JS (SAMA KAYAK TEMPLATE) -->
<script src="../assets/libs/jquery/jquery.min.js"></script>
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/metismenu/metisMenu.min.js"></script>
<script src="../assets/libs/simplebar/simplebar.min.js"></script>
<script src="../assets/libs/node-waves/waves.min.js"></script>
<script src="../assets/js/app.js"></script>

<script>
// Ctrl+S => submit
document.addEventListener('keydown', function(e){
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    if (btn) btn.click();
  }
});

// Auto-resize textarea
document.querySelectorAll('textarea').forEach(t => {
  const resize = () => { t.style.height = 'auto'; t.style.height = (t.scrollHeight + 2) + 'px'; };
  t.addEventListener('input', resize);
  resize();
});
</script>

</body>
</html>