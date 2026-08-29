/* Core card package: host and account CPU gauges share shell rendering helpers. */
window.ZogographCpuGaugesCard = {
  manifest: Object.freeze({
    id: "core-cpu-gauges", locales: ["cpu."],
    helpers: ["target", "buildGauge", "count", "i18n"],
  }),
  render(input, helpers) {
    const { sample, accountVitals } = input;
    const { target, buildGauge, count, i18n } = helpers;
    if (!target) return;

    const cores = Number(sample?.cpu_cores) || 0;
    const load1 = sample?.load1 == null ? null : Number(sample.load1);
    const dials = [];

    if (sample?.cpu_percent != null) {
      const monitoringCpu = sample.monitoring_cpu_percent == null ? null : Number(sample.monitoring_cpu_percent);
      const workloadCpu = monitoringCpu == null ? null : Math.max(0, Number(sample.cpu_percent) - monitoringCpu);
      dials.push({
        label: i18n.t("cpu.gauge_host_cpu"), value: Number(sample.cpu_percent), warn: 70, crit: 90,
        overlay: monitoringCpu,
        overlayLabel: monitoringCpu == null ? null : i18n.t("cpu.gauge_collector_arc", { percent: monitoringCpu.toFixed(1) }),
        sub: i18n.t("cpu.gauge_host_total", { cores })
          + (workloadCpu == null ? "" : i18n.t("cpu.gauge_workload_excluding_monitor", { percent: workloadCpu.toFixed(1) })),
      });
    }

    if (accountVitals.cpu_percent != null) {
      dials.push({
        label: i18n.t("cpu.gauge_account_cpu"), value: Number(accountVitals.cpu_percent), warn: 70, crit: 90,
        sub: i18n.t("cpu.gauge_account_allowance"),
      });
    }

    if (cores > 0 && load1 != null) {
      const monitorRunnable = sample.monitoring_runnable == null ? null : Number(sample.monitoring_runnable);
      dials.push({
        label: i18n.t("cpu.gauge_load_per_core"), value: (load1 / cores) * 100,
        warn: 100, crit: 150, max: 200,
        overlay: monitorRunnable == null ? null : (monitorRunnable / cores) * 100,
        overlayLabel: monitorRunnable == null ? null : i18n.t("cpu.gauge_runnable_arc", { count: monitorRunnable }),
        sub: i18n.t("cpu.gauge_load_over_cores", { load: load1.toFixed(2), cores: count(cores, "core") })
          + (monitorRunnable == null ? "" : i18n.t("cpu.gauge_collector_runnable", { count: monitorRunnable })),
      });
    }

    const myCores = Number(accountVitals.cpu_cores) || 0;
    if (myCores > 0 && cores > 0 && myCores < cores) {
      dials.push({
        label: i18n.t("cpu.gauge_core_share"), value: (myCores / cores) * 100, warn: 60, crit: 85,
        sub: i18n.t("cpu.gauge_core_share_summary", { mine: myCores, total: cores }),
      });
    }

    target.replaceChildren(...dials.map(buildGauge));
  },
};
