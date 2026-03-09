<?php // pcs/_partials/scripts.php ?>

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
  const userRole = "<?= htmlspecialchars(strtolower($role ?? 'user')) ?>";

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

        const res = await fetch("delete_ajax.php", { method: "POST", body: formData });
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
  $("#datatable-buttons").DataTable({
    responsive: true,
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
      { orderable: false, targets: [8] }
    ]
  }).buttons().container().appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");
});
</script>
