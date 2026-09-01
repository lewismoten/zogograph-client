/* Core card package: Assistant data navigation label. */
window.ZogographAiDataNavigationCard = {
  manifest: Object.freeze({
    id: 'core-ai-data-navigation',
    locales: ['core-ai-data-navigation.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-data-navigation.label': 'Assistant data',
      }),
      es: Object.freeze({
        'core-ai-data-navigation.label': 'Datos del asistente',
      }),
      hi: Object.freeze({
        'core-ai-data-navigation.label': 'सहायक डेटा',
      }),
      ar: Object.freeze({
        'core-ai-data-navigation.label': 'بيانات المساعد',
      }),
      zh: Object.freeze({
        'core-ai-data-navigation.label': '助手数据',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    const id = `core-ai-data-navigation.${key}`;
    return i18n.t(id, {}, this.manifest.catalogs.en[id]);
  },
};
