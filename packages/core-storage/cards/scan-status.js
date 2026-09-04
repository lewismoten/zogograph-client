/* Built-in storage package: scan-state labels. */
const StorageScanStatusCard = {
  manifest: Object.freeze({
    id: 'core-storage-scan-status',
    locales: ['core-storage-scan-status.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-scan-status.title': 'The last scan',
        'core-storage-scan-status.now': 'Scan now',
        'core-storage-scan-status.running': 'Scanning… ({{elapsed}})',
        'core-storage-scan-status.finished': 'Scan finished.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/scan-status.json',
      hi: 'packages/core-storage/locales/hi/scan-status.json',
      ar: 'packages/core-storage/locales/ar/scan-status.json',
      zh: 'packages/core-storage/locales/zh/scan-status.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-storage-scan-status.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-scan-status.${key}`],
    );
  },
  render(input, { i18n }) {
    const { elapsed = '', state } = input;
    const fallback = {
      now: 'Scan now',
      running: `Scanning… (${elapsed})`,
      finished: 'Scan finished.',
    };
    return i18n.t(
      `core-storage-scan-status.${state}`,
      { elapsed },
      fallback[state],
    );
  },
};
Zogograph.cards.register(StorageScanStatusCard);
