/* Core card package: page shell for host-wide disk activity. */
window.ZogographHostIoShellCard = {
  manifest: Object.freeze({
    id: 'core-host-io-shell',
    locales: ['core-host-io-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-io-shell.title': 'How hard the disks are working',
      }),
      es: Object.freeze({
        'core-host-io-shell.title': 'Cuánto están trabajando los discos',
      }),
      hi: Object.freeze({
        'core-host-io-shell.title': 'डिस्क कितनी मेहनत कर रही हैं',
      }),
      ar: Object.freeze({ 'core-host-io-shell.title': 'مدى انشغال الأقراص' }),
      zh: Object.freeze({ 'core-host-io-shell.title': '磁盘工作负载' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-io-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-io-shell.${key}`],
    );
  },
  render() {},
};
