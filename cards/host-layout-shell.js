/* Core card package: page shell for the host filesystem layout. */
window.ZogographHostLayoutShellCard = {
  manifest: Object.freeze({
    id: 'core-host-layout-shell',
    locales: ['core-host-layout-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-layout-shell.title': 'Filesystem layout',
      }),
      es: Object.freeze({
        'core-host-layout-shell.title': 'Disposición de sistemas de archivos',
      }),
      hi: Object.freeze({
        'core-host-layout-shell.title': 'फ़ाइलसिस्टम विन्यास',
      }),
      ar: Object.freeze({
        'core-host-layout-shell.title': 'تخطيط نظام الملفات',
      }),
      zh: Object.freeze({ 'core-host-layout-shell.title': '文件系统布局' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-layout-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-layout-shell.${key}`],
    );
  },
  render() {},
};
