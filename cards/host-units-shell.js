/* Core card package: page shell for the complete host service list. */
window.ZogographHostUnitsShellCard = {
  manifest: Object.freeze({
    id: 'core-host-units-shell',
    locales: ['core-host-units-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-host-units-shell.title': 'Every service' }),
      es: Object.freeze({
        'core-host-units-shell.title': 'Todos los servicios',
      }),
      hi: Object.freeze({ 'core-host-units-shell.title': 'हर सेवा' }),
      ar: Object.freeze({ 'core-host-units-shell.title': 'كل خدمة' }),
      zh: Object.freeze({ 'core-host-units-shell.title': '每项服务' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-units-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-units-shell.${key}`],
    );
  },
  render() {},
};
