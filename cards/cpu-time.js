/* Core card package: CPU-state summaries share the shell's chart and labels. */
window.ZogographCpuTimeCard = {
  manifest: Object.freeze({
    id: "core-cpu-time", locales: ["cpu."], helpers: ["setCard", "bandOf", "cpuStateLabel", "i18n"],
  }),
  render(input, helpers) {
    const { now, states, series } = input;
    const { setCard, bandOf, cpuStateLabel, i18n } = helpers;
    const bands = { steal: [5, 20], iowait: [15, 35], softirq: [10, 25], irq: [5, 15] };
    for (const [key, meta] of Object.entries(states)) {
      const value = Number(now[key]);
      const values = series.map(row => Number(row[key])).filter(Number.isFinite);
      const average = values.length ? values.reduce((sum, one) => sum + one, 0) / values.length : null;
      const band = bands[key] ? bandOf(value, bands[key][0], bands[key][1]) : null;
      setCard(cpuStateLabel(meta), Number.isFinite(value) ? `${value.toFixed(1)}%` : "—",
        average == null ? "" : i18n.t("cpu.average_over_range", { percent: average.toFixed(1) }, "{{percent}}% on average over this range"),
        band === "crit" ? "crit" : band === "warn" ? "warn" : "");
    }
  },
};
