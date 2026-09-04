/* Built-in storage package: account database maintenance status messages. */
const StorageOperationsCard = {
  manifest: Object.freeze({
    id: 'core-storage-operations',
    locales: ['core-storage-operations.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-operations.preparing_export':
          'Preparing the safe database export…',
        'core-storage-operations.rollup_result':
          '{{removed, number}} readings merged into {{compacted, number}}.',
        'core-storage-operations.rollup_protected':
          '{{count, number}} left at full detail because they sit beside an alert.',
        'core-storage-operations.rollup_space':
          'The space they used is inside the file until you reclaim it.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/operations.json',
      hi: 'packages/core-storage/locales/hi/operations.json',
      ar: 'packages/core-storage/locales/ar/operations.json',
      zh: 'packages/core-storage/locales/zh/operations.json',
    }),
  }),
  text(key, values, helpers) {
    const id = `core-storage-operations.${key}`;
    return helpers.i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
  render() {},
};
Zogograph.cards.register(StorageOperationsCard);
