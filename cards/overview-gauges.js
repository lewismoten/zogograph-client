/* Core card package: the dashboard's account-level gauge cluster. */
window.ZogographOverviewGaugesCard = {
  manifest: Object.freeze({
    id: "core-overview-gauges", locales: ["core-overview-gauges."],
    helpers: ["target", "buildGauge", "gaugeSeries", "humanBytes", "i18n"],
    catalogs: Object.freeze({
      en: Object.freeze({"core-overview-gauges.nothing_collected":"Nothing collected yet.","core-overview-gauges.disk_used":"Disk used","core-overview-gauges.free_of_total":"{{free}} free of {{total}}","core-overview-gauges.inodes_used":"Inodes used","core-overview-gauges.cpu":"CPU","core-overview-gauges.no_proc_stat":"no /proc/stat here","core-overview-gauges.account_allowance":"of this account's allowance","core-overview-gauges.allowance_host":"of the allowance · host {{percent}}%","core-overview-gauges.memory":"Memory","core-overview-gauges.available_of_total":"{{available}} available of {{total}}","core-overview-gauges.load_over_cores":"{{load}} over {{count, number}} cores","core-overview-gauges.load_cores_unknown":"{{load}}, cores unknown"}),
      es: Object.freeze({"core-overview-gauges.nothing_collected":"Aún no se recopiló nada.","core-overview-gauges.disk_used":"Disco usado","core-overview-gauges.free_of_total":"{{free}} libres de {{total}}","core-overview-gauges.inodes_used":"Inodos usados","core-overview-gauges.cpu":"CPU","core-overview-gauges.no_proc_stat":"no hay /proc/stat aquí","core-overview-gauges.account_allowance":"del límite de esta cuenta","core-overview-gauges.allowance_host":"del límite · host {{percent}}%","core-overview-gauges.memory":"Memoria","core-overview-gauges.available_of_total":"{{available}} disponibles de {{total}}","core-overview-gauges.load_over_cores":"{{load}} en {{count, number}} núcleos","core-overview-gauges.load_cores_unknown":"{{load}}, núcleos desconocidos"}),
      hi: Object.freeze({"core-overview-gauges.nothing_collected":"अभी तक कुछ एकत्र नहीं हुआ।","core-overview-gauges.disk_used":"प्रयुक्त डिस्क","core-overview-gauges.free_of_total":"{{total}} में से {{free}} खाली","core-overview-gauges.inodes_used":"प्रयुक्त इनोड","core-overview-gauges.cpu":"CPU","core-overview-gauges.no_proc_stat":"यहाँ /proc/stat नहीं है","core-overview-gauges.account_allowance":"इस खाते की अनुमत सीमा का","core-overview-gauges.allowance_host":"अनुमत सीमा का · होस्ट {{percent}}%","core-overview-gauges.memory":"मेमोरी","core-overview-gauges.available_of_total":"{{total}} में से {{available}} उपलब्ध","core-overview-gauges.load_over_cores":"{{count, number}} कोर पर {{load}}","core-overview-gauges.load_cores_unknown":"{{load}}, कोर अज्ञात"}),
      ar: Object.freeze({"core-overview-gauges.nothing_collected":"لم تُجمع أي بيانات بعد.","core-overview-gauges.disk_used":"المساحة المستخدمة","core-overview-gauges.free_of_total":"{{free}} متاح من {{total}}","core-overview-gauges.inodes_used":"عُقد inode مستخدمة","core-overview-gauges.cpu":"المعالج","core-overview-gauges.no_proc_stat":"لا يوجد ‎/proc/stat هنا","core-overview-gauges.account_allowance":"من حد هذا الحساب","core-overview-gauges.allowance_host":"من الحد · المضيف {{percent}}%","core-overview-gauges.memory":"الذاكرة","core-overview-gauges.available_of_total":"{{available}} متاح من {{total}}","core-overview-gauges.load_over_cores":"{{load}} عبر {{count, number}} أنوية","core-overview-gauges.load_cores_unknown":"{{load}}، عدد الأنوية غير معروف"}),
      zh: Object.freeze({"core-overview-gauges.nothing_collected":"尚未收集任何数据。","core-overview-gauges.disk_used":"已用磁盘","core-overview-gauges.free_of_total":"共 {{total}}，剩余 {{free}}","core-overview-gauges.inodes_used":"已用 inode","core-overview-gauges.cpu":"CPU","core-overview-gauges.no_proc_stat":"这里没有 /proc/stat","core-overview-gauges.account_allowance":"占此帐户的限额","core-overview-gauges.allowance_host":"占限额 · 主机 {{percent}}%","core-overview-gauges.memory":"内存","core-overview-gauges.available_of_total":"共 {{total}}，可用 {{available}}","core-overview-gauges.load_over_cores":"{{count, number}} 个核心上的 {{load}}","core-overview-gauges.load_cores_unknown":"{{load}}，核心数未知"}),
    }),
  }),
  render(input, helpers) {
    const { overview, rows = [] } = input;
    const { target, buildGauge, gaugeSeries, humanBytes, i18n } = helpers;
    const t = (key, values = {}) => i18n.t(`core-overview-gauges.${key}`, values);
    if (!target) return;
    const vitals = overview.vitals;
    if (!vitals) {
      target.replaceChildren(Object.assign(document.createElement("p"), {
        className: "muted", textContent: t("nothing_collected"),
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
        label: t("disk_used"), value: usedPercent, warn: 88, crit: 95,
        series: gaugeSeries(rows, row => row.disk_total_bytes ? (row.disk_used_bytes / row.disk_total_bytes) * 100 : null),
        sub: vitals.disk_total_bytes ? t("free_of_total", { free: humanBytes(vitals.disk_free_bytes), total: humanBytes(vitals.disk_total_bytes) }) : i18n.t("gauge.not_reported"),
      },
      {
        label: t("inodes_used"), value: inodePercent, warn: 88, crit: 95,
        series: gaugeSeries(rows, row => row.inodes_total ? (row.inodes_used / row.inodes_total) * 100 : null),
        sub: vitals.inodes_total ? i18n.t("gauge.free", { count: vitals.inodes_free }) : i18n.t("gauge.not_reported"),
      },
      {
        label: t("cpu"), value: vitals.cpu_percent == null ? null : Number(vitals.cpu_percent), warn: 70, crit: 90,
        series: gaugeSeries(rows, row => row.cpu_percent),
        sub: vitals.cpu_percent == null ? t("no_proc_stat") : hostCpu == null ? t("account_allowance") : t("allowance_host", { percent: Number(hostCpu).toFixed(0) }),
      },
      {
        label: t("memory"), value: vitals.mem_used_percent == null ? null : Number(vitals.mem_used_percent), warn: 80, crit: 92,
        series: gaugeSeries(rows, row => row.mem_used_percent),
        sub: vitals.mem_total_bytes ? t("available_of_total", { available: humanBytes(vitals.mem_available_bytes), total: humanBytes(vitals.mem_total_bytes) }) : i18n.t("gauge.not_reported"),
      },
      {
        label: i18n.t("gauge.load"), value: perCore, warn: 100, crit: 150, max: 200,
        series: gaugeSeries(rows, row => row.load1 != null && row.cpu_cores ? (Number(row.load1) / Number(row.cpu_cores)) * 100 : null),
        sub: vitals.load1 == null ? i18n.t("gauge.not_reported") : cores ? t("load_over_cores", { load: Number(vitals.load1).toFixed(2), count: cores }) : t("load_cores_unknown", { load: Number(vitals.load1).toFixed(2) }),
      },
    ];
    target.replaceChildren(...dials.map(buildGauge));
  },
};
