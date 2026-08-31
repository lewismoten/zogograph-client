/* Core card package: static headings for the alerts page. */
window.ZogographAlertsShellCard = {
  manifest: Object.freeze({
    id: 'core-alerts-shell',
    locales: ['core-alerts-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-alerts-shell.timeline_title': 'When alerts fired',
        'core-alerts-shell.title': 'Alerts',
        'core-alerts-shell.incident_title': 'Why this fired',
      }),
      es: Object.freeze({
        'core-alerts-shell.timeline_title': 'Cuándo se activaron las alertas',
        'core-alerts-shell.title': 'Alertas',
        'core-alerts-shell.incident_title': 'Por qué se activó',
      }),
      hi: Object.freeze({
        'core-alerts-shell.timeline_title': 'अलर्ट कब चालू हुए',
        'core-alerts-shell.title': 'अलर्ट',
        'core-alerts-shell.incident_title': 'यह क्यों चला',
      }),
      ar: Object.freeze({
        'core-alerts-shell.timeline_title': 'متى أُطلقت التنبيهات',
        'core-alerts-shell.title': 'التنبيهات',
        'core-alerts-shell.incident_title': 'لماذا أُطلق هذا',
      }),
      zh: Object.freeze({
        'core-alerts-shell.timeline_title': '警报何时触发',
        'core-alerts-shell.title': '警报',
        'core-alerts-shell.incident_title': '为何触发',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-alerts-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-alerts-shell.${key}`],
    );
  },
  render() {},
};
