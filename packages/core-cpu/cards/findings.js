/* Core card package: current CPU findings from the host collector. */
const CpuFindingsCard = {
  manifest: Object.freeze({
    id: 'core-cpu-findings',
    locales: ['core-cpu-findings.'],
    owner: 'core-cpu',
    helpers: ['target', 'findingRow', 'ui', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-findings.title': 'What is true right now',
        'core-cpu-findings.help':
          'Every line below is a measurement taken from this machine, with a link to the page it came from. Nothing here infers a cause it cannot see, and ‘nothing found’ is said in those words rather than as a clean bill of health.',
        'core-cpu-findings.nothing_to_report': 'Nothing to report.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-cpu/locales/es/findings.json',
      hi: 'packages/core-cpu/locales/hi/findings.json',
      ar: 'packages/core-cpu/locales/ar/findings.json',
      zh: 'packages/core-cpu/locales/zh/findings.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-findings.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-findings.${key}`],
    );
  },
  render(findings, helpers) {
    const { target, findingRow, ui, i18n } = helpers;
    if (!target) return;
    if (!findings.length) {
      target.replaceChildren(
        ui.empty(
          i18n.t(
            'core-cpu-findings.nothing_to_report',
            {},
            'Nothing to report.',
          ),
        ),
      );
      return;
    }
    target.replaceChildren(...findings.map(findingRow));
  },
};
Zogograph.cards.register(CpuFindingsCard);
