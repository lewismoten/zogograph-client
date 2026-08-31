/* Core card package: static shell copy for the marketplace page. */
window.ZogographSystemMarketplaceShellCard = {
  manifest: Object.freeze({
    id: 'core-system-marketplace-shell',
    locales: ['core-system-marketplace-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-marketplace-shell.title': 'Marketplace',
      }),
      es: Object.freeze({ 'core-system-marketplace-shell.title': 'Mercado' }),
      hi: Object.freeze({
        'core-system-marketplace-shell.title': 'मार्केटप्लेस',
      }),
      ar: Object.freeze({ 'core-system-marketplace-shell.title': 'السوق' }),
      zh: Object.freeze({ 'core-system-marketplace-shell.title': '市场' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-marketplace-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-marketplace-shell.${key}`],
    );
  },
  render() {},
};
