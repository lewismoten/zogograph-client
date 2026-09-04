const StorageMeasuringCard = {
  manifest: Object.freeze({
    id: 'core-storage-measuring',
    locales: ['core-storage-measuring.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-storage-measuring.label': 'Measuring…' }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/measuring.json',
      hi: 'packages/core-storage/locales/hi/measuring.json',
      ar: 'packages/core-storage/locales/ar/measuring.json',
      zh: 'packages/core-storage/locales/zh/measuring.json',
    }),
  }),
  render(_input, { i18n }) {
    return i18n.t('core-storage-measuring.label', {}, 'Measuring…');
  },
};
Zogograph.cards.register(StorageMeasuringCard);
