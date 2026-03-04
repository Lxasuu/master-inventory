(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    // pastikan apex ada
    if (typeof ApexCharts === "undefined") return;

    const el = document.querySelector("#stacked-column-chart");
    if (!el) return;

    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const s = (window.DASH_SERIES || {});

    const series = [
      { name: "PC Baru", data: Array.isArray(s.newPc) ? s.newPc : [] },
      { name: "Ready", data: Array.isArray(s.readyPc) ? s.readyPc : [] },
      { name: "Rusak", data: Array.isArray(s.brokenPc) ? s.brokenPc : [] },
    ];

    const options = {
      chart: {
        type: "bar",
        height: 320,
        stacked: true,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          columnWidth: "45%",
          borderRadius: 6
        }
      },
      dataLabels: { enabled: false },
      stroke: { width: 0 },
      xaxis: { categories: months },
      yaxis: { labels: { formatter: (v) => Math.round(v) } },
      legend: { position: "top", horizontalAlign: "right" },
      tooltip: {
        y: { formatter: (v) => `${v} PC` }
      }
    };

    // hapus chart lama kalau kebentuk (hindari duplicate render)
    if (window.__pcChart) {
      try { window.__pcChart.destroy(); } catch(e) {}
    }
    window.__pcChart = new ApexCharts(el, options);
    window.__pcChart.render();
  });
})();
