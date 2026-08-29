/* Core card package: chart/data helpers are supplied by the shell. */
window.ZogographTypeGrowthCard = {
  render(scans, helpers) {
    const { canvas, mode, drawLineChart, bytesAxis, typeColor, i18n } = helpers;
    const history = scans.file_type_history ?? [];
    const empty = i18n.t("storage.no_scans_in_range_yet", {}, "No completed scans in this range yet");
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
      empty: mode === "change" ? i18n.t("storage.two_scans_for_change", {}, "Two completed scans are needed before there is a change to draw") : empty,
    });
  },
};
