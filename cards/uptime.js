/* Core card package: the compact uptime reading that appears on the dashboard. */
window.ZogographUptimeCard = {
  manifest: Object.freeze({
    id: "core-uptime", locales: ["core-uptime."], helpers: ["setCard", "humanDuration", "humanTime", "i18n"],
    catalogs: Object.freeze({
      en: Object.freeze({ "core-uptime.no_boot_time": "no boot time recorded yet", "core-uptime.since": "since {{time}}" }),
      es: Object.freeze({ "core-uptime.no_boot_time": "aún no se registró la hora de arranque", "core-uptime.since": "desde {{time}}" }),
      hi: Object.freeze({ "core-uptime.no_boot_time": "अभी कोई बूट समय दर्ज नहीं है", "core-uptime.since": "{{time}} से" }),
      ar: Object.freeze({ "core-uptime.no_boot_time": "لم يُسجل وقت إقلاع بعد", "core-uptime.since": "منذ {{time}}" }),
      zh: Object.freeze({ "core-uptime.no_boot_time": "尚未记录启动时间", "core-uptime.since": "自 {{time}} 起" }),
    }),
  }),
  render(input, helpers) {
    const { upFor, bootAt } = input;
    const { setCard, humanDuration, humanTime, i18n } = helpers;
    if (upFor == null) {
      setCard("—", i18n.t("core-uptime.no_boot_time", {}, "no boot time recorded yet"));
      return;
    }
    setCard(humanDuration(upFor), i18n.t("core-uptime.since", { time: humanTime(bootAt) }, "since {{time}}"),
      upFor < 3600 ? "warn" : "");
  },
};
