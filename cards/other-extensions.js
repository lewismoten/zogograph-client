/* Core card package: the shell supplies all rendering and formatting helpers. */
window.ZogographOtherExtensionsCard = {
  manifest: Object.freeze({
    id: "core-other-extensions", locales: ["storage.", "chart."],
    helpers: ["target", "renderTable", "humanBytes", "humanCount", "i18n"],
  }),
  render(rows, helpers) {
    const { target, renderTable, humanBytes, humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!rows.length) {
      const message = document.createElement("p"); message.className = "muted";
      message.append(t("storage.other_empty_before", {}, "Nothing landed in "));
      const other = document.createElement("code"); other.textContent = t("chart.other", {}, "Other");
      message.append(other, t("storage.other_empty_after", {}, ", or the last scan ran before this was measured."));
      target.replaceChildren(message); return;
    }
    const total = rows.reduce((sum, row) => sum + Number(row.bytes || 0), 0);
    renderTable(target.id, rows, [
      { label: t("storage.extension", {}, "Extension"), className: "name", render: row => row.extension === "(none)" ? t("storage.no_extension", {}, "(no extension)") : `.${row.extension}` },
      { label: t("storage.size", {}, "Size"), className: "size", render: row => humanBytes(row.bytes) },
      { label: t("storage.files", {}, "Files"), className: "size", render: row => humanCount(row.files) },
      { label: t("storage.of_other", {}, "Of other"), className: "size", render: row => total ? `${((Number(row.bytes) / total) * 100).toFixed(1)}%` : "—" },
    ]);
  },
};
