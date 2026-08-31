/* Core card package: static shell copy for the marketplace page. */
window.ZogographSystemMarketplaceShellCard = {
  manifest: Object.freeze({
    id: 'core-system-marketplace-shell',
    locales: ['core-system-marketplace-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-marketplace-shell.title': 'Marketplace',
        'core-system-marketplace-shell.help':
          'Local package manifests only. Enabling never executes package code. A few fixed, core-owned capabilities may be authorized; requested capabilities remain visible for review.',
      }),
      es: Object.freeze({
        'core-system-marketplace-shell.title': 'Mercado',
        'core-system-marketplace-shell.help':
          'Solo manifiestos de paquetes locales. Activarlos nunca ejecuta código de paquetes. Pueden autorizarse unas pocas capacidades fijas propiedad del núcleo; las capacidades solicitadas siguen visibles para su revisión.',
      }),
      hi: Object.freeze({
        'core-system-marketplace-shell.title': 'मार्केटप्लेस',
        'core-system-marketplace-shell.help':
          'केवल स्थानीय पैकेज मैनिफेस्ट। सक्षम करना पैकेज कोड कभी निष्पादित नहीं करता। कुछ निश्चित, कोर-स्वामित्व वाली क्षमताएँ अधिकृत हो सकती हैं; अनुरोधित क्षमताएँ समीक्षा के लिए दिखाई देती रहती हैं।',
      }),
      ar: Object.freeze({
        'core-system-marketplace-shell.title': 'السوق',
        'core-system-marketplace-shell.help':
          'بيانات تعريف الحزم المحلية فقط. التمكين لا ينفذ رمز الحزمة مطلقًا. يجوز تفويض عدد قليل من القدرات الثابتة المملوكة للنواة؛ وتظل القدرات المطلوبة مرئية للمراجعة.',
      }),
      zh: Object.freeze({
        'core-system-marketplace-shell.title': '市场',
        'core-system-marketplace-shell.help':
          '仅限本地软件包清单。启用不会执行软件包代码。少数固定的核心自有功能可以获得授权；请求的功能仍会显示以供审查。',
      }),
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
