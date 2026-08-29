/* Core card package: the compact uptime reading that appears on the dashboard. */
window.ZogographUptimeCard = {
  manifest: Object.freeze({
    id: "core-uptime", locales: ["uptime."], helpers: ["setCard", "humanDuration", "humanTime", "i18n"],
  }),
  render(input, helpers) {
    const { upFor, bootAt } = input;
    const { setCard, humanDuration, humanTime, i18n } = helpers;
    if (upFor == null) {
      setCard("—", i18n.t("uptime.no_boot_time", {}, "no boot time recorded yet"));
      return;
    }
    setCard(humanDuration(upFor), i18n.t("uptime.since", { time: humanTime(bootAt) }, "since {{time}}"),
      upFor < 3600 ? "warn" : "");
  },
};
