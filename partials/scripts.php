<?php
// partials/scripts.php

// fallback biar aman
$year = $year ?? (int)date('Y');

$chartNew    = $chartNew ?? [];
$chartReady  = $chartReady ?? [];
$chartBroken = $chartBroken ?? [];

$readyCount      = $readyCount ?? 0;
$notReadyCount   = $notReadyCount ?? 0;
$internetOkCount  = $internetOkCount ?? 0;
$noInternetCount  = $noInternetCount ?? 0;
$brokenCount      = $brokenCount ?? 0;
$nonBrokenCount   = $nonBrokenCount ?? 0;

$pcByLocation = $pcByLocation ?? [];
?>


<?php $BASE_URL = $BASE_URL ?? '/HTML/'; ?>

<script src="<?= $BASE_URL ?>assets/libs/jquery/jquery.min.js"></script>
<script src="<?= $BASE_URL ?>assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $BASE_URL ?>assets/libs/metismenu/metisMenu.min.js"></script>
<script src="<?= $BASE_URL ?>assets/libs/simplebar/simplebar.min.js"></script>
<script src="<?= $BASE_URL ?>assets/libs/node-waves/waves.min.js"></script>

<!-- apexcharts -->
<script src="<?= $BASE_URL ?>assets/libs/apexcharts/apexcharts.min.js"></script>

<script>
  // inject data dari PHP
  window.DASH_YEAR = <?= (int)$year ?>;
  window.DASH_SERIES = {
    newPc: <?= json_encode(array_values($chartNew)) ?>,
    readyPc: <?= json_encode(array_values($chartReady)) ?>,
    brokenPc: <?= json_encode(array_values($chartBroken)) ?>
  };

  window.DONUT_DATA = {
    ready: [<?= (int)$readyCount ?>, <?= (int)$notReadyCount ?>],
    internet: [<?= (int)$internetOkCount ?>, <?= (int)$noInternetCount ?>],
    broken: [<?= (int)$brokenCount ?>, <?= (int)$nonBrokenCount ?>]
  };

  window.mapLocations = <?= json_encode($pcByLocation, JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- app -->
<script src="<?= $BASE_URL ?>assets/js/app.js"></script>

<!-- Mark all notif as read -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".js-read-all-notif").forEach(function (btn) {
    btn.addEventListener("click", async function (e) {
      e.preventDefault();

      try {
        const res = await fetch("/HTML/notification/read_all_ajax.php", {
          method: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" }
        });

        const data = await res.json();
        if (!data.ok) return;

        const bellBtn = document.getElementById("page-header-notifications-dropdown");
        if (bellBtn) {
          const badge = bellBtn.querySelector(".badge.bg-danger.rounded-pill");
          if (badge) badge.remove();
        }

        document.querySelectorAll(".dropdown-menu .notification-item").forEach(function (item) {
          item.classList.remove("bg-light");
          const newBadge = item.querySelector(".badge.bg-danger.mt-1");
          if (newBadge) newBadge.remove();
        });
      } catch (err) {
        console.error("Read all notifications failed:", err);
      }
    });
  });
});
</script>

<!-- Dashboard chart init (kalau element ada) -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const el = document.querySelector("#stacked-column-chart");
  if (!el) return;

  const seriesData = window.DASH_SERIES || { newPc: [], readyPc: [], brokenPc: [] };

  const options = {
    chart: { type: "bar", height: 320, stacked: true, toolbar: { show: false } },
    plotOptions: { bar: { columnWidth: "45%", borderRadius: 6 } },
    dataLabels: { enabled: false },
    stroke: { width: 1 },
    series: [
      { name: "PC Baru",  data: seriesData.newPc || [] },
      { name: "PC Ready", data: seriesData.readyPc || [] },
      { name: "PC Rusak", data: seriesData.brokenPc || [] }
    ],
    xaxis: { categories: ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"] },
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
