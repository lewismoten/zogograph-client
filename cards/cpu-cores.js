/* Core card package: one gauge per CPU core. */
window.ZogographCpuCoresCard = {
  manifest: Object.freeze({
    id: "core-cpu-cores", locales: ["cpu."],
    helpers: ["target", "buildGauge", "cpuCoreShare", "i18n"],
  }),
  render(cores, helpers) {
    const { target, buildGauge, cpuCoreShare, i18n } = helpers;
    if (!target) return;
    if (!cores.length) {
      target.replaceChildren(Object.assign(document.createElement("p"), {
        className: "muted", textContent: i18n.t("cpu.no_per_core_lines"),
      }));
      return;
    }
    target.replaceChildren(...cores.map(core => {
      const value = core.busy_percent == null ? null : Number(core.busy_percent);
      return buildGauge({
        label: i18n.t("cpu.core_label", { core: core.cpu }), value, warn: 75, crit: 90,
        sub: value == null ? i18n.t("cpu.not_measured_yet") : cpuCoreShare(core),
      });
    }));
  },
};
