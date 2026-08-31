/* Core card package: page shell for symlink inspection and cleanup. */
window.ZogographSymlinkShellCard = {
  manifest: Object.freeze({
    id: 'core-symlink-shell',
    locales: ['core-symlink-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-shell.title': 'Symlinks that eat things',
      }),
      es: Object.freeze({
        'core-symlink-shell.title': 'Enlaces simbólicos que devoran cosas',
      }),
      hi: Object.freeze({
        'core-symlink-shell.title': 'चीज़ें खा जाने वाले सिमलिंक',
      }),
      ar: Object.freeze({
        'core-symlink-shell.title': 'روابط رمزية تلتهم الأشياء',
      }),
      zh: Object.freeze({ 'core-symlink-shell.title': '会吞噬内容的符号链接' }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-symlink-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-symlink-shell.${key}`],
    );
  },
  render() {},
};
