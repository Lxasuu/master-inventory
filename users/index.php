<?php
require_once __DIR__ . "/../partials/bootstrap.php";
require_role(['admin']);

$pageTitle = "Pengguna";

// =========================
// USER LOGIN DATA (topbar/sidebar)
// =========================
$sessionUser = $_SESSION["user"] ?? [];
$userId   = (int)($sessionUser["user_id"] ?? 0);
$email    = $sessionUser["email"] ?? "";
$fullName = $sessionUser["full_name"] ?? $email;
$role     = $sessionUser["role"] ?? "User";

$BASE_URL = "/HTML/";

// validasi aktif
$stmt = $pdo->prepare("SELECT email, full_name, role, is_active, photo FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbUser || (int)$dbUser["is_active"] !== 1) {
  session_unset();
  session_destroy();
  header("Location: ../auth-login.php");
  exit;
}

$email    = $dbUser["email"] ?? $email;
$fullName = $dbUser["full_name"] ?: ($fullName ?: $email);
$role     = $dbUser["role"] ?: $role;

$photoDb = $dbUser["photo"] ?? "";
$photo = $photoDb
  ? $BASE_URL . ltrim($photoDb, "/")
  : $BASE_URL . "assets/images/default-avatar.png";

// =========================
// NOTIFICATIONS DIHANDLE OLEH partials/topbar.php
// =========================

// =========================
// USERS LIST + SEARCH
// =========================
$users = $pdo->query("
  SELECT user_id, public_id, username, email, full_name, role, is_active, photo, last_login_at
  FROM users
  ORDER BY user_id DESC
")->fetchAll(PDO::FETCH_ASSOC);


function avatar_url(array $row): string {
  $BASE_URL = "/HTML/";
  $DEFAULT  = $BASE_URL . "assets/images/default-avatar.png"; 

  $photoDb = $row["photo"] ?? "";
  if (!$photoDb) return $DEFAULT;

  $abs = $_SERVER["DOCUMENT_ROOT"] . $BASE_URL . ltrim($photoDb, "/");
  if (file_exists($abs)) {
    return $BASE_URL . ltrim($photoDb, "/");
  }

  return $DEFAULT;
}

?>
<!doctype html>
<html lang="en">
<head>



  <meta charset="utf-8" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
  <meta content="Themesdesign" name="author" />
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
/* === TABLE AVATAR CENTER & FIX SIZE === */
#datatable-buttons td.avatar-cell {
  text-align: center;
  vertical-align: middle;
}
#datatable-buttons td.avatar-cell .avatar-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}
#datatable-buttons td.avatar-cell img.avatar-img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  background-color: #f1f3f5;
  border: 2px solid #e6e8ec;
  box-shadow: 0 2px 6px rgba(0,0,0,.08);
}

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

/* Avatar cell selalu rata tengah */
#datatable-buttons td.avatar-cell {
  overflow: visible;
  text-align: center;
}

/* Kolom Aksi selalu visible, tidak dipotong */
#datatable-buttons td:last-child,
#datatable-buttons th:last-child {
  text-align: center;
  overflow: visible;
}

/* Tombol aksi - icon only */
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
.btn-act:hover {
  transform: scale(1.12);
  opacity: .9;
}

/* === ACTIONBAR HEADER === */
.pc-actionbar .card-body {
  padding: 20px 24px;
}
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
  background: linear-gradient(135deg, #5b73e8 0%, #7b52d3 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 22px;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(91,115,232,.35);
}
.pc-actionbar-text {}
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
  display:flex;
  align-items:center;
  gap:10px;
  font-weight: 700;
  box-shadow: 0 14px 28px rgba(0,0,0,.08);
}
.btn-addpc-ic {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(255,255,255,.22);
}
@media (max-width: 768px) {
  .pc-actionbar-inner { flex-direction: column; align-items: stretch; }
  .btn-addpc { width: 100%; justify-content:center; }
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

          <li>
            <a href="index.php" class="waves-effect">
              <i class="dripicons-user"></i>
              <span>Pengguna</span>
            </a>
          </li>

          <?php if (can(['pic','admin'])): ?>
            <li>
              <a href="../pcs/index.php" class="waves-effect">
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
                  <li class="breadcrumb-item active">Pengguna</li>
                </ol>
              </div>
            </div>

          </div>
        </div>
      </div>
      <!-- end page title -->

      <div class="container-fluid">
        <div class="page-content-wrapper">
          <div class="card pc-card pc-actionbar">
            <div class="card-body">
              <div class="pc-actionbar-inner">
                <div class="pc-actionbar-left">
                  <div class="pc-actionbar-icon">
                    <i class="mdi mdi-account-group"></i>
                  </div>
                  <div class="pc-actionbar-text">
                    <div class="pc-actionbar-title">Data Pengguna</div>
                    <div class="pc-actionbar-sub">Kelola seluruh akun pengguna sistem</div>
                  </div>
                </div>
                    <?php if (can(['pic','admin'])): ?>
                    <div class="d-flex gap-2 w-100 justify-content-end">
                      <button type="button" class="btn btn-danger btn-addpc" id="btnBulkDeleteUser" style="display:none;">
                        <span class="btn-addpc-ic"><i class="mdi mdi-delete-outline"></i></span>
                        <span>Hapus Terpilih (<span id="bulkDeleteCountUser">0</span>)</span>
                      </button>
                      <a href="create.php" class="btn btn-primary btn-addpc">
                        <span class="btn-addpc-ic"><i class="mdi mdi-plus"></i></span>
                        <span>Tambahkan Pengguna</span>
                      </a>
                    </div>
                    <?php endif; ?>
                </div>
              </div>
            </div>

    <div class="table-premium">
      <div class="table-responsive">
        <table id="datatable-buttons"
          class="table table-bordered nowrap"
          style="border-collapse: collapse; border-spacing: 0; width: 100%;">

      <thead>
        <tr>
          <?php if (can(['admin'])): ?>
          <th style="width:40px; text-align:center;">
             <input type="checkbox" id="checkAllUser" class="form-check-input" style="cursor:pointer;">
          </th>
          <?php endif; ?>
          <th style="width:36px; text-align:center;">ID</th>
          <th style="width:100px;">Username</th>
          <th style="width:150px;">Email</th>
          <th style="width:130px;">Nama Lengkap</th>
          <th style="width:65px;">Role</th>
          <th style="width:72px;">Status</th>
          <th style="width:52px; text-align:center;">Foto</th>
          <th style="width:130px;">Login Terakhir</th>
          <th style="width:96px; text-align:center;">Aksi</th>
        </tr>
      </thead>

      <tbody>
      <?php foreach ($users as $u): ?>
        <?php $active = ((int)$u["is_active"] === 1); ?>
        <tr>
          <?php if (can(['admin'])): ?>
          <td style="text-align:center; vertical-align:middle;">
             <?php if ((int)$u['user_id'] !== $userId): ?>
                <input type="checkbox" class="form-check-input checkItemUser" value="<?= (int)$u['user_id'] ?>" style="cursor:pointer;">
             <?php else: ?>
                <input type="checkbox" class="form-check-input" disabled title="Anda tidak dapat menghapus diri sendiri">
             <?php endif; ?>
          </td>
          <?php endif; ?>
          <td><?= (int)$u["user_id"] ?></td>
          <td><span class="fw-semibold"><?= htmlspecialchars($u["username"]) ?></span></td>
          <td><?= htmlspecialchars($u["email"]) ?></td>
          <td><?= htmlspecialchars($u["full_name"] ?: '-') ?></td>
          <td><?= htmlspecialchars($u["role"]) ?></td>
          <td>
            <span class="badge <?= $active ? 'bg-success' : 'bg-danger' ?>">
              <?= $active ? 'Active' : 'Inactive' ?>
            </span>
          </td>

          <td class="avatar-cell">
            <div class="avatar-wrap">
              <img src="<?= htmlspecialchars(avatar_url($u)) ?>" class="avatar-img" alt="avatar">
            </div>
          </td>

          <td><?= htmlspecialchars($u["last_login_at"] ?? '-') ?></td>

          <td class="text-center" style="overflow:visible;">
            <div class="d-flex gap-1 justify-content-center">
              <a class="btn btn-sm btn-info btn-act js-view"
                 href="view.php?u=<?= htmlspecialchars($u['public_id']) ?>"
                 title="View" data-bs-toggle="tooltip">
                <i class="mdi mdi-eye-outline"></i>
              </a>
              <a class="btn btn-sm btn-secondary btn-act js-edit"
                 href="edit.php?u=<?= htmlspecialchars($u['public_id']) ?>"
                 title="Edit" data-bs-toggle="tooltip">
                <i class="mdi mdi-pencil-outline"></i>
              </a>
              <button type="button"
                      class="btn btn-sm btn-danger btn-act js-delete-user"
                      data-id="<?= (int)$u['user_id'] ?>"
                      title="Delete" data-bs-toggle="tooltip">
                <i class="mdi mdi-delete-outline"></i>
              </button>
            </div>
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

        </div>
      </div>

    </div>

    <!-- Footer -->
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

<!-- Morvin JS -->
<script src="../assets/libs/jquery/jquery.min.js"></script>
<script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/libs/metismenu/metisMenu.min.js"></script>
<script src="../assets/libs/simplebar/simplebar.min.js"></script>
<script src="../assets/libs/node-waves/waves.min.js"></script>
<script src="../assets/js/app.js"></script>

<!-- Required datatable js -->
<script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- Buttons -->
<script src="../assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="../assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>

<!-- File export (Excel/PDF) -->
<script src="../assets/libs/jszip/jszip.min.js"></script>
<script src="../assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="../assets/libs/pdfmake/build/vfs_fonts.js"></script>

<script src="../assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="../assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="../assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive -->
<script src="../assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="../assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- js -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

// =============================================
// confirmDeleteAjax — single-row delete
// =============================================
function confirmDeleteAjax(userId, btnEl) {
  const userRole = "<?= htmlspecialchars(strtolower($role)) ?>";

  const titleText = (userRole === "admin") ? "Danger Alert (Admin)" : "Konfirmasi Hapus";
  const subtitle  = (userRole === "admin")
    ? "Sebagai admin, kamu akan menghapus data pengguna."
    : "Kamu tidak memiliki hak admin penuh, pastikan tindakan ini benar.";
  const warning   = (userRole === "admin")
    ? "Data akan dihapus permanen dan tidak bisa dikembalikan."
    : "Jika kamu tidak yakin, klik Batal.";

  Swal.fire({
    icon: 'warning',
    title: `<div style="font-weight:800;font-size:22px;">${titleText}</div>`,
    html: `
      <div style="text-align:center;margin-top:10px;">
        <div style="font-size:13px;color:#666;margin-bottom:6px;">${subtitle}</div>
        <div style="font-size:14px;color:#444;margin-top:10px;">Apakah kamu yakin ingin menghapus data ini?</div>
        <div style="font-size:13px;margin-top:10px;color:#d63031;font-weight:700;">${warning}</div>
        <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;font-size:13px;color:#444;">
          <input type="checkbox" id="confirmCheck" style="cursor:pointer;">
          <label for="confirmCheck" style="cursor:pointer;">Saya mengerti dan ingin menghapus data ini</label>
        </div>
      </div>`,
    showCancelButton: true,
    confirmButtonText: 'Hapus Data',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#e74c3c',
    cancelButtonColor: '#6c757d',
    focusConfirm: false,
    width: 460,
    padding: '1.5rem',
    didOpen: () => {
      const btn = Swal.getConfirmButton();
      btn.disabled = true;
      btn.style.opacity = '0.6';
      btn.style.cursor  = 'not-allowed';
      document.getElementById('confirmCheck').addEventListener('change', (e) => {
        btn.disabled   = !e.target.checked;
        btn.style.opacity = e.target.checked ? '1' : '0.6';
        btn.style.cursor  = e.target.checked ? 'pointer' : 'not-allowed';
      });
    },
    showLoaderOnConfirm: true,
    allowOutsideClick: () => !Swal.isLoading(),
    preConfirm: async () => {
      if (!document.getElementById('confirmCheck').checked) {
        Swal.showValidationMessage('Silakan centang konfirmasi terlebih dahulu');
        return false;
      }
      try {
        const fd = new FormData();
        fd.append('id', userId);
        const res  = await fetch('delete_ajax.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.ok) { Swal.showValidationMessage(data.message || 'Gagal menghapus data.'); return false; }
        return data;
      } catch (e) {
        Swal.showValidationMessage('Terjadi kesalahan koneksi.');
        return false;
      }
    }
  }).then((result) => {
    if (!result.isConfirmed) return;
    const dt  = $('#datatable-buttons').DataTable();
    const row = $(btnEl).closest('tr');
    dt.row(row).remove().draw(false);
    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data berhasil dihapus.', timer: 1400, showConfirmButton: false });
  });
}


// =============================================
// DataTable + Event Handlers
// =============================================
$(document).ready(function () {

  const table = $('#datatable-buttons').DataTable({
    lengthChange: true,
    pageLength: 10,
    ordering: true,
    searching: true,
    dom: 'Bfrtip',
    buttons: [
      { extend: 'copy',  text: 'Copy',  exportOptions: { columns: [1,2,3,4,5,6] } },
      { extend: 'excel', text: 'Excel', exportOptions: { columns: [1,2,3,4,5,6] } },
      { extend: 'pdf',   text: 'PDF',   exportOptions: { columns: [1,2,3,4,5,6] } },
      { extend: 'colvis', text: 'Column visibility' }
    ],
    columnDefs: [
      { orderable: false, targets: [0, 7, 9] } // Checkbox, Avatar & Aksi
    ]
  });

  table.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

  // Init Bootstrap tooltips
  var tooltipEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipEls.forEach(function (el) { new bootstrap.Tooltip(el, { trigger: 'hover' }); });

  // Re-init tooltips after DataTable redraws (pagination/search)
  table.on('draw', function () {
    var tEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tEls.forEach(function (el) { new bootstrap.Tooltip(el, { trigger: 'hover' }); });
  });

  // View / Edit — navigasi langsung
  $('#datatable-buttons tbody').on('click', 'a.js-view, a.js-edit', function (e) {
    e.preventDefault();
    e.stopPropagation();
    window.location.assign(this.getAttribute('href'));
  });

  // Delete — panggil confirmDeleteAjax
  $('#datatable-buttons tbody').on('click', '.js-delete-user', function () {
    confirmDeleteAjax($(this).data('id'), this);
  });

  // =============================================
  // Bulk Delete UI
  // =============================================
  const $checkAll  = $('#checkAllUser');
  const $btnBulk   = $('#btnBulkDeleteUser');
  const $bulkCount = $('#bulkDeleteCountUser');

  function updateBulkBtn() {
    const n = $('.checkItemUser:checked').length;
    if (n > 0) { $bulkCount.text(n); $btnBulk.fadeIn(200); }
    else        { $btnBulk.fadeOut(200); }
  }

  $checkAll.on('change', function () {
    $('.checkItemUser').prop('checked', this.checked);
    updateBulkBtn();
  });

  $('#datatable-buttons tbody').on('change', '.checkItemUser', function () {
    if (!this.checked) $checkAll.prop('checked', false);
    else if ($('.checkItemUser:checked').length === $('.checkItemUser').length) $checkAll.prop('checked', true);
    updateBulkBtn();
  });

  table.on('draw', function () {
    $checkAll.prop('checked', false);
    $('.checkItemUser').prop('checked', false);
    updateBulkBtn();
  });

  $btnBulk.on('click', function () {
    const ids = [];
    $('.checkItemUser:checked').each(function () { ids.push($(this).val()); });
    if (!ids.length) return;

    Swal.fire({
      icon: 'warning',
      title: 'Hapus Banyak Data Pengguna',
      html: `Apakah Anda yakin ingin menghapus <b>${ids.length}</b> pengguna yang dipilih?<br><small class="text-danger">Tindakan ini permanen!</small>`,
      showCancelButton: true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Ya, Hapus Semua!',
      cancelButtonText: 'Batal',
      showLoaderOnConfirm: true,
      preConfirm: async () => {
        try {
          const fd = new FormData();
          fd.append('ids', JSON.stringify(ids));
          const res  = await fetch('delete_bulk_ajax.php', { method: 'POST', body: fd });
          const data = await res.json();
          if (!data.ok) { Swal.showValidationMessage(data.message || 'Gagal menghapus pengguna.'); return false; }
          return data;
        } catch (e) {
          Swal.showValidationMessage('Terjadi kesalahan koneksi.');
          return false;
        }
      }
    }).then((result) => {
      if (!result.isConfirmed) return;
      $('.checkItemUser:checked').each(function () { table.row($(this).closest('tr')).remove(); });
      table.draw(false);
      $checkAll.prop('checked', false);
      updateBulkBtn();
      Swal.fire({ icon: 'success', title: 'Berhasil', text: result.value.message || `${ids.length} Pengguna berhasil dihapus.`, timer: 1500, showConfirmButton: false });
    });
  });

});
</script>


</body>
</html>