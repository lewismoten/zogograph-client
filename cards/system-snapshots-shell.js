/* Core card package: static shell copy for database copies. */
window.ZogographSystemSnapshotsShellCard = {
  manifest: Object.freeze({
    id: 'core-system-snapshots-shell',
    locales: ['core-system-snapshots-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-snapshots-shell.title': 'Database copies',
      }),
      es: Object.freeze({
        'core-system-snapshots-shell.title': 'Copias de bases de datos',
      }),
      hi: Object.freeze({
        'core-system-snapshots-shell.title': 'डेटाबेस प्रतियाँ',
      }),
      ar: Object.freeze({
        'core-system-snapshots-shell.title': 'نسخ قواعد البيانات',
      }),
      zh: Object.freeze({ 'core-system-snapshots-shell.title': '数据库副本' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-snapshots-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-snapshots-shell.${key}`],
    );
  },
  render() {},
};
