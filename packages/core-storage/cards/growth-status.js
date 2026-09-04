/* Built-in storage package: offender-list size change descriptions. */
const StorageGrowthStatusCard = {
  manifest: Object.freeze({
    id: 'core-storage-growth-status',
    locales: ['core-storage-growth-status.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-growth-status.none':
          'No measurable change since the previous scan.',
        'core-storage-growth-status.grew':
          'Grew by {{bytes}} since the previous scan — this is taking space.',
        'core-storage-growth-status.shrank':
          'Shrank by {{bytes}} since the previous scan — this gave space back.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/growth-status.json',
      hi: 'packages/core-storage/locales/hi/growth-status.json',
      ar: 'packages/core-storage/locales/ar/growth-status.json',
      zh: 'packages/core-storage/locales/zh/growth-status.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-growth-status.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-growth-status.${key}`],
    );
  },
};
Zogograph.cards.register(StorageGrowthStatusCard);
