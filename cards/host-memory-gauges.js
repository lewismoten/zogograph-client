/* Core card package: memory headroom, swap state, and account share gauges. */
window.ZogographHostMemoryGaugesCard = {
  manifest: Object.freeze({
    id: "core-host-memory-gauges", locales: ["host."],
    helpers: ["target", "buildGauge", "humanBytes", "i18n"],
  }),
  render(input, helpers) {
    const { sample, swapUsed, swap, accountVitals } = input;
    const { target, buildGauge, humanBytes, i18n } = helpers;
    if (!target) return;

    const total = Number(sample.mem_total) || 0;
    const available = Number(sample.mem_available) || 0;
    const swapTotal = Number(sample.swap_total) || 0;
    const dials = [];

    if (total > 0) {
      const committed = Math.max(0, total - available);
      const monitoringPss = sample.monitoring_pss_bytes == null ? null : Number(sample.monitoring_pss_bytes);
      const workloadCommitted = monitoringPss == null ? null : Math.max(0, committed - monitoringPss);
      dials.push({
        label: i18n.t("host.memory_committed"), value: (committed / total) * 100, warn: 80, crit: 92,
        overlay: monitoringPss == null ? null : (monitoringPss / total) * 100,
        overlayLabel: monitoringPss == null ? null : i18n.t("host.memory_collector_arc", { amount: humanBytes(monitoringPss) }),
        sub: i18n.t("host.memory_committed_sub", { committed: humanBytes(committed), total: humanBytes(total), available: humanBytes(available) })
          + (workloadCommitted == null ? "" : i18n.t("host.memory_non_monitor", { amount: humanBytes(workloadCommitted) }))
          + (sample.monitoring_processes == null ? "" : i18n.t("host.memory_monitoring_processes", { count: sample.monitoring_processes })),
      });
    }

    if (swapTotal > 0) {
      dials.push({
        label: i18n.t("host.swap_used"), value: (swapUsed / swapTotal) * 100,
        warn: swap?.state === "pressure" ? 1 : 60, crit: swap?.state === "pressure" ? 25 : 90,
        sub: i18n.t("host.swap_used_sub", { used: humanBytes(swapUsed), total: humanBytes(swapTotal) })
          + (swap?.state === "pressure" ? i18n.t("host.swap_under_pressure") : i18n.t("host.swap_no_pressure")),
      });
    } else {
      dials.push({
        label: i18n.t("host.swap_used"), value: null, warn: 60, crit: 90,
        sub: i18n.t("host.swap_not_configured"),
      });
    }

    const mine = Number(accountVitals.mem_total_bytes) || 0;
    if (total > 0 && mine > 0 && mine < total) {
      dials.push({
        label: i18n.t("host.account_memory_share"), value: (mine / total) * 100, warn: 60, crit: 85,
        sub: i18n.t("host.account_memory_share_sub", { amount: humanBytes(mine), total: humanBytes(total) }),
      });
    }
    target.replaceChildren(...dials.map(buildGauge));
  },
};
