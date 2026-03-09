<?php // pcs/_partials/head.php ?>

<!-- DataTables -->
<link href="../assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="../assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link href="../assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

<style>
  #datatable-buttons{
    width:100% !important;
    table-layout: fixed !important;
  }
  #datatable-buttons th,
  #datatable-buttons td{
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
  }
  #datatable-buttons th:nth-child(1),
  #datatable-buttons td:nth-child(1){
    width:70px !important;
    min-width:70px !important;
    max-width:70px !important;
  }
  #datatable-buttons th:nth-child(9),
  #datatable-buttons td:nth-child(9){
    width:190px !important;
    min-width:190px !important;
    max-width:190px !important;
    text-align:center;
  }

  /* ===== Action bar style (keep bentuk lama) ===== */
  .pc-actionbar .card-body{ padding: 16px 18px; }
  .pc-actionbar-inner{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 14px;
  }
  .pc-actionbar-title{ font-weight: 800; font-size: 16px; line-height: 1.1; }
  .pc-actionbar-sub{ font-size: 12.5px; color: #6c757d; margin-top: 4px; }

  .btn-addpc{
    height: 46px;
    border-radius: 16px;
    padding: 0 16px 0 10px;
    display:flex;
    align-items:center;
    gap:10px;
    font-weight: 700;
    box-shadow: 0 14px 28px rgba(0,0,0,.08);
  }
  .btn-addpc-ic{
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: rgba(255,255,255,.22);
  }

  @media (max-width: 768px){
    .pc-actionbar-inner{ flex-direction: column; align-items: stretch; }
    .btn-addpc{ width: 100%; justify-content:center; }
  }
</style>
