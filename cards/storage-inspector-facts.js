/* Core card package: type row in the inspected-path facts grid. */
window.ZogographStorageInspectorFactsCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-facts',
    locales: ['core-storage-inspector-facts.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-facts.kind': 'Kind',
        'core-storage-inspector-facts.directory': 'directory',
      }),
      es: Object.freeze({
        'core-storage-inspector-facts.kind': 'Tipo',
        'core-storage-inspector-facts.directory': 'directorio',
      }),
      hi: Object.freeze({
        'core-storage-inspector-facts.kind': 'प्रकार',
        'core-storage-inspector-facts.directory': 'डायरेक्टरी',
      }),
      ar: Object.freeze({
        'core-storage-inspector-facts.kind': 'النوع',
        'core-storage-inspector-facts.directory': 'مجلد',
      }),
      zh: Object.freeze({
        'core-storage-inspector-facts.kind': '类型',
        'core-storage-inspector-facts.directory': '目录',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    return i18n.t(
      `core-storage-inspector-facts.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-inspector-facts.${key}`],
    );
  },
};
