/* Core card package: chart/data helpers are supplied by the shell. */
window.ZogographTypeGrowthCard = {
  manifest: Object.freeze({
    id: "core-type-growth", locales: ["core-type-growth."],
    helpers: ["canvas", "mode", "drawLineChart", "bytesAxis", "typeColor", "i18n"],
    catalogs: Object.freeze({
      en: Object.freeze({
        "core-type-growth.no_scans_in_range_yet": "No completed scans in this range yet",
        "core-type-growth.two_scans_for_change": "Two completed scans are needed before there is a change to draw",
      }),
      es: Object.freeze({
        "core-type-growth.no_scans_in_range_yet": "Aún no hay análisis completados en este intervalo",
        "core-type-growth.two_scans_for_change": "Se necesitan dos análisis completados antes de dibujar un cambio",
      }),
      hi: Object.freeze({
        "core-type-growth.no_scans_in_range_yet": "इस दायरे में अभी कोई पूरा स्कैन नहीं है",
        "core-type-growth.two_scans_for_change": "बदलाव दिखाने से पहले दो पूरे स्कैन चाहिए",
      }),
      ar: Object.freeze({
        "core-type-growth.no_scans_in_range_yet": "لا توجد فحوصات مكتملة في هذا النطاق بعد",
        "core-type-growth.two_scans_for_change": "يلزم فحصان مكتملان قبل رسم تغيير",
      }),
      zh: Object.freeze({
        "core-type-growth.no_scans_in_range_yet": "此范围内尚无完成的扫描",
        "core-type-growth.two_scans_for_change": "至少需要两次完成的扫描才能绘制变化",
      }),
    }),
  }),
  render(scans, helpers) {
    const { canvas, mode, drawLineChart, bytesAxis, typeColor, i18n } = helpers;
    const history = scans.file_type_history ?? [];
    const empty = i18n.t("core-type-growth.no_scans_in_range_yet", {}, "No completed scans in this range yet");
    if (!history.length) { drawLineChart(canvas, [], [], { empty }); return; }
    const byScan = new Map(), seen = new Set();
    for (const entry of history) {
      const at = Number(entry.started_at);
      if (!byScan.has(at)) byScan.set(at, { started_at: at });
      byScan.get(at)[entry.type] = Number(entry.bytes); seen.add(entry.type);
    }
    let rows = [...byScan.values()].sort((a, b) => a.started_at - b.started_at);
    const types = [...seen].sort();
    if (mode === "change") {
      const changes = [];
      for (let i = 1; i < rows.length; i++) {
        const row = { started_at: rows[i].started_at };
        for (const type of types) row[type] = rows[i - 1][type] == null || rows[i][type] == null ? null : rows[i][type] - rows[i - 1][type];
        changes.push(row);
      }
      rows = changes;
    }
    drawLineChart(canvas, rows, types.map(type => ({ key: type, label: type, color: typeColor(type) })), {
      xKey: "started_at", axis: bytesAxis, zeroBased: mode !== "change",
      empty: mode === "change" ? i18n.t("core-type-growth.two_scans_for_change", {}, "Two completed scans are needed before there is a change to draw") : empty,
    });
  },
};
