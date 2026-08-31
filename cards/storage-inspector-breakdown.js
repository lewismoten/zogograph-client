/* Core card package: storage inspector breakdown-chart empty state. */
window.ZogographStorageInspectorBreakdownCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-breakdown',
    locales: ['core-storage-inspector-breakdown.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'What is taking the space',
        'core-storage-inspector-breakdown.empty': 'Nothing to show',
        'core-storage-inspector-breakdown.title': 'What it is made of',
      }),
      es: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'Qué está ocupando el espacio',
        'core-storage-inspector-breakdown.empty': 'Nada que mostrar',
        'core-storage-inspector-breakdown.title': 'De qué está hecho',
      }),
      hi: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title': 'कौन जगह ले रहा है',
        'core-storage-inspector-breakdown.empty': 'दिखाने के लिए कुछ नहीं',
        'core-storage-inspector-breakdown.title': 'यह किससे बना है',
      }),
      ar: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'ما الذي يستهلك المساحة',
        'core-storage-inspector-breakdown.empty': 'لا شيء لعرضه',
        'core-storage-inspector-breakdown.title': 'ممّ يتكون',
      }),
      zh: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          '哪些内容占用了空间',
        'core-storage-inspector-breakdown.empty': '没有可显示的内容',
        'core-storage-inspector-breakdown.title': '它由什么构成',
      }),
    }),
  }),
  render({ key = 'empty' } = {}, { i18n }) {
    return i18n.t(
      `core-storage-inspector-breakdown.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-inspector-breakdown.${key}`],
    );
  },
};
