/* Core card package: static shell copy for trusted Zogograph servers. */
window.ZogographSystemPeersShellCard = {
  manifest: Object.freeze({
    id: 'core-system-peers-shell',
    locales: ['core-system-peers-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-peers-shell.title': 'Trusted Zogograph servers',
      }),
      es: Object.freeze({
        'core-system-peers-shell.title': 'Servidores Zogograph de confianza',
      }),
      hi: Object.freeze({
        'core-system-peers-shell.title': 'विश्वसनीय Zogograph सर्वर',
      }),
      ar: Object.freeze({
        'core-system-peers-shell.title': 'خوادم Zogograph الموثوقة',
      }),
      zh: Object.freeze({
        'core-system-peers-shell.title': '受信任的 Zogograph 服务器',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-peers-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-peers-shell.${key}`],
    );
  },
  render() {},
};
