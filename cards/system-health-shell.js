/* Core card package: static shell copy for system health. */
window.ZogographSystemHealthShellCard = {
  manifest: Object.freeze({
    id: 'core-system-health-shell',
    locales: ['core-system-health-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-health-shell.title': 'Health',
        'core-system-health-shell.help':
          'What this install can and cannot do. The first three are the usual reason a fresh deployment returns errors.',
      }),
      es: Object.freeze({
        'core-system-health-shell.title': 'Estado',
        'core-system-health-shell.help':
          'Lo que esta instalación puede y no puede hacer. Los tres primeros son la razón habitual por la que una implementación nueva devuelve errores.',
      }),
      hi: Object.freeze({
        'core-system-health-shell.title': 'स्वास्थ्य',
        'core-system-health-shell.help':
          'यह इंस्टॉलेशन क्या कर सकता है और क्या नहीं। पहले तीन नए डिप्लॉयमेंट के त्रुटियाँ लौटाने का सामान्य कारण हैं।',
      }),
      ar: Object.freeze({
        'core-system-health-shell.title': 'الحالة الصحية',
        'core-system-health-shell.help':
          'ما يستطيع هذا التثبيت فعله وما لا يستطيع. الثلاثة الأولى هي السبب المعتاد لعودة أخطاء من نشر جديد.',
      }),
      zh: Object.freeze({
        'core-system-health-shell.title': '运行状况',
        'core-system-health-shell.help':
          '此安装能够做什么以及不能做什么。前三项通常是全新部署返回错误的原因。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-health-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-health-shell.${key}`],
    );
  },
  render() {},
};
