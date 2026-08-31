/* Core card package: static shell copy for data-protection settings. */
window.ZogographSystemEncryptionShellCard = {
  manifest: Object.freeze({
    id: 'core-system-encryption-shell',
    locales: ['core-system-encryption-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-encryption-shell.title': 'Data protection',
      }),
      es: Object.freeze({
        'core-system-encryption-shell.title': 'Protección de datos',
      }),
      hi: Object.freeze({
        'core-system-encryption-shell.title': 'डेटा सुरक्षा',
      }),
      ar: Object.freeze({
        'core-system-encryption-shell.title': 'حماية البيانات',
      }),
      zh: Object.freeze({ 'core-system-encryption-shell.title': '数据保护' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-encryption-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-encryption-shell.${key}`],
    );
  },
  render() {},
};
