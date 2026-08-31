/* Core card package: storage inspector breakdown-chart empty state. */
window.ZogographStorageInspectorBreakdownCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-breakdown',
    locales: ['core-storage-inspector-breakdown.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-breakdown.empty': 'Nothing to show',
      }),
      es: Object.freeze({
        'core-storage-inspector-breakdown.empty': 'Nada que mostrar',
      }),
      hi: Object.freeze({
        'core-storage-inspector-breakdown.empty': 'दिखाने के लिए कुछ नहीं',
      }),
      ar: Object.freeze({
        'core-storage-inspector-breakdown.empty': 'لا شيء لعرضه',
      }),
      zh: Object.freeze({
        'core-storage-inspector-breakdown.empty': '没有可显示的内容',
      }),
    }),
  }),
  render(_input, { i18n }) {
    return i18n.t(
      'core-storage-inspector-breakdown.empty',
      {},
      this.manifest.catalogs.en['core-storage-inspector-breakdown.empty'],
    );
  },
};
