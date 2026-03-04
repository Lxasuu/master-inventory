<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['user','pic','admin']);

$pageTitle = "Detail PC";

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/../partials/app_context.php";

// =========================
// GET ID + FETCH PC
// =========================
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header("Location: index.php");
  exit;
}

$stmt = $pdo->prepare("
  SELECT 
    p.pc_id, p.unique_code, p.unique_name, p.internet, p.is_ready,
    p.location_id, p.condition_id, p.check_status_id,
    l.location_name, c.condition_name, cs.status_name
  FROM pcs p
  LEFT JOIN locations l ON p.location_id = l.location_id
  LEFT JOIN conditions c ON p.condition_id = c.condition_id
  LEFT JOIN check_statuses cs ON p.check_status_id = cs.check_status_id
  WHERE p.pc_id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$pc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pc) {
  header("Location: index.php");
  exit;
}

// badge helper (pakai class yg sudah ada di app.custom.css: ok/bad/no/info)
$internetOk = ((int)($pc['internet'] ?? 0) === 1);
$readyOk    = ((int)($pc['is_ready'] ?? 0) === 1);

$cond = strtolower(trim($pc['condition_name'] ?? ''));
$condClass = 'info';
if (in_array($cond, ['good','baik','normal'])) $condClass = 'ok';
if (in_array($cond, ['broken','rusak'])) $condClass = 'bad';

$st = strtolower(trim($pc['status_name'] ?? ''));
$stClass = 'info';
if (in_array($st, ['checked','sudah dicek'])) $stClass = 'ok';
if (in_array($st, ['unchecked','belum dicek'])) $stClass = 'no';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../assets/images/META/meta logo.png">

  <!-- Morvin CSS -->
  <link href="../assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
  <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <link href="../assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
  <link href="../assets/css/app.custom.css" rel="stylesheet">

  <style>
    .detail-label{ font-size:12px; color:#6c757d; margin-bottom:6px; }
    .detail-value{ font-size:15px; font-weight:600; }
    .detail-box{ border:1px solid #eef0f4; border-radius:14px; padding:14px; }
  </style>
</head>

<body>
<div id="layout-wrapper">

  <header id="page-topbar">
                <div class="navbar-header">
                    <div class="d-flex">
                        
                    <!-- LOGO -->

                    <div class="navbar-brand-box d-flex align-items-center gap-2">

                    <!-- Logo -->
                    <a href="index.php" class="topbar-logo-wrap d-flex align-items-center">
                        <img src="/HTML/dist/assets/images/logo-meta copy.png"
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
                      <a class="dropdown-item" href="/HTML/dist/profile/profile.php"><i class="mdi mdi-account-circle-outline font-size-16 align-middle me-1"></i> Profil</a>
                        <!-- <a class="dropdown-item" href="#"><i class="mdi mdi-wallet-outline font-size-16 align-middle me-1"></i> My Wallet</a> -->
                      <a class="dropdown-item d-block" href="/HTML/dist/profile/profile.php"><span class="badge badge-success float-end">11</span><i class="mdi mdi-cog-outline font-size-16 align-middle me-1"></i> Pengaturan</a>
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

  <!-- SIDEBAR -->
  <div class="vertical-menu">
    <div data-simplebar class="h-100">

      <div class="user-sidebar text-center">
        <div class="dropdown">
          <div class="user-img">
            <img src="<?= htmlspecialchars($photo) ?>" alt="" class="rounded-circle">
            <span class="avatar-online bg-success"></span>
          </div>
          <div class="user-info">
            <h5 class="mt-3 font-size-16 text-white"><?= htmlspecialchars($fullName) ?></h5>
            <span class="font-size-13 text-white-50"><?= htmlspecialchars($role) ?></span>
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
            <li>
              <a href="index.php" class="waves-effect">
                <i class="mdi mdi-desktop-classic"></i>
                <span>Data PC</span>
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>

    </div>
  </div>

  <!-- CONTENT -->
  <div class="main-content">
    <div class="page-content">

      <div class="page-title-box">
        <div class="container-fluid">
          <div class="row align-items-center">
            <div class="col-md-6">
              <div class="page-title">
                <h4><?= htmlspecialchars($pageTitle) ?></h4>
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">Meta Inventory</li>
                  <li class="breadcrumb-item"><a href="index.php">Data PC</a></li>
                  <li class="breadcrumb-item active">Detail</li>
                </ol>
              </div>
            </div>

            <div class="col-md-6 text-end">
              <a href="index.php" class="btn btn-light">
                <i class="mdi mdi-arrow-left"></i> Kembali
              </a>

              <?php if (can(['pic','admin'])): ?>
                <a href="edit.php?id=<?= (int)$pc['pc_id'] ?>" class="btn btn-primary">
                  <i class="mdi mdi-pencil-outline"></i> Edit
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="container-fluid">
        <div class="page-content-wrapper">

          <div class="card pc-card">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                  <h4 class="mb-1"><?= htmlspecialchars($pc['unique_name'] ?? '-') ?></h4>
                  <div class="meta-small"><?= htmlspecialchars($pc['unique_code'] ?? '-') ?></div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Kode</div>
                    <div class="detail-value"><?= htmlspecialchars($pc['unique_code'] ?? '-') ?></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Nama</div>
                    <div class="detail-value"><?= htmlspecialchars($pc['unique_name'] ?? '-') ?></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Lokasi</div>
                    <div class="detail-value"><?= htmlspecialchars($pc['location_name'] ?? '-') ?></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Kondisi</div>
                    <div class="detail-value">
                      <span class="badge-soft <?= $condClass ?>">
                        <?= htmlspecialchars($pc['condition_name'] ?? '-') ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Status Pengecekan</div>
                    <div class="detail-value">
                      <span class="badge-soft <?= $stClass ?>">
                        <?= htmlspecialchars($pc['status_name'] ?? '-') ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Internet</div>
                    <div class="detail-value">
                      <span class="badge-soft <?= $internetOk ? 'ok' : 'no' ?>">
                        <?= $internetOk ? 'Yes' : 'No' ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="detail-box">
                    <div class="detail-label">Readiness</div>
                    <div class="detail-value">
                      <span class="badge-soft <?= $readyOk ? 'ok' : 'bad' ?>">
                        <?= $readyOk ? 'Ready' : 'Not Ready' ?>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="col-md-8">
                  <div class="detail-box">
                    <div class="detail-label">Info</div>
                    <div class="text-muted" style="font-size:13px;">
                      Halaman ini hanya untuk melihat detail data PC tanpa edit.
                    </div>
                  </div>
                </div>

              </div>

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

<script src="../assets/libs/jquery/jquery.min.js"></script>
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/metismenu/metisMenu.min.js"></script>
<script src="../assets/libs/simplebar/simplebar.min.js"></script>
<script src="../assets/libs/node-waves/waves.min.js"></script>
<script src="../assets/js/app.js"></script>
</body>
</html>