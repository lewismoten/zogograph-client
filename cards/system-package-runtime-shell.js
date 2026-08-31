/* Core card package: static shell copy for executable package runtime. */
window.ZogographSystemPackageRuntimeShellCard = {
  manifest: Object.freeze({
    id: 'core-system-package-runtime-shell',
    locales: ['core-system-package-runtime-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-package-runtime-shell.title': 'Executable package runtime',
      }),
      es: Object.freeze({
        'core-system-package-runtime-shell.title':
          'Entorno de paquetes ejecutables',
      }),
      hi: Object.freeze({
        'core-system-package-runtime-shell.title':
          'निष्पादन योग्य पैकेज रनटाइम',
      }),
      ar: Object.freeze({
        'core-system-package-runtime-shell.title':
          'وقت تشغيل الحزم القابلة للتنفيذ',
      }),
      zh: Object.freeze({
        'core-system-package-runtime-shell.title': '可执行软件包运行时',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-package-runtime-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-package-runtime-shell.${key}`],
    );
  },
  render() {},
};
