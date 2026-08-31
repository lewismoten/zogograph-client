/* Core card package: static shell copy for system health. */
window.ZogographSystemHealthShellCard = {
  manifest: Object.freeze({
    id: 'core-system-health-shell',
    locales: ['core-system-health-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-system-health-shell.title': 'Health' }),
      es: Object.freeze({ 'core-system-health-shell.title': 'Estado' }),
      hi: Object.freeze({ 'core-system-health-shell.title': 'स्वास्थ्य' }),
      ar: Object.freeze({ 'core-system-health-shell.title': 'الحالة الصحية' }),
      zh: Object.freeze({ 'core-system-health-shell.title': '运行状况' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-health-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-health-shell.${key}`],
    );
  },
  render() {},
};
