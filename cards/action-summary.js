/* Core card package: concise outcome cards above the action log. */
window.ZogographActionSummaryCard = {
  manifest: Object.freeze({
    id: "core-action-summary", locales: ["action_log."],
    helpers: ["target", "humanBytes", "humanTime", "count", "i18n"],
  }),
  render(rows, helpers) {
    const { target, humanBytes, humanTime, count, i18n } = helpers;
    if (!target) return;
    const freed = rows.reduce((sum, row) => sum + Number(row.bytes_freed || 0), 0);
    const files = rows.reduce((sum, row) => sum + Number(row.files_removed || 0), 0);
    const refused = rows.filter(row => row.outcome !== "ok").length;
    const cards = [
      [i18n.t("action_log.things_done"), String(rows.length), rows.length ? i18n.t("action_log.since", { time: humanTime(rows[rows.length - 1].at) }) : ""],
      [i18n.t("action_log.space_freed"), humanBytes(freed), i18n.t("action_log.files_removed", { count: count(files, "file") })],
      [i18n.t("action_log.refused_failed"), String(refused), refused ? i18n.t("action_log.changed_nothing") : i18n.t("action_log.everything_worked")],
    ];
    const fragment = document.createDocumentFragment();
    for (const [label, value, sub] of cards) {
      const card = document.createElement("div"); card.className = "card";
      for (const [className, text] of [["label", label], ["value", value], ["sub muted", sub]]) {
        const node = document.createElement("div"); node.className = className; node.textContent = text; card.appendChild(node);
      }
      fragment.appendChild(card);
    }
    target.replaceChildren(fragment);
  },
};
