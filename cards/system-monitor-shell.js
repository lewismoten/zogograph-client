/* Core card package: static shell copy for monitor diagnostics. */
window.ZogographSystemMonitorShellCard = {
  manifest: Object.freeze({
    id: 'core-system-monitor-shell',
    locales: ['core-system-monitor-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-monitor-shell.title': 'The monitor itself',
      }),
      es: Object.freeze({
        'core-system-monitor-shell.title': 'El propio monitor',
      }),
      hi: Object.freeze({ 'core-system-monitor-shell.title': 'मॉनिटर स्वयं' }),
      ar: Object.freeze({ 'core-system-monitor-shell.title': 'المراقب نفسه' }),
      zh: Object.freeze({ 'core-system-monitor-shell.title': '监控器自身' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-monitor-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-monitor-shell.${key}`],
    );
  },
  render() {},
};
