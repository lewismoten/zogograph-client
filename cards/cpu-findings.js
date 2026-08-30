/* Core card package: current CPU findings from the host collector. */
window.ZogographCpuFindingsCard = {
  manifest: Object.freeze({
    id: 'core-cpu-findings',
    locales: ['core-cpu-findings.'],
    helpers: ['target', 'findingRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-findings.nothing_to_report': 'Nothing to report.',
      }),
      es: Object.freeze({
        'core-cpu-findings.nothing_to_report': 'Nada que informar.',
      }),
      hi: Object.freeze({
        'core-cpu-findings.nothing_to_report': 'रिपोर्ट करने के लिए कुछ नहीं।',
      }),
      ar: Object.freeze({
        'core-cpu-findings.nothing_to_report': 'لا شيء للإبلاغ عنه.',
      }),
      zh: Object.freeze({
        'core-cpu-findings.nothing_to_report': '没有需要报告的内容。',
      }),
    }),
  }),
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
