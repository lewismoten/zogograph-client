/* Core card package: the dashboard's account-level gauge cluster. */
window.ZogographOverviewGaugesCard = {
  manifest: Object.freeze({
    id: "core-overview-gauges", locales: ["gauge."],
    helpers: ["target", "buildGauge", "gaugeSeries", "humanBytes", "i18n"],
  }),
  render(input, helpers) {
    const { overview, rows = [] } = input;
    const { target, buildGauge, gaugeSeries, humanBytes, i18n } = helpers;
    if (!target) return;
    const vitals = overview.vitals;
    if (!vitals) {
      target.replaceChildren(Object.assign(document.createElement("p"), {
        className: "muted", textContent: i18n.t("gauge.nothing_collected"),
      }));
      return;
    }

    const host = overview.limits?.rows || [];
    const hostCpu = host.find(row => row.name === "CPU in use")?.host ?? null;
    const usedPercent = vitals.disk_total_bytes ? (vitals.disk_used_bytes / vitals.disk_total_bytes) * 100 : null;
    const inodePercent = vitals.inodes_total ? (vitals.inodes_used / vitals.inodes_total) * 100 : null;
    const cores = vitals.cpu_cores ? Number(vitals.cpu_cores) : null;
    const perCore = vitals.load1 != null && cores ? (Number(vitals.load1) / cores) * 100 : null;

    const dials = [
      {
        label: i18n.t("gauge.disk_used"), value: usedPercent, warn: 88, crit: 95,
        series: gaugeSeries(rows, row => row.disk_total_bytes ? (row.disk_used_bytes / row.disk_total_bytes) * 100 : null),
        sub: vitals.disk_total_bytes ? i18n.t("gauge.free_of_total", { free: humanBytes(vitals.disk_free_bytes), total: humanBytes(vitals.disk_total_bytes) }) : i18n.t("gauge.not_reported"),
      },
      {
        label: i18n.t("gauge.inodes_used"), value: inodePercent, warn: 88, crit: 95,
        series: gaugeSeries(rows, row => row.inodes_total ? (row.inodes_used / row.inodes_total) * 100 : null),
        sub: vitals.inodes_total ? i18n.t("gauge.free", { count: vitals.inodes_free }) : i18n.t("gauge.not_reported"),
      },
      {
        label: i18n.t("gauge.cpu"), value: vitals.cpu_percent == null ? null : Number(vitals.cpu_percent), warn: 70, crit: 90,
        series: gaugeSeries(rows, row => row.cpu_percent),
        sub: vitals.cpu_percent == null ? i18n.t("gauge.no_proc_stat") : hostCpu == null ? i18n.t("gauge.account_allowance") : i18n.t("gauge.allowance_host", { percent: Number(hostCpu).toFixed(0) }),
      },
      {
        label: i18n.t("gauge.memory"), value: vitals.mem_used_percent == null ? null : Number(vitals.mem_used_percent), warn: 80, crit: 92,
        series: gaugeSeries(rows, row => row.mem_used_percent),
        sub: vitals.mem_total_bytes ? i18n.t("gauge.available_of_total", { available: humanBytes(vitals.mem_available_bytes), total: humanBytes(vitals.mem_total_bytes) }) : i18n.t("gauge.not_reported"),
      },
      {
        label: i18n.t("gauge.load"), value: perCore, warn: 100, crit: 150, max: 200,
        series: gaugeSeries(rows, row => row.load1 != null && row.cpu_cores ? (Number(row.load1) / Number(row.cpu_cores)) * 100 : null),
        sub: vitals.load1 == null ? i18n.t("gauge.not_reported") : cores ? i18n.t("gauge.load_over_cores", { load: Number(vitals.load1).toFixed(2), count: cores }) : i18n.t("gauge.load_cores_unknown", { load: Number(vitals.load1).toFixed(2) }),
      },
    ];
    target.replaceChildren(...dials.map(buildGauge));
  },
};
