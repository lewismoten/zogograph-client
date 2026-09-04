/* Built-in storage package: offender-table size and hard-link context. */
const StorageOffenderSizeCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-size',
    locales: ['core-storage-offender-size.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-size.last_seen': 'Size when last seen',
        'core-storage-offender-size.link_count': ' · {{count, number}} links',
        'core-storage-offender-size.link_count_help':
          'This file has {{count, number}} names. Removing one frees nothing until the last one goes.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/offender-size.json',
      hi: 'packages/core-storage/locales/hi/offender-size.json',
      ar: 'packages/core-storage/locales/ar/offender-size.json',
      zh: 'packages/core-storage/locales/zh/offender-size.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-offender-size.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-offender-size.${key}`],
    );
  },
};
Zogograph.cards.register(StorageOffenderSizeCard);
