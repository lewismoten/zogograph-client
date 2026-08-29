/* Core card package: current CPU findings from the host collector. */
window.ZogographCpuFindingsCard = {
  manifest: Object.freeze({
    id: "core-cpu-findings", locales: ["cpu."],
    helpers: ["target", "findingRow", "i18n"],
  }),
  render(findings, helpers) {
    const { target, findingRow, i18n } = helpers;
    if (!target) return;
    if (!findings.length) {
      target.replaceChildren(Object.assign(document.createElement("p"), {
        className: "muted", textContent: i18n.t("cpu.nothing_to_report"),
      }));
      return;
    }
    target.replaceChildren(...findings.map(findingRow));
  },
};
