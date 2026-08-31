/* Core card package: static shell copy for executable package runtime. */
window.ZogographSystemPackageRuntimeShellCard = {
  manifest: Object.freeze({
    id: 'core-system-package-runtime-shell',
    locales: ['core-system-package-runtime-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-package-runtime-shell.title': 'Executable package runtime',
        'core-system-package-runtime-shell.help':
          'Signed packages are reviewed here before any future runtime can use them. Enabling records explicit capability grants and private-package settings; it does not execute package PHP in this Zogograph release.',
      }),
      es: Object.freeze({
        'core-system-package-runtime-shell.title':
          'Entorno de paquetes ejecutables',
        'core-system-package-runtime-shell.help':
          'Aquí se revisan paquetes firmados antes de que cualquier entorno futuro pueda usarlos. Activarlos registra concesiones explícitas de capacidades y ajustes de paquetes privados; no ejecuta PHP de paquetes en esta versión de Zogograph.',
      }),
      hi: Object.freeze({
        'core-system-package-runtime-shell.title':
          'निष्पादन योग्य पैकेज रनटाइम',
        'core-system-package-runtime-shell.help':
          'किसी भविष्य रनटाइम के उपयोग से पहले हस्ताक्षरित पैकेज यहाँ समीक्षा किए जाते हैं। सक्षम करना स्पष्ट क्षमता अनुदान और निजी-पैकेज सेटिंग दर्ज करता है; इस Zogograph रिलीज़ में पैकेज PHP निष्पादित नहीं होता।',
      }),
      ar: Object.freeze({
        'core-system-package-runtime-shell.title':
          'وقت تشغيل الحزم القابلة للتنفيذ',
        'core-system-package-runtime-shell.help':
          'تُراجع الحزم الموقعة هنا قبل أن يستخدمها أي وقت تشغيل مستقبلي. يسجل التمكين منح قدرات صريحة وإعدادات الحزم الخاصة؛ ولا ينفذ PHP الحزمة في إصدار Zogograph هذا.',
      }),
      zh: Object.freeze({
        'core-system-package-runtime-shell.title': '可执行软件包运行时',
        'core-system-package-runtime-shell.help':
          '任何未来运行时使用签名软件包之前都会在此进行审查。启用会记录明确的功能授权和私有软件包设置；本 Zogograph 版本不会执行软件包 PHP。',
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
