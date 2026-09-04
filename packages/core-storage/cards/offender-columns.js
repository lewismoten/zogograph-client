/* Built-in storage package: offender-table core column labels. */
const StorageOffenderColumnsCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-columns',
    locales: ['core-storage-offender-columns.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-columns.path': 'Path',
        'core-storage-offender-columns.kind': 'Kind',
        'core-storage-offender-columns.directory': 'directory',
        'core-storage-offender-columns.file': 'file',
        'core-storage-offender-columns.category': 'Category',
        'core-storage-offender-columns.files': 'Files',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/offender-columns.json',
      hi: 'packages/core-storage/locales/hi/offender-columns.json',
      ar: 'packages/core-storage/locales/ar/offender-columns.json',
      zh: 'packages/core-storage/locales/zh/offender-columns.json',
    }),
  }),
  render({ key }, { i18n }) {
    return i18n.t(
      `core-storage-offender-columns.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-offender-columns.${key}`],
    );
  },
};
Zogograph.cards.register(StorageOffenderColumnsCard);
