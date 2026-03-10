<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_once __DIR__ . "/../partials/csrf.php";
require_role(['user','pic','admin']);

$pageTitle = "Data PC";

// ambil context global (user + notif + photo + base_url)
require_once __DIR__ . "/../partials/app_context.php";

// =========================
// NOTIFICATIONS DIHANDLE OLEH partials/topbar.php
// =========================

// =========================
// DATA PC + SEARCH 
// =========================
$q = trim($_GET["q"] ?? "");

$sql = "
SELECT 
  p.pc_id, p.unique_code, p.unique_name, p.internet, p.is_ready,
  l.location_name, c.condition_name, cs.status_name
FROM pcs p
LEFT JOIN locations l ON p.location_id = l.location_id
LEFT JOIN conditions c ON p.condition_id = c.condition_id
LEFT JOIN check_statuses cs ON p.check_status_id = cs.check_status_id
";

$params = [];
if ($q !== "") {
  $sql .= "
  WHERE
    p.unique_code LIKE ? OR
    p.unique_name LIKE ? OR
    l.location_name LIKE ? OR
    c.condition_name LIKE ? OR
    cs.status_name LIKE ?
  ";
  $like = "%$q%";
  $params = [$like,$like,$like,$like,$like];
}

$sql .= " ORDER BY p.pc_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pcs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

  <!-- DataTables -->
  <link href="../assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="../assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
  <link href="../assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

  <style>
  /* === COMPACT TABLE === */
  #datatable-buttons {
    width: 100% !important;
    table-layout: fixed !important;
    font-size: 13px;
  }
  #datatable-buttons th,
  #datatable-buttons td {
    vertical-align: middle;
    padding: 8px 8px !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #datatable-buttons td:last-child,
  #datatable-buttons th:last-child {
    text-align: center;
    overflow: visible;
  }

  /* Tombol aksi icon-only */
  .btn-act {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    font-size: 15px;
    transition: transform .12s, opacity .12s;
  }
  .btn-act:hover { transform: scale(1.12); opacity: .9; }

  /* === ACTIONBAR HEADER === */
  .pc-actionbar .card-body { padding: 20px 24px; }
  .pc-actionbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .pc-actionbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .pc-actionbar-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #2ecc71 0%, #1a9e5c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(46,204,113,.35);
  }
  .pc-actionbar-title {
    font-weight: 700;
    font-size: 15px;
    color: #2d3748;
    line-height: 1.2;
    margin: 0;
  }
  .pc-actionbar-sub {
    font-size: 12px;
    color: #a0aec0;
    margin-top: 2px;
  }

  /* Tombol Add */
  .btn-addpc {
    height: 46px;
    border-radius: 16px;
    padding: 0 16px 0 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    box-shadow: 0 14px 28px rgba(0,0,0,.08);
  }
  .btn-addpc-ic {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.22);
  }
  @media (max-width: 768px) {
    .pc-actionbar-inner { flex-direction: column; align-items: stretch; }
    .btn-addpc { width: 100%; justify-content: center; }
  }
  </style>

  

</head>

<body>
<div id="layout-wrapper">

  <!-- ================= TOPBAR ================= -->
  <?php include __DIR__ . '/../partials/topbar.php'; ?>

  <!-- ================= SIDEBAR ================= -->
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
            <div class="col-md-6">
              <div class="page-title">
                <h4><?= htmlspecialchars($pageTitle) ?></h4>
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item">Meta Inventory</li>
                  <li class="breadcrumb-item active">Data PC</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="container-fluid">

        <div class="page-content-wrapper">
          <div class="card pc-card pc-actionbar">
            <div class="card-body">
              <div class="pc-actionbar-inner">
                <div class="pc-actionbar-left">
                  <div class="pc-actionbar-icon">
                    <i class="mdi mdi-desktop-classic"></i>
                  </div>
                  <div class="pc-actionbar-text">
                    <div class="pc-actionbar-title">Data PC</div>
                    <div class="pc-actionbar-sub">Kelola inventaris dan status seluruh unit PC</div>
                  </div>
                </div>

                <?php if (can(['pic','admin'])): ?>
                  <div class="d-flex gap-2 w-100 justify-content-end">
                    <button type="button" class="btn btn-danger btn-addpc" id="btnBulkDeletePc" style="display:none;">
                      <span class="btn-addpc-ic"><i class="mdi mdi-delete-outline"></i></span>
                      <span>Hapus Terpilih (<span id="bulkDeleteCountPc">0</span>)</span>
                    </button>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-success btn-addpc" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                      <span class="btn-addpc-ic"><i class="mdi mdi-file-excel"></i></span>
                      <span>Import Excel</span>
                    </button>
                    <a href="create.php" class="btn btn-primary btn-addpc">
                      <span class="btn-addpc-ic"><i class="mdi mdi-plus"></i></span>
                      <span>Tambahkan PC</span>
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>


          <!-- TABLE -->
          <div class="card pc-card">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between mb-3">
              </div>

              <div class="table-premium">
                <div class="table-responsive">
                  <table id="datatable-buttons"
                    class="table table-bordered nowrap"
                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                    <thead>
                      <tr>
                        <?php if (can(['pic','admin'])): ?>
                        <th style="width: 40px; text-align:center;">
                           <input type="checkbox" id="checkAllPc" class="form-check-input" style="cursor:pointer;">
                        </th>
                        <?php endif; ?>
                        <th style="width:36px; text-align:center;">ID</th>
                        <th style="width:120px;">Kode</th>
                        <th style="width:140px;">Nama PC</th>
                        <th style="width:110px;">Lokasi</th>
                        <th style="width:90px;">Kondisi</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:70px;">Internet</th>
                        <th style="width:80px;">Ready</th>
                        <th style="width:86px; text-align:center;">Aksi</th>
                      </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($pcs as $pc): ?>
                      <?php
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
                      <tr>
                        <?php if (can(['pic','admin'])): ?>
                        <td style="text-align:center; vertical-align:middle;">
                           <input type="checkbox" class="form-check-input checkItemPc" value="<?= (int)$pc['pc_id'] ?>" style="cursor:pointer;">
                        </td>
                        <?php endif; ?>
                        <td class="name-strong"><?= (int)$pc['pc_id'] ?></td>
                        <td><span class="code-pill"><?= htmlspecialchars($pc['unique_code'] ?? '-') ?></span></td>

                        <td>
                          <div class="name-strong"><?= htmlspecialchars($pc['unique_name'] ?: '-') ?></div>
                          <div class="meta-small"><?= htmlspecialchars($pc['location_name'] ?? '-') ?></div>
                        </td>

                        <td><?= htmlspecialchars($pc['location_name'] ?? '-') ?></td>

                        <td>
                          <span class="badge-soft <?= $condClass ?>">
                            <?= htmlspecialchars($pc['condition_name'] ?? '-') ?>
                          </span>
                        </td>

                        <td>
                          <span class="badge-soft <?= $stClass ?>">
                            <?= htmlspecialchars($pc['status_name'] ?? '-') ?>
                          </span>
                        </td>

                        <td>
                          <span class="badge-soft <?= $internetOk ? 'ok' : 'no' ?>">
                            <?= $internetOk ? 'Yes' : 'No' ?>
                          </span>
                        </td>

                        <td>
                          <span class="badge-soft <?= $readyOk ? 'ok' : 'bad' ?>">
                            <?= $readyOk ? 'Ready' : 'Not Ready' ?>
                          </span>
                        </td>

                        <td class="col-aksi" style="overflow:visible;">
                          <?php if (can(['pic','admin'])): ?>
                            <div class="d-flex gap-1 justify-content-center">
                              <a class="btn btn-sm btn-info btn-act"
                                 href="view.php?id=<?= (int)$pc['pc_id'] ?>"
                                 title="View" data-bs-toggle="tooltip">
                                <i class="mdi mdi-eye-outline"></i>
                              </a>
                              <a class="btn btn-sm btn-secondary btn-act"
                                 href="edit.php?id=<?= (int)$pc['pc_id'] ?>"
                                 title="Edit" data-bs-toggle="tooltip">
                                <i class="mdi mdi-pencil-outline"></i>
                              </a>
                              <button type="button" class="btn btn-sm btn-danger btn-act"
                                onclick="confirmDeletePcAjax(<?= (int)$pc['pc_id'] ?>, this)"
                                title="Delete" data-bs-toggle="tooltip">
                                <i class="mdi mdi-delete-outline"></i>
                              </button>
                            </div>
                          <?php else: ?>
                            <div class="d-flex gap-1 justify-content-center">
                              <a class="btn btn-sm btn-info btn-act"
                                 href="view.php?id=<?= (int)$pc['pc_id'] ?>"
                                 title="View" data-bs-toggle="tooltip">
                                <i class="mdi mdi-eye-outline"></i>
                              </a>
                            </div>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    </tbody>

                  </table>
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

<!-- Modal Import Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importExcelModalLabel">Import Data PC dari Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="import_excel.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="modal-body">
          <div class="mb-3">
            <p class="text-muted font-size-13 mb-3">
              Gunakan fitur ini untuk menambahkan banyak data PC sekaligus.<br>
              Download template Excel di bawah ini, isi data sesuai format, lalu upload kembali.
            </p>
            <a href="download_template.php" class="btn btn-sm btn-outline-info mb-3">
              <i class="mdi mdi-download me-1"></i> Download Template Excel
            </a>
          </div>
          <div class="mb-3">
            <label for="excelFile" class="form-label">Upload File Excel (.xlsx, .xls, .csv)</label>
            <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls, .csv" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success" id="btnImportSubmit">Import Data</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="rightbar-overlay"></div>

<!-- Morvin JS -->
<script src="../assets/libs/jquery/jquery.min.js"></script>
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/metismenu/metisMenu.min.js"></script>
<script src="../assets/libs/simplebar/simplebar.min.js"></script>
<script src="../assets/libs/node-waves/waves.min.js"></script>
<script src="../assets/js/app.js"></script>

<!-- DataTables -->
<script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- Buttons -->
<script src="../assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="../assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="../assets/libs/jszip/jszip.min.js"></script>
<script src="../assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="../assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="../assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="../assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="../assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive -->
<script src="../assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="../assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  const CSRF_TOKEN = "<?= htmlspecialchars(csrf_token()) ?>";
</script>

<script>
function confirmDeletePcAjax(pcId, btnEl) {
  const userRole = "<?= htmlspecialchars(strtolower($role)) ?>";

  const titleText = (userRole === "admin") ? "Danger Alert (Admin)" : "Konfirmasi Hapus";
  const subtitle = (userRole === "admin")
    ? "Sebagai admin, kamu akan menghapus data PC."
    : "Pastikan tindakan ini benar.";

  const warning = (userRole === "admin")
    ? "Data akan dihapus permanen dan tidak bisa dikembalikan."
    : "Jika kamu tidak yakin, klik Batal.";

  Swal.fire({
    icon: 'warning',
    title: `<div style="font-weight:800;font-size:22px;">${titleText}</div>`,
    html: `
      <div style="text-align:center; margin-top:10px;">
        <div style="font-size:13px;color:#666;margin-bottom:6px;">${subtitle}</div>
        <div style="font-size:14px;color:#444;margin-top:10px;">
          Apakah kamu yakin ingin menghapus data ini?
        </div>
        <div style="font-size:13px;margin-top:10px;color:#d63031;font-weight:700;">
          ${warning}
        </div>
        <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;font-size:13px;color:#444;">
          <input type="checkbox" id="confirmCheckPc" style="cursor:pointer;">
          <label for="confirmCheckPc" style="cursor:pointer;">
            Saya mengerti dan ingin menghapus data ini
          </label>
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Hapus Data',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#e74c3c',
    cancelButtonColor: '#6c757d',
    focusConfirm: false,
    width: 460,
    padding: '1.5rem',

    didOpen: () => {
      const confirmBtn = Swal.getConfirmButton();
      confirmBtn.disabled = true;
      confirmBtn.style.opacity = "0.6";
      confirmBtn.style.cursor = "not-allowed";

      const checkbox = document.getElementById("confirmCheckPc");
      checkbox.addEventListener("change", () => {
        confirmBtn.disabled = !checkbox.checked;
        confirmBtn.style.opacity = checkbox.checked ? "1" : "0.6";
        confirmBtn.style.cursor = checkbox.checked ? "pointer" : "not-allowed";
      });
    },

    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),

    preConfirm: async () => {
      const checkbox = document.getElementById("confirmCheckPc");
      if (!checkbox.checked) {
        Swal.showValidationMessage("Silakan centang konfirmasi terlebih dahulu");
        return false;
      }

      try {
        const formData = new FormData();
        formData.append("id", pcId);
        formData.append("csrf_token", CSRF_TOKEN);

        const res = await fetch("delete_ajax.php", {
          method: "POST",
          body: formData
        });

        const data = await res.json();
        if (!data.ok) {
          Swal.showValidationMessage(data.message || "Gagal menghapus data.");
          return false;
        }
        return data;
      } catch (err) {
        Swal.showValidationMessage("Terjadi kesalahan koneksi.");
        return false;
      }
    }
  }).then((result) => {
    if (!result.isConfirmed) return;

    const table = $("#datatable-buttons").DataTable();
    const row = $(btnEl).closest("tr");
    table.row(row).remove().draw(false);

    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: "Data PC berhasil dihapus.",
      timer: 1400,
      showConfirmButton: false
    });
  });
}
</script>

<script>
$(document).ready(function () {
  const table = $("#datatable-buttons").DataTable({
    lengthChange: true,
    pageLength: 10,
    ordering: true,
    searching: true,
    dom: "Bfrtip",
    buttons: [
      { extend: "copy",  text: "Copy",  exportOptions: { columns: [1,2,3,4,5,6,7] } },
      { extend: "excel", text: "Excel", exportOptions: { columns: [1,2,3,4,5,6,7] } },
      { extend: "pdf",   text: "PDF",   exportOptions: { columns: [1,2,3,4,5,6,7] } },
      { extend: "colvis", text: "Column visibility" }
    ],
    columnDefs: [
      { orderable: false, targets: [0, 8] } // Checkbox & Aksi
    ]
  });

  table.buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");

  // Tooltip init
  var ttEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  ttEls.forEach(function(el){ new bootstrap.Tooltip(el, { trigger: 'hover' }); });
  table.on('draw', function() {
    var tEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tEls.forEach(function(el){ new bootstrap.Tooltip(el, { trigger: 'hover' }); });
  });

  // Bulk Delete UI Logic
  const $checkAll = $('#checkAllPc');
  const $btnBulkDelete = $('#btnBulkDeletePc');
  const $bulkCount = $('#bulkDeleteCountPc');

  function updateBulkDeleteBtn() {
    const checkedCount = $('.checkItemPc:checked').length;
    if (checkedCount > 0) {
      $bulkCount.text(checkedCount);
      $btnBulkDelete.fadeIn(200);
    } else {
      $btnBulkDelete.fadeOut(200);
    }
  }

  // Checkbox interactions
  $checkAll.on('change', function() {
    $('.checkItemPc').prop('checked', this.checked);
    updateBulkDeleteBtn();
  });

  $('#datatable-buttons tbody').on('change', '.checkItemPc', function() {
    if (!this.checked) {
      $checkAll.prop('checked', false);
    } else if ($('.checkItemPc:checked').length === $('.checkItemPc').length) {
      $checkAll.prop('checked', true);
    }
    updateBulkDeleteBtn();
  });

  // Handle pagination/search issues with master checkbox
  $('#datatable-buttons').DataTable().on('draw', function() {
    $checkAll.prop('checked', false);
    $('.checkItemPc').prop('checked', false);
    updateBulkDeleteBtn();
  });

  // Bulk Delete Action
  $btnBulkDelete.on('click', function() {
    const checkedIds = [];
    $('.checkItemPc:checked').each(function() {
      checkedIds.push($(this).val());
    });

    if (checkedIds.length === 0) return;

    Swal.fire({
      icon: 'warning',
      title: 'Hapus Banyak Data',
      html: `Apakah Anda yakin ingin menghapus <b>${checkedIds.length}</b> data PC yang dipilih?<br><small class="text-danger">Tindakan ini permanen!</small>`,
      showCancelButton: true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Hapus Semua!',
      cancelButtonText: 'Batal',
      showLoaderOnConfirm: true,
      preConfirm: async () => {
        try {
          const formData = new FormData();
          formData.append('ids', JSON.stringify(checkedIds));
          formData.append('csrf_token', CSRF_TOKEN);

          const res = await fetch("delete_bulk_ajax.php", {
            method: "POST",
            body: formData
          });

          const data = await res.json();
          if (!data.ok) {
            Swal.showValidationMessage(data.message || "Gagal menghapus data.");
            return false;
          }
          return data;
        } catch (err) {
          Swal.showValidationMessage("Terjadi kesalahan koneksi.");
          return false;
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        const table = $("#datatable-buttons").DataTable();
        // Hapus row data table secara visual
        $('.checkItemPc:checked').each(function() {
          const row = $(this).closest('tr');
          table.row(row).remove();
        });
        table.draw(false);
        $checkAll.prop('checked', false);
        updateBulkDeleteBtn();

        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: result.value.message || `${checkedIds.length} Data PC berhasil dihapus.`,
          timer: 1500,
          showConfirmButton: false
        });
      }
    });

  });

});
</script>

</body>
</html>