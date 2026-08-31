/* Core card package: page shell for the CageFS skeleton view. */
window.ZogographCagefsShellCard = {
  manifest: Object.freeze({
    id: 'core-cagefs-shell',
    locales: ['core-cagefs-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-cagefs-shell.title': 'The CageFS skeleton' }),
      es: Object.freeze({
        'core-cagefs-shell.title': 'El esqueleto de CageFS',
      }),
      hi: Object.freeze({ 'core-cagefs-shell.title': 'CageFS स्केलेटन' }),
      ar: Object.freeze({ 'core-cagefs-shell.title': 'هيكل CageFS' }),
      zh: Object.freeze({ 'core-cagefs-shell.title': 'CageFS 骨架' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cagefs-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-cagefs-shell.${key}`],
    );
  },
  render() {},
};
