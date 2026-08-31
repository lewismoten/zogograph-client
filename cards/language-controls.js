/* Core card package: language-inspection controls in the persistent header. */
window.ZogographLanguageControlsCard = {
  manifest: Object.freeze({
    id: 'core-language-controls',
    locales: ['core-language-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-language-controls.keys': 'Show translation keys',
        'core-language-controls.highlight': 'Highlight translation sources',
      }),
      es: Object.freeze({
        'core-language-controls.keys': 'Mostrar claves de traducción',
        'core-language-controls.highlight': 'Resaltar fuentes de traducción',
      }),
      hi: Object.freeze({
        'core-language-controls.keys': 'अनुवाद कुंजियाँ दिखाएँ',
        'core-language-controls.highlight': 'अनुवाद स्रोत हाइलाइट करें',
      }),
      ar: Object.freeze({
        'core-language-controls.keys': 'إظهار مفاتيح الترجمة',
        'core-language-controls.highlight': 'ميّز مصادر الترجمة',
      }),
      zh: Object.freeze({
        'core-language-controls.keys': '显示翻译键',
        'core-language-controls.highlight': '突出显示翻译来源',
      }),
    }),
  }),
  render(_input, helpers) {
    return {
      keys: helpers.i18n.t(
        'core-language-controls.keys',
        {},
        'Show translation keys',
      ),
      highlight: helpers.i18n.t(
        'core-language-controls.highlight',
        {},
        'Highlight translation sources',
      ),
    };
  },
};
