<?php
require_once __DIR__ . "/partials/bootstrap.php";
require_role(['user','pic','admin']);

$pageTitle = "Dashboard";

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/partials/app_context.php";


    $sql = "
    SELECT 
    p.pc_id,
    p.unique_code,
    p.unique_name,
    p.internet,
    p.is_ready,
    l.location_name,
    c.condition_name,
    cs.status_name
    FROM pcs p
    LEFT JOIN locations l ON p.location_id = l.location_id
    LEFT JOIN conditions c ON p.condition_id = c.condition_id
    LEFT JOIN check_statuses cs ON p.check_status_id = cs.check_status_id
    ORDER BY p.pc_id DESC
    ";

    $pcs = $pdo->query($sql)->fetchAll();
    $recentPcs = $pdo->query("
    SELECT 
        p.pc_id, p.unique_code, p.unique_name,
        l.location_name,
        c.condition_name,
        cs.status_name,
        p.is_ready,
        p.internet,
        p.updated_at
    FROM pcs p
    LEFT JOIN locations l ON p.location_id = l.location_id
    LEFT JOIN conditions c ON p.condition_id = c.condition_id
    LEFT JOIN check_statuses cs ON p.check_status_id = cs.check_status_id
    ORDER BY p.updated_at DESC, p.pc_id DESC
    LIMIT 5
    ")->fetchAll();

    $readyPcs = $pdo->query("
    SELECT 
        p.pc_id, p.unique_code, p.unique_name, p.internet,
        l.location_name,
        c.condition_name,
        cs.status_name
    FROM pcs p
    LEFT JOIN locations l ON p.location_id = l.location_id
    LEFT JOIN conditions c ON p.condition_id = c.condition_id
    LEFT JOIN check_statuses cs ON p.check_status_id = cs.check_status_id
    WHERE p.is_ready = 1
    ORDER BY p.updated_at DESC, p.pc_id DESC
    LIMIT 5
    ")->fetchAll();

    $totalPcRusak = (int)$pdo->query("
    SELECT COUNT(*)
    FROM pcs p
    JOIN conditions c ON c.condition_id = p.condition_id
    WHERE LOWER(c.condition_name) IN ('broken','rusak')
    ")->fetchColumn();

    // Total PC
    $totalPc = (int)$pdo->query("SELECT COUNT(*) FROM pcs")->fetchColumn();

    // Jumlah PC per lokasi (Lokasi PC)
    $stmtMap = $pdo->query("
    SELECT 
        l.location_name,
        l.latitude,
        l.longitude,
        COUNT(p.pc_id) AS total_pc
    FROM locations l
    LEFT JOIN pcs p ON p.location_id = l.location_id
    WHERE l.latitude IS NOT NULL
        AND l.longitude IS NOT NULL
    GROUP BY l.location_id
    ");

    $pcByLocation = $pdo->query("
    SELECT 
        l.location_name,
        COUNT(p.pc_id) AS total
    FROM locations l
    LEFT JOIN pcs p ON p.location_id = l.location_id
    GROUP BY l.location_id
    ORDER BY total DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // DASHBOARD:
    // =========================
    $year = (int)date('Y');

    function months12zero() {
        return array_fill(1, 12, 0);
    }

    $stmt = $pdo->prepare("
        SELECT MONTH(created_at) AS m, COUNT(*) AS total
        FROM pcs
        WHERE YEAR(created_at) = ?
        GROUP BY MONTH(created_at)
        ORDER BY m
    ");
    $stmt->execute([$year]);
    $newMap = months12zero();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $newMap[(int)$r['m']] = (int)$r['total'];
    }

    $stmt = $pdo->prepare("
        SELECT MONTH(created_at) AS m, COUNT(*) AS total
        FROM pcs
        WHERE YEAR(created_at) = ? AND is_ready = 1
        GROUP BY MONTH(created_at)
        ORDER BY m
    ");
    $stmt->execute([$year]);
    $readyMap = months12zero();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $readyMap[(int)$r['m']] = (int)$r['total'];
    }

    $stmt = $pdo->prepare("
        SELECT MONTH(p.created_at) AS m, COUNT(*) AS total
        FROM pcs p
        JOIN conditions c ON c.condition_id = p.condition_id
        WHERE YEAR(p.created_at) = ?
        AND LOWER(c.condition_name) IN ('broken','rusak')
        GROUP BY MONTH(p.created_at)
        ORDER BY m
    ");
    $stmt->execute([$year]);
    $brokenMap = months12zero();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $brokenMap[(int)$r['m']] = (int)$r['total'];
    }

    $totalReady = (int)$pdo->query("SELECT COUNT(*) FROM pcs WHERE is_ready = 1")->fetchColumn();
    $totalNoInternet = (int)$pdo->query("SELECT COUNT(*) FROM pcs WHERE internet = 0")->fetchColumn();

    // =========================
    // STATUS OVERVIEW (Donut)
    // =========================

    // Ready vs Not Ready
    $readyCount = (int)$pdo->query("SELECT COUNT(*) FROM pcs WHERE is_ready = 1")->fetchColumn();
    $notReadyCount = (int)$pdo->query("SELECT COUNT(*) FROM pcs WHERE is_ready = 0")->fetchColumn();

    // Internet OK vs No Internet
    $internetOkCount = (int)$pdo->query("SELECT COUNT(*) FROM pcs WHERE internet = 1")->fetchColumn();
    $noInternetCount = (int)$pdo->query("SELECT COUNT(*) FROM pcs WHERE internet = 0")->fetchColumn();

    // Broken vs Non Broken (berdasarkan conditions)
    $brokenCount = (int)$pdo->query("
    SELECT COUNT(*)
    FROM pcs p
    JOIN conditions c ON c.condition_id = p.condition_id
    WHERE LOWER(c.condition_name) IN ('broken','rusak')
    ")->fetchColumn();

    $nonBrokenCount = $totalPc - $brokenCount;
    if ($nonBrokenCount < 0) $nonBrokenCount = 0;


    $chartNew    = array_values($newMap);
    $chartReady  = array_values($readyMap);
    $chartBroken = array_values($brokenMap);

    // =========================
    // NOTIFICATIONS (activity_logs)
    // =========================

    // unread count 
// =========================
// NOTIFICATIONS DIHANDLE OLEH partials/topbar.php
// =========================


    ?>

    <!doctype html>
    <html lang="en">

    <head>

        <meta charset="utf-8" />
        <title>Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/politeknik-meta favicon.png">

        <!-- plugin css -->
        <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

        <!-- Bootstrap Css -->
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
        <link href="assets/css/app.custom.css" id="app-style" rel="stylesheet" type="text/css" />

    </head>

    
    <body>

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include __DIR__ . '/partials/topbar.php'; ?>

            <!-- ========== Left Sidebar Start ========== -->
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

            <!--- Sidemenu -->
            <div id="sidebar-menu">
            <!-- Left Menu Start -->
                <?php
            // auto active class
                    $uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
                    function isActive($needle, $uri){
                    return strpos($uri, strtolower($needle)) !== false;
                    }
                ?>

                <ul class="metismenu list-unstyled" id="side-menu">
                    <li class="menu-title">MENU</li>

                    <li>
                        <a href="index.php" class="waves-effect <?= isActive('/index.php', $uri) ? 'active' : '' ?>">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                <?php if (can(['admin'])): ?>
                    <li>
                        <a href="users/index.php" class="waves-effect <?= (isActive('/users/', $uri) || isActive('/users', $uri)) ? 'active' : '' ?>">
                            <i class="dripicons-user"></i>
                            <span>Pengguna</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (can(['pic','admin'])): ?>
                    <li>
                        <a href="pcs/index.php" class="waves-effect <?= (isActive('/pcs/', $uri) || isActive('/pcs', $uri)) ? 'active' : '' ?>">
                            <i class="mdi mdi-desktop-classic"></i>
                            <span>Data PC</span>
                        </a>
                    </li>
                </ul>
                <?php endif; ?> 

                
             </div>

            <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
                
            <div class="page-content">

            <!-- start page title -->
                <div class="page-title-box">
                    <div class="container-fluid">
                        <div class="row align-items-center">

                        <!-- kiri: judul -->
                            <div class="col-md-6">
                                <div class="page-title">
                                    <h4>Dashboard</h4>
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item">Meta Inventory</li>
                                             <li class="breadcrumb-item active">Dashboard</li>
                                        </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- end page title -->    

        <div class="container-fluid">
            <div class="page-content-wrapper">
                <div class="row g-3">
        <!-- Quick Summary -->
        <?php
            $pctReady = $totalPc ? round(($totalReady / $totalPc) * 100, 1) : 0;
            $pctRusak = $totalPc ? round(($totalPcRusak / $totalPc) * 100, 1) : 0;
        ?>

            <div class="col-xl-8">
                <div class="card qs-card h-100">
                <div class="qs-head">
                    <div>
                        <h4 class="qs-title">Ringkasan Singkat</h4>
                            <div class="qs-sub"></div>

                                <div class="qs-chips">
                                    <span class="qs-chip"><i class="mdi mdi-calendar"></i> Tahun: <?= (int)$year ?></span>
                                    <span class="qs-chip"><i class="mdi mdi-update"></i> Update: <?= date('Y-m-d H:i') ?></span>
                                </div>
                            </div>

                            <ul class="nav qs-tabs">
                                <li class="nav-item"><a class="nav-link" href="#" data-range="day">Harian</a></li>
                                <li class="nav-item"><a class="nav-link" href="#" data-range="week">Mingguan</a></li>
                                <li class="nav-item"><a class="nav-link" href="#" data-range="month">Bulanan</a></li>
                                <li class="nav-item"><a class="nav-link active" href="#" data-range="year">Tahunan</a></li>
                            </ul>
                        </div>

                        <div class="qs-body">
                            <div class="qs-grid">

                            <!-- Chart -->
                            <div class="qs-chart">
                                <div class="qs-chart-inner">
                                    <div id="stacked-column-chart" class="apex-charts" dir="ltr" style="height: 320px;"></div>
                                </div>
                            </div>

                            <!-- KPI kanan (dalam card Quick Summary) -->
                            <div class="qs-kpis">
                                <div class="qs-kpi">
                                    <div class="qs-kpi-left">
                                        <div class="qs-ico"><i class="mdi mdi-desktop-classic"></i></div>
                                            <div>
                                                <p class="qs-kpi-label">Total PC</p>
                                                <p class="qs-kpi-value"><?= (int)$totalPc ?></p>
                                            </div>
                                        </div>

                                        <div class="qs-kpi-right">
                                            <div class="qs-kpi-note">Seluruh waktu</div>
                                            <div class="qs-progress"><div style="width:100%"></div></div>
                                        </div>
                                    </div>

                                    <div class="qs-kpi success">
                                        <div class="qs-kpi-left">
                                            <div class="qs-ico"><i class="mdi mdi-check-circle-outline"></i></div>
                                                <div>
                                                    <p class="qs-kpi-label">Kondisi</p>
                                                    <p class="qs-kpi-value"><?= (int)$totalReady ?></p>
                                                </div>
                                            </div>
                                        <div class="qs-kpi-right">
                                        <div class="qs-kpi-note"><?= $pctReady ?>% siap digunakan</div>
                                        <div class="qs-progress"><div style="width: <?= $pctReady ?>%"></div></div>
                                        </div>
                                    </div>

                                    <div class="qs-kpi warning">
                                        <div class="qs-kpi-left">
                                            <div class="qs-ico"><i class="mdi mdi-alert-outline"></i></div>
                                                <div>
                                                    <p class="qs-kpi-label">PC Rusak</p>
                                                    <p class="qs-kpi-value"><?= (int)$totalPcRusak ?></p>
                                                </div>
                                            </div>

                                        <div class="qs-kpi-right">
                                            <div class="qs-kpi-note"><?= $pctRusak ?>% rusak</div>
                                                <div class="qs-progress"><div style="width: <?= $pctRusak ?>%"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


        <!-- Total PC cards -->
        <?php
            $pctRusak = $totalPc ? round(($totalPcRusak / $totalPc) * 100, 1) : 0;
            $pctReady = $totalPc ? round(($totalReady   / $totalPc) * 100, 1) : 0;

        // buat progress enak dilihat (min 6% biar ga "hilang" kalau nilainya kecil)
            $barRusak = max(6, min(100, $pctRusak));
            $barTotal = 100;
        ?>

        <div class="col-xl-4">
            <div class="row g-3">

                <!-- Total PC -->
                <div class="col-12 col-md-6 col-xl-6">
                    <div class="kpi-card h-100">
                        <div class="kpi-head">
                            <h6 class="kpi-title">Total PC</h6>
                                <span class="kpi-chip">Total PC</span>
                        </div>

                <div class="kpi-body">
                    <div class="kpi-ico"><i class="mdi mdi-desktop-classic"></i></div>
                        <p class="kpi-value"><?= (int)$totalPc ?></p>
                    <div class="kpi-sub">Total perangkat terdaftar</div>
                    <div class="kpi-progress"><div style="width: <?= $barTotal ?>%"></div>
                </div>
            </div>
      </div>
    </div>

    <!-- Total PC Rusak -->
    <div class="col-12 col-md-6 col-xl-6">
      <div class="kpi-card danger h-100">

        <div class="kpi-head">
          <h6 class="kpi-title">Total PC Rusak</h6>
          <span class="kpi-chip"><?= $pctRusak ?>%</span>
        </div>
        
        <div class="kpi-body">
          <div class="kpi-ico"><i class="mdi mdi-alert-outline"></i></div>
            <p class="kpi-value"><?= (int)$totalPcRusak ?></p>
          <div class="kpi-sub"><?= $pctRusak ?>% dari total (<?= (int)$totalPc ?>)</div>
          <div class="kpi-progress"><div style="width: <?= $barRusak ?>%"></div></div>
        </div>

      </div>
    </div>

    <div class="col-12">

      <div class="kpi-card success">
        <div class="kpi-head">
          <h6 class="kpi-title">PC siap digunakan</h6>
          <span class="kpi-chip"><?= $pctReady ?>%</span>
            </div>

                <div class="kpi-body" style="text-align:left">
                    <div class="d-flex align-items-center justify-content-between">

                        <div class="d-flex align-items-center gap-3">
                            <div class="kpi-ico" style="margin:0"><i class="mdi mdi-check-circle-outline"></i></div>
                        <div>
                            <p class="kpi-value" style="margin:0"><?= (int)$totalReady ?></p>
                        <div class="kpi-sub">Siap digunakan</div>
                    </div>
                    </div>

                    <div class="text-end">
                    <div class="kpi-sub">Internet off: <b><?= (int)$totalNoInternet ?></b></div>
                    </div>
                </div>

                    <div class="kpi-progress mt-3"><div style="width: <?= max(6, min(100, $pctReady)) ?>%"></div></div>
                </div>
                </div>
            </div>
        </div>
    </div>


    <div class="section-block">

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">PC Terbaru</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h4 class="mb-1">PC Terbaru</h4>
                                    <div class="qs-sub">Update terbaru dari perangkat yang baru ditambahkan/diubah.</div>
                                </div>
                                <a href="pcs/index.php" class="btn-soft">
                                 <i class="mdi mdi-view-list-outline"></i> Lihat semua
                                </a>
                            </div>

                        <?php if (!$recentPcs): ?>
                            <div class="text-muted">Belum ada data PC.</div>
                        <?php else: ?>
                            <div class="recent-wrap">
                            <?php foreach ($recentPcs as $pc): ?>
                            <?php
                                $badgeReady = $pc['is_ready'] ? 'success' : 'gray';
                                $textReady  = $pc['is_ready'] ? 'Ready' : 'Not Ready';

                                $badgeInet = $pc['internet'] ? 'primary' : 'warning';
                                $textInet  = $pc['internet'] ? 'Internet OK' : 'No Internet';
                            ?>

                            <div class="recent-item">
                                <div class="recent-top">

                                <div class="recent-ico">
                                    <i class="mdi mdi-desktop-classic font-size-20"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <p class="recent-title">
                                    <?= htmlspecialchars($pc['unique_name']) ?>
                                    <span class="text-muted fw-normal"> (<?= htmlspecialchars($pc['unique_code']) ?>)</span>
                                    </p>

                                    <div class="recent-sub">
                                    Lokasi: <b><?= htmlspecialchars($pc['location_name'] ?? '-') ?></b> •
                                    Kondisi: <b><?= htmlspecialchars($pc['condition_name'] ?? '-') ?></b> •
                                    Status: <b><?= htmlspecialchars($pc['status_name'] ?? '-') ?></b>
                                    </div>

                                    <div class="recent-meta">
                                    <span class="pill <?= $badgeReady ?>">
                                        <i class="mdi <?= $pc['is_ready'] ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' ?>"></i>
                                        <?= $textReady ?>
                                    </span>

                                    <span class="pill <?= $badgeInet ?>">
                                        <i class="mdi <?= $pc['internet'] ? 'mdi-wifi' : 'mdi-wifi-off' ?>"></i>
                                        <?= $textInet ?>
                                    </span>

                                    <span class="pill gray">
                                        <i class="mdi mdi-update"></i>
                                        <?= htmlspecialchars($pc['updated_at'] ?? '-') ?>
                                    </span>
                                    </div>
                                </div>

                                <div class="recent-actions">
                                    <a class="btn-soft" href="pcs/edit.php?id=<?= (int)$pc['pc_id'] ?>">
                                    <i class="mdi mdi-pencil-outline"></i> Detail
                                    </a>
                                </div>

                                </div>
                            </div>

                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- main content -->
    
    <div class="card">
        <div class="card pc-ready-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="header-title mb-0">PC Siap Digunakan</h4>

      <!-- counter -->
      <div class="pc-ready-counter text-muted small">
        <span id="pcReadyCurrent">1</span>/<span id="pcReadyTotal"><?= max(1, count($readyPcs)) ?></span>
      </div>
    </div>

    <?php if (!$readyPcs): ?>
      <div class="text-muted">Belum ada PC yang siap digunakan.</div>
    <?php else: ?>

      <div id="pcReadyCarousel" class="carousel slide pc-ready-carousel"
           data-bs-ride="carousel"
           data-bs-interval="4000"
           data-bs-pause="false">
        
        <div class="carousel-inner">

          <?php foreach ($readyPcs as $i => $pc): ?>
            <?php
              $loc  = $pc['location_name'] ?? '-';
              $cond = $pc['condition_name'] ?? '-';
              $stat = $pc['status_name'] ?? '-';
              $inet = ((int)$pc['internet'] === 1) ? 'Internet OK' : 'No Internet';
              $inetBadge = ((int)$pc['internet'] === 1) ? 'bg-success' : 'bg-warning';
            ?>

            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
              <div class="pc-ready-slide">

                <!-- left icon bubble -->
                <div class="pc-ready-left">
                  <div class="pc-ready-bubble">
                    <i class="mdi mdi-desktop-classic"></i>
                  </div>
                </div>

                <!-- right info -->
                <div class="pc-ready-right">
                  <div class="pc-ready-loc"><?= htmlspecialchars($loc) ?></div>

                  <div class="pc-ready-name">
                    <?= htmlspecialchars($pc['unique_name']) ?>
                  </div>

                  <div class="pc-ready-code">
                    <?= htmlspecialchars($pc['unique_code']) ?>
                  </div>

                  <div class="pc-ready-badges">
                    <span class="badge bg-primary"><?= htmlspecialchars($cond) ?></span>
                    <span class="badge bg-info"><?= htmlspecialchars($stat) ?></span>
                    <span class="badge <?= $inetBadge ?>"><?= $inet ?></span>
                    <span class="badge bg-success">Ready</span>
                  </div>

                  <div class="pc-ready-actions">
                    <a href="pcs/edit.php?id=<?= (int)$pc['pc_id'] ?>" class="btn btn-primary btn-sm">
                      <i class="mdi mdi-pencil-outline me-1"></i> Detail / Edit
                    </a>
                  </div>
                </div>

              </div>
            </div>
          <?php endforeach; ?>

        </div>

                    <button class="carousel-control-prev pc-ready-nav" type="button"
                            data-bs-target="#pcReadyCarousel" data-bs-slide="prev">
                    <span class="pc-ready-nav-btn" aria-hidden="true">
                        <i class="mdi mdi-chevron-left"></i>
                    </span>
                    <span class="visually-hidden">Sebelumnya</span>
                    </button>

                    <button class="carousel-control-next pc-ready-nav" type="button"
                            data-bs-target="#pcReadyCarousel" data-bs-slide="next">
                    <span class="pc-ready-nav-btn" aria-hidden="true">
                        <i class="mdi mdi-chevron-right"></i>
                    </span>
                    <span class="visually-hidden">Selanjutnya</span>
                    </button>

                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="pc-loc-wrap">

                <!-- LEFT: MAP -->
                    <div class="pc-map-shell">
                        <div class="pc-map-titlebar">
                            <div><h4 class="header-title mb-1">Lokasi PC</h4></div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pc-floor-tabs" id="pcFloorTabs">
                                    <button type="button" class="pc-floor-btn active" data-floor="2">Lantai 2</button>
                                    <button type="button" class="pc-floor-btn" data-floor="3">Lantai 3</button>
                                </div>
                                    <span class="pc-floor-chip" id="pcFloorInfo">Menampilkan: Lantai 2</span>
                            </div>
                        </div>

                        <div class="position-relative">
                            <canvas id="pcMapCanvas" class="pc-map-canvas"></canvas>
                        <div id="pcMapTooltip" class="pc-map-tooltip"></div>
                    </div>
                </div>

                <!-- RIGHT: LIST -->
                <div class="pc-loc-panel">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="mb-0">Distribusi Lokasi</h5>
                        <span class="text-muted small">Top lokasi</span>
                    </div>

                <?php if (empty($pcByLocation)): ?>
                    <div class="text-muted">Belum ada data lokasi.</div>
                    <?php else: ?>
                <?php foreach ($pcByLocation as $row): ?>

                <?php
                $count = (int)$row['total'];
                $pct = ($totalPc > 0) ? round(($count / $totalPc) * 100) : 0;
                ?>

                <div class="pc-loc-row">
                    <div class="pc-loc-head">
                        <div class="pc-loc-name"><?= htmlspecialchars($row['location_name']) ?></div>
                        <div class="pc-loc-meta"><?= $pct ?>% • <?= $count ?> PC</div>
                    </div>

                    <div class="pc-loc-bar">
                        <div style="width: <?= $pct ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                 <?php endif; ?>
                </div>

                </div>

                </div>
                </div>

                </div>
                </div>

                <div class="row">

                <!-- KIRI: Status Overview -->
                    <div class="col-xl-4">
                        <div class="card h-100">
                            <div class="card-body">

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="header-title mb-0">Status Keseluruhan</h4>
                                    <span class="text-muted small"><?= (int)$year ?></span>
                                    </div>

                                    <div class="ov-wrap">
                                    <?php
                                        $pctReady = $totalPc ? round(($readyCount/$totalPc)*100, 1) : 0;
                                        $pctInet  = $totalPc ? round(($internetOkCount/$totalPc)*100, 1) : 0;
                                        $pctBroken = $totalPc ? round(($brokenCount/$totalPc)*100, 1) : 0;
                                    ?>

                                    <!-- Ready -->
                                    <div class="ov-card is-green">
                                        <div class="ov-head">
                                        <div>
                                            <div class="ov-title">Siap digunakan</div>
                                            <div class="ov-sub">Siap digunakan vs tidak</div>
                                        </div>
                                        <span class="ov-chip"><?= $pctReady ?>%</span>
                                        </div>

                                        <div class="ov-body">
                                        <div class="ov-metric">
                                            <div class="ov-icon"><i class="mdi mdi-check-circle-outline font-size-20"></i></div>
                                            <div>
                                            <div class="ov-value"><?= $readyCount ?><small>/<?= $totalPc ?></small></div>
                                            <div class="ov-note">Unit siap digunakan</div>
                                            </div>
                                        </div>

                                        <div class="ov-right">
                                            <div class="ov-pct"><?= $pctReady ?>%</div>
                                            <div class="ov-note"> </div>
                                        </div>
                                        </div>

                                        <div class="ov-bar"><span style="width: <?= $pctReady ?>%"></span></div>
                                    </div>

                                    <!-- Internet -->
                                    <div class="ov-card">
                                        <div class="ov-head">
                                        <div>
                                            <div class="ov-title">Internet</div>
                                            <div class="ov-sub">Koneksi OK vs tidak</div>
                                        </div>
                                        <span class="ov-chip"><?= $pctInet ?>%</span>
                                        </div>

                                        <div class="ov-body">
                                        <div class="ov-metric">
                                            <div class="ov-icon"><i class="mdi mdi-wifi font-size-20"></i></div>
                                            <div>
                                            <div class="ov-value"><?= $internetOkCount ?><small>/<?= $totalPc ?></small></div>
                                            <div class="ov-note">Internet OK</div>
                                            </div>
                                        </div>

                                        <div class="ov-right">
                                            <div class="ov-pct"><?= $pctInet ?>%</div>
                                            <div class="ov-note"></div>
                                        </div>
                                        </div>

                                        <div class="ov-bar"><span style="width: <?= $pctInet ?>%"></span></div>
                                    </div>

                                    <!-- Condition -->
                                    <div class="ov-card is-amber">
                                        <div class="ov-head">
                                        <div>
                                            <div class="ov-title">Kondisi</div>
                                            <div class="ov-sub">Rusak vs lainnya</div>
                                        </div>
                                        <span class="ov-chip"><?= $pctBroken ?>%</span>
                                        </div>

                                        <div class="ov-body">
                                        <div class="ov-metric">
                                            <div class="ov-icon"><i class="mdi mdi-alert-outline font-size-20"></i></div>
                                            <div>
                                            <div class="ov-value"><?= $brokenCount ?><small>/<?= $totalPc ?></small></div>
                                            <div class="ov-note">Unit rusak</div>
                                            </div>
                                        </div>

                                        <div class="ov-right">
                                            <div class="ov-pct"><?= $pctBroken ?>%</div>
                                            <div class="ov-note"></div>
                                        </div>
                                        </div>

                                        <div class="ov-bar"><span style="width: <?= $pctBroken ?>%"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>



                               <div class="col-xl-8">
                                    <div class="card pc-card">
                                        <div class="card-body">

                                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                            <div>
                                            <h4 class="header-title mb-1">Data PC</h4>
                                            <div class="text-muted small">Daftar perangkat terdaftar</div>
                                            </div>

                                            <a href="pcs/create.php" class="btn-premium">
                                            <span class="btn-ic"><i class="mdi mdi-plus font-size-18"></i></span>
                                            <span>Tambahkan PC</span>
                                            </a>
                                        </div>

                                        <!-- Controls -->
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted">Tampilkan</span>
                                            <select id="pcPageSize" class="form-select form-select-sm" style="width:110px;">
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                            <span class="text-muted">entri</span>
                                            </div>

                                            <div class="text-muted small" id="pcPageInfo">Menampilkan 0–0 dari 0</div>

                                            <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-light btn-sm" id="pcPrevBtn" type="button" aria-label="Prev">
                                                <i class="mdi mdi-chevron-left"></i>
                                            </button>
                                            <div id="pcPagination" class="d-flex gap-1"></div>
                                            <button class="btn btn-light btn-sm" id="pcNextBtn" type="button" aria-label="Next">
                                                <i class="mdi mdi-chevron-right"></i>
                                            </button>
                                            </div>
                                        </div>

                                        <!-- Table -->
                                        <div class="table-responsive pc-table-wrap">
                                            <table id="pcTable" class="table table-centered table-nowrap mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                <th>ID</th>
                                                <th>Kode</th>
                                                <th>Nama</th>
                                                <th>Lokasi</th>
                                                <th>Kondisi</th>
                                                <th>Status</th>
                                                <th>Internet</th>
                                                <th>Siap digunakan</th>
                                                </tr>
                                            </thead>

                                            <tbody id="pcTbody">
                                                <?php if (!$pcs): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">Belum ada data PC</td>
                                                </tr>
                                                <?php else: ?>
                                                <?php foreach ($pcs as $pc): ?>
                                                    <tr>
                                                    <td><?= (int)$pc['pc_id'] ?></td>
                                                    <td><?= htmlspecialchars($pc['unique_code']) ?></td>
                                                    <td><?= htmlspecialchars($pc['unique_name']) ?></td>
                                                    <td><?= htmlspecialchars($pc['location_name'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($pc['condition_name'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($pc['status_name'] ?? '-') ?></td>
                                                    <td><?= $pc['internet'] ? 'Yes' : 'No' ?></td>
                                                    <td><?= $pc['is_ready'] ? 'Ready' : 'Not Ready' ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

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
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <!-- Right Sidebar -->
        <div class="right-bar">
            <div data-simplebar class="h-100">
                <div class="rightbar-title d-flex align-items-center px-3 py-4">
                
                    <h5 class="m-0 me-2">Settings</h5>

                    <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                        <i class="mdi mdi-close noti-icon"></i>
                    </a>
                </div>

                <!-- Settings -->
                <hr class="mt-0" />
                <h6 class="text-center mb-0">Choose Layouts</h6>

                <div class="p-4">
                    <div class="mb-2">
                        <img src="assets/images/layouts/layout-1.jpg" class="img-fluid img-thumbnail" alt="layout-1">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                        <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                    </div>
        
                    <div class="mb-2">
                        <img src="assets/images/layouts/layout-2.jpg" class="img-fluid img-thumbnail" alt="layout-2">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch" data-bsStyle="assets/css/bootstrap-dark.min.css" data-appStyle="assets/css/app-dark.min.css">
                        <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                    </div>
        
                    <div class="mb-2">
                        <img src="assets/images/layouts/layout-3.jpg" class="img-fluid img-thumbnail" alt="layout-3">
                    </div>
                    <div class="form-check form-switch mb-5">
                        <input class="form-check-input theme-choice" type="checkbox" id="rtl-mode-switch" data-appStyle="assets/css/app-rtl.min.css">
                        <label class="form-check-label" for="rtl-mode-switch">RTL Mode</label>
                    </div>

                
                </div>

            </div> <!-- end slimscroll-menu-->
        </div>
        <!-- /Right-bar -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".js-read-all-notif").forEach(function (btn) {
                btn.addEventListener("click", async function (e) {
                e.preventDefault();

                try {
                    const res = await fetch("notification/read_all_ajax.php", {
                    method: "POST",
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                    });

                    const data = await res.json();

                    if (!data.ok) {
                    console.error(data);
                    return;
                    }

                    const bellBtn = document.getElementById("page-header-notifications-dropdown");
                    if (bellBtn) {
                    const badge = bellBtn.querySelector(".badge.bg-danger.rounded-pill");
                    if (badge) badge.remove();
                    }

                    document.querySelectorAll(".dropdown-menu .notification-item").forEach(function (item) {
                    item.classList.remove("bg-light"); // kamu pakai bg-light utk unread
                    const newBadge = item.querySelector(".badge.bg-danger.mt-1");
                    if (newBadge) newBadge.remove();
                    });

                } catch (err) {
                    console.error(err);
                }
                });
            });
            });
        </script>

        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            const el = document.querySelector("#stacked-column-chart");
            if (!el) return;

            const seriesData = window.DASH_SERIES || { newPc: [], readyPc: [], brokenPc: [] };

            const options = {
                chart: {
                type: "bar",
                height: 320,
                stacked: true,
                toolbar: { show: false }
                },
                plotOptions: {
                bar: { columnWidth: "45%", borderRadius: 6 }
                },
                dataLabels: { enabled: false },
                stroke: { width: 1 },
                series: [
                { name: "PC Baru",   data: seriesData.newPc || [] },
                { name: "PC Ready",  data: seriesData.readyPc || [] },
                { name: "PC Rusak",  data: seriesData.brokenPc || [] }
                ],
                xaxis: {
                categories: ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
                },
                yaxis: { labels: { formatter: (v) => Math.round(v) } },
                legend: { position: "top", horizontalAlign: "left" },
                fill: { opacity: 1 },
                grid: { borderColor: "#f1f1f1" }
            };

            if (window.__dashChart) {
                try { window.__dashChart.destroy(); } catch(e) {}
            }

            window.__dashChart = new ApexCharts(el, options);
            window.__dashChart.render();
            });
        </script>
        
        <script>
        window.DASH_YEAR = <?= (int)$year ?>;
        window.DASH_SERIES = {
            newPc: <?= json_encode($chartNew) ?>,
            readyPc: <?= json_encode($chartReady) ?>,
            brokenPc: <?= json_encode($chartBroken) ?>
        };
        </script>

        <script>
        window.DONUT_DATA = {
        ready: [<?= (int)$readyCount ?>, <?= (int)$notReadyCount ?>],
        internet: [<?= (int)$internetOkCount ?>, <?= (int)$noInternetCount ?>],
        broken: [<?= (int)$brokenCount ?>, <?= (int)$nonBrokenCount ?>]
        };
        </script>

        <script src="assets/js/app.js"></script>

        <script>
            window.mapLocations = <?= json_encode($pcByLocation, JSON_UNESCAPED_UNICODE) ?>;

            document.addEventListener("DOMContentLoaded", function () {
            const canvas = document.getElementById("pcMapCanvas");
            if (!canvas) return;
            const ctx = canvas.getContext("2d");
            const tooltip = document.getElementById("pcMapTooltip");
            const tabs = document.getElementById("pcFloorTabs");
            const info = document.getElementById("pcFloorInfo");

            const FLOOR_LAYOUT = {
                2: {
                title: "Lantai 2",
                nodes: {
                    "Komlab 1": { rx: 0.72, ry: 0.70 },
                    "Komlab 2": { rx: 0.48, ry: 0.70 },
                }
                },
                3: {
                title: "Lantai 3",
                nodes: {
                    "Komlab l3": { rx: 0.58, ry: 0.40 },
                }
                }
            };

            let activeFloor = 2;

            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                const dpr = window.devicePixelRatio || 1;
                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            }
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            const data = Array.isArray(window.mapLocations) ? window.mapLocations : [];

            function hash01(str) {
                let h = 2166136261;
                for (let i = 0; i < str.length; i++) {
                h ^= str.charCodeAt(i);
                h += (h << 1) + (h << 4) + (h << 7) + (h << 8) + (h << 24);
                }
                return (h >>> 0) / 4294967295;
            }

            function buildNodesForFloor(floor){
                const layoutNodes = FLOOR_LAYOUT[floor]?.nodes || {};
                // filter hanya lokasi yang ada di layout lantai tsb
                const names = Object.keys(layoutNodes);

                const nodes = names.map((name, i) => {
                // cari total dari data
                const row = data.find(r => String(r.location_name).trim().toLowerCase() === name.toLowerCase());
                const total = row ? (parseInt(row.total ?? 0, 10) || 0) : 0;

                const p = layoutNodes[name];
                const rx = p?.rx ?? hash01(name + "_x");
                const ry = p?.ry ?? hash01(name + "_y");

                return { name, total, rx, ry, phase: Math.random() * Math.PI * 2, x:0, y:0, r:0 };
                });

                return nodes;
            }

            let nodes = buildNodesForFloor(activeFloor);
            let hoverNode = null;

            function draw(t) {
                const w = canvas.getBoundingClientRect().width;
                const h = canvas.getBoundingClientRect().height;

                ctx.clearRect(0, 0, w, h);

                // soft grid
                ctx.globalAlpha = 0.10;
                ctx.strokeStyle = "#3b5de7";
                for (let x = 0; x < w; x += 48) {
                ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, h); ctx.stroke();
                }
                for (let y = 0; y < h; y += 48) {
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(w, y); ctx.stroke();
                }
                ctx.globalAlpha = 1;

                // label lantai aktif
                ctx.globalAlpha = 0.65;
                ctx.fillStyle = "#6c757d";
                ctx.font = "12px Arial";
                ctx.fillText(FLOOR_LAYOUT[activeFloor]?.title || `Lantai ${activeFloor}`, 16, 22);
                ctx.globalAlpha = 1;

                const pad = 26;
                const toXY = (n) => ({
                x: pad + n.rx * (w - pad * 2),
                y: pad + n.ry * (h - pad * 2),
                });

                // nodes
                nodes.forEach(n => {
                const p = toXY(n);
                n.x = p.x; n.y = p.y;

                const baseR = 10 + Math.min(n.total, 20) * 1.3;
                const pulse = (Math.sin(t / 650 + n.phase) + 1) / 2;
                const glowR = baseR + pulse * 8;
                n.r = baseR;

                // glow
                ctx.globalAlpha = 0.18;
                ctx.beginPath();
                ctx.arc(p.x, p.y, glowR, 0, Math.PI * 2);
                ctx.fillStyle = "#3b5de7";
                ctx.fill();

                // core
                ctx.globalAlpha = 1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, baseR, 0, Math.PI * 2);
                ctx.closePath();
                // fix arc properly
                ctx.beginPath();
                ctx.arc(p.x, p.y, baseR, 0, Math.PI * 2);
                ctx.fillStyle = (hoverNode === n) ? "#2f4fe0" : "#3b5de7";
                ctx.fill();

                // label
                ctx.fillStyle = "#334155";
                ctx.font = "12px Arial";
                ctx.fillText(`${n.name} (${n.total})`, p.x + 14, p.y - 10);
                });

                requestAnimationFrame(draw);
            }

            // hover detection
            function onMove(e){
                const rect = canvas.getBoundingClientRect();
                const mx = e.clientX - rect.left;
                const my = e.clientY - rect.top;

                hoverNode = null;
                for (const n of nodes) {
                const dx = mx - n.x;
                const dy = my - n.y;
                if (Math.sqrt(dx*dx + dy*dy) <= (n.r + 8)) {
                    hoverNode = n;
                    break;
                }
                }

                if (hoverNode && tooltip) {
                tooltip.style.display = "block";
                tooltip.style.left = `${mx}px`;
                tooltip.style.top = `${my}px`;
                tooltip.innerHTML = `
                    <div><b>${hoverNode.name}</b></div>
                    <div class="sub">${hoverNode.total} PC terdaftar • ${FLOOR_LAYOUT[activeFloor]?.title || ''}</div>
                `;
                canvas.style.cursor = "pointer";
                } else {
                if (tooltip) tooltip.style.display = "none";
                canvas.style.cursor = "default";
                }
            }

            canvas.addEventListener("mousemove", onMove);
            canvas.addEventListener("mouseleave", () => {
                hoverNode = null;
                if (tooltip) tooltip.style.display = "none";
                canvas.style.cursor = "default";
            });

            // ===== 2) Floor Switch handlers =====
            function setActiveFloor(floor){
                activeFloor = parseInt(floor, 10) || 2;
                nodes = buildNodesForFloor(activeFloor);

                if (info) info.textContent = `Menampilkan: ${FLOOR_LAYOUT[activeFloor]?.title || ('Lantai ' + activeFloor)}`;
                if (tooltip) tooltip.style.display = "none";
                hoverNode = null;

                // set active button
                if (tabs) {
                tabs.querySelectorAll(".pc-floor-btn").forEach(btn => {
                    btn.classList.toggle("active", String(btn.dataset.floor) === String(activeFloor));
                });
                }
            }

            if (tabs) {
                tabs.addEventListener("click", function(e){
                const btn = e.target.closest(".pc-floor-btn");
                if (!btn) return;
                setActiveFloor(btn.dataset.floor);
                });
            }

            // init
            setActiveFloor(activeFloor);
            requestAnimationFrame(draw);
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
            const table = document.getElementById("pcTable");
            const tbody = document.getElementById("pcTbody");
            if (!table || !tbody) return;

            const rows = Array.from(tbody.querySelectorAll("tr"));
            const pageSizeSelect = document.getElementById("pcPageSize");
            const pageInfo = document.getElementById("pcPageInfo");
            const pgWrap = document.getElementById("pcPagination");
            const prevBtn = document.getElementById("pcPrevBtn");
            const nextBtn = document.getElementById("pcNextBtn");

            // kalau data kosong (misal 1 row colspan), jangan dipaginasi
            if (rows.length === 1) {
                const tds = rows[0].querySelectorAll("td");
                if (tds.length === 1 || rows[0].innerText.toLowerCase().includes("belum ada data")) return;
            }

            let pageSize = parseInt(pageSizeSelect?.value || "10", 10);
            let currentPage = 1;

            function getTotalPages() {
                return Math.max(1, Math.ceil(rows.length / pageSize));
            }

            function clampPage(p) {
                const total = getTotalPages();
                if (p < 1) return 1;
                if (p > total) return total;
                return p;
            }

            function renderRows() {
                const total = rows.length;
                const totalPages = getTotalPages();
                currentPage = clampPage(currentPage);

                const startIdx = (currentPage - 1) * pageSize;
                const endIdx = Math.min(startIdx + pageSize, total);

                rows.forEach((tr, idx) => {
                tr.style.display = (idx >= startIdx && idx < endIdx) ? "" : "none";
                });

                pageInfo.textContent = `Menampilkan ${total ? startIdx + 1 : 0}–${endIdx} of ${total}`;

                prevBtn.disabled = currentPage <= 1;
                nextBtn.disabled = currentPage >= totalPages;
            }

            function makePageButton(p) {
                const btn = document.createElement("button");
                btn.type = "button";
                btn.className = "pg-btn" + (p === currentPage ? " active" : "");
                btn.textContent = p;
                btn.addEventListener("click", () => {
                currentPage = p;
                renderPagination();
                renderRows();
                });
                return btn;
            }

            function renderPagination() {
                const totalPages = getTotalPages();
                pgWrap.innerHTML = "";

                const maxButtons = 5;
                let start = Math.max(1, currentPage - Math.floor(maxButtons / 2));
                let end = start + maxButtons - 1;

                if (end > totalPages) {
                end = totalPages;
                start = Math.max(1, end - maxButtons + 1);
                }

                if (start > 1) {
                pgWrap.appendChild(makePageButton(1));
                if (start > 2) {
                    const dots = document.createElement("span");
                    dots.className = "pg-btn";
                    dots.style.pointerEvents = "none";
                    dots.textContent = "…";
                    pgWrap.appendChild(dots);
                }
                }

                for (let p = start; p <= end; p++) {
                pgWrap.appendChild(makePageButton(p));
                }

                if (end < totalPages) {
                if (end < totalPages - 1) {
                    const dots = document.createElement("span");
                    dots.className = "pg-btn";
                    dots.style.pointerEvents = "none";
                    dots.textContent = "…";
                    pgWrap.appendChild(dots);
                }
                pgWrap.appendChild(makePageButton(totalPages));
                }
            }

            prevBtn.addEventListener("click", () => {
                currentPage = clampPage(currentPage - 1);
                renderPagination();
                renderRows();
            });

            nextBtn.addEventListener("click", () => {
                currentPage = clampPage(currentPage + 1);
                renderPagination();
                renderRows();
            });

            pageSizeSelect.addEventListener("change", () => {
                pageSize = parseInt(pageSizeSelect.value, 10);
                currentPage = 1;
                renderPagination();
                renderRows();
            });

            renderPagination();
            renderRows();
            });
        </script>

    </body>

    </html>