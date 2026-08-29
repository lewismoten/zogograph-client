/* Core card package: receives presentation helpers explicitly from the shell. */
window.ZogographScanFactCards = {
  manifest: Object.freeze({
    id: "core-scan-facts", locales: ["storage."],
    helpers: ["facts", "note", "humanTime", "humanDuration", "humanBytes", "humanCount", "i18n"],
  }),
  render(scans, helpers) {
    const { facts, note, humanTime, humanDuration, humanBytes, humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(`storage.${key}`, values, fallback);
    const last = scans.last_scan;
    if (!last) {
      facts.replaceChildren();
      note.textContent = scans.scan_running ? t("first_scan_running", {}, "The first scan is running now.") : t("no_completed_scan", {}, "No completed scan yet.");
      return;
    }
    const cards = [
      [t("last_scan_label", {}, "Last scan"), humanTime(last.finished_at || last.started_at), last.duration_ms ? t("scan_took", { duration: humanDuration(last.duration_ms / 1000) }, `took ${humanDuration(last.duration_ms / 1000)}`) : ""],
      [t("seen_label", {}, "Seen"), humanBytes(last.bytes_seen), t("files_in_directories", { files: humanCount(last.files_seen), directories: humanCount(last.dirs_seen) }, `${humanCount(last.files_seen)} files in ${humanCount(last.dirs_seen)} directories`)],
      [t("scans_in_range", {}, "Scans in this range"), String(scans.rows.length), scans.rows.length > 1 ? t("each_chart_point", {}, "each point on the charts below") : t("not_enough_trend", {}, "not enough to draw a trend")],
    ];
    const frag = document.createDocumentFragment();
    for (const [label, value, sub] of cards) {
      const card = document.createElement("div"); card.className = "card";
      for (const [cls, text] of [["label", label], ["value", value], ["sub muted", sub]]) {
        const div = document.createElement("div"); div.className = cls; div.textContent = text; card.appendChild(div);
      }
      frag.appendChild(card);
    }
    facts.replaceChildren(frag);
    note.textContent = last.error ? t("last_scan_reported", { error: last.error }, `The last scan reported: ${last.error}`) : "";
  },
};
