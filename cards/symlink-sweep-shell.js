/* Core card package: page shell for removing broken symlinks below one path. */
window.ZogographSymlinkSweepShellCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-shell',
    locales: ['core-symlink-sweep-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-shell.title': 'Clear out a whole path',
      }),
      es: Object.freeze({
        'core-symlink-sweep-shell.title': 'Vaciar una ruta completa',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-shell.title': 'पूरा पथ साफ़ करें',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-shell.title': 'أفرغ مسارًا كاملًا',
      }),
      zh: Object.freeze({ 'core-symlink-sweep-shell.title': '清除整条路径' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-symlink-sweep-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-symlink-sweep-shell.${key}`],
    );
  },
  render() {},
};
