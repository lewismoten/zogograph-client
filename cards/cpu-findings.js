/* Core card package: current CPU findings from the host collector. */
window.ZogographCpuFindingsCard = {
  manifest: Object.freeze({
    id: 'core-cpu-findings',
    locales: ['core-cpu-findings.'],
    helpers: ['target', 'findingRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-findings.title': 'What is true right now',
        'core-cpu-findings.nothing_to_report': 'Nothing to report.',
      }),
      es: Object.freeze({
        'core-cpu-findings.title': 'Qué es cierto ahora mismo',
        'core-cpu-findings.nothing_to_report': 'Nada que informar.',
      }),
      hi: Object.freeze({
        'core-cpu-findings.title': 'अभी क्या सच है',
        'core-cpu-findings.nothing_to_report': 'रिपोर्ट करने के लिए कुछ नहीं।',
      }),
      ar: Object.freeze({
        'core-cpu-findings.title': 'ما هو صحيح الآن',
        'core-cpu-findings.nothing_to_report': 'لا شيء للإبلاغ عنه.',
      }),
      zh: Object.freeze({
        'core-cpu-findings.title': '此刻确定的事实',
        'core-cpu-findings.nothing_to_report': '没有需要报告的内容。',
      }),
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
    const { target, findingRow, i18n } = helpers;
    if (!target) return;
    if (!findings.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: i18n.t(
            'core-cpu-findings.nothing_to_report',
            {},
            'Nothing to report.',
          ),
        }),
      );
      return;
    }
    target.replaceChildren(...findings.map(findingRow));
  },
};
