/* Built-in storage package: inspector breakdown-chart empty state. */
const StorageInspectorBreakdownCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-breakdown',
    locales: ['core-storage-inspector-breakdown.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'What is taking the space',
        'core-storage-inspector-breakdown.empty': 'Nothing to show',
        'core-storage-inspector-breakdown.title': 'What it is made of',
        'core-storage-inspector-breakdown.category_trash': 'Trash',
        'core-storage-inspector-breakdown.category_logs': 'Logs',
        'core-storage-inspector-breakdown.category_temp': 'Temp',
        'core-storage-inspector-breakdown.kind_files': 'Files',
        'core-storage-inspector-breakdown.kind_directories': 'Directories',
        'core-storage-inspector-breakdown.sort_size': 'Largest',
        'core-storage-inspector-breakdown.sort_growth':
          'Grew most since last scan',
        'core-storage-inspector-breakdown.sort_hour':
          'Grew most in the last hour',
        'core-storage-inspector-breakdown.sort_today': 'Grew most today',
        'core-storage-inspector-breakdown.sort_week': 'Grew most in 7 days',
        'core-storage-inspector-breakdown.sort_new': 'Newly appeared',
        'core-storage-inspector-breakdown.sort_deleted': 'Gone since last scan',
        'core-storage-inspector-breakdown.masked_names':
          'Names are hidden until you sign in. The sizes and the growth are real.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/inspector-breakdown.json',
      hi: 'packages/core-storage/locales/hi/inspector-breakdown.json',
      ar: 'packages/core-storage/locales/ar/inspector-breakdown.json',
      zh: 'packages/core-storage/locales/zh/inspector-breakdown.json',
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
Zogograph.cards.register(StorageInspectorBreakdownCard);
