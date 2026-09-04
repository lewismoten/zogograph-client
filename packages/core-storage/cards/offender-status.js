/* Built-in storage package: offender-list status column labels. */
const StorageOffenderStatusCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-status',
    locales: ['core-storage-offender-status.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-status.what_happened': 'What happened',
        'core-storage-offender-status.first_seen': 'First seen',
        'core-storage-offender-status.gone': 'Gone',
        'core-storage-offender-status.new': 'New',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/offender-status.json',
      hi: 'packages/core-storage/locales/hi/offender-status.json',
      ar: 'packages/core-storage/locales/ar/offender-status.json',
      zh: 'packages/core-storage/locales/zh/offender-status.json',
    }),
  }),
  render({ key }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-storage-offender-status.${key}`];
    return i18n.t(`core-storage-offender-status.${key}`, {}, fallback);
  },
};
Zogograph.cards.register(StorageOffenderStatusCard);
