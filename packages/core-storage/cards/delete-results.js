/* Built-in storage package: individual path deletion state and notices. */
const StorageDeleteResultsCard = {
  manifest: Object.freeze({
    id: 'core-storage-delete-results',
    locales: ['core-storage-delete-results.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-delete-results.delete_directory_confirm':
          'Delete {{path}} and everything inside it? That is {{size}} in {{files}} files, and it cannot be undone.',
        'core-storage-delete-results.delete_file_confirm':
          'Delete {{path}}? That is {{size}}, and it cannot be undone.',
        'core-storage-delete-results.deleted': 'deleted',
        'core-storage-delete-results.deleted_row_help':
          'Deleted {{at}}. Still listed because the last scan found it; the next scan will drop it.',
        'core-storage-delete-results.deleted_not_freed':
          'Deleted, but nothing was freed: {{links}} still point at those {{size}}.',
        'core-storage-delete-results.removed_directory':
          'Removed {{size}} in {{files}}.',
        'core-storage-delete-results.removed_directory_with_skipped':
          'Removed {{size}} in {{files}}. {{skipped}} could not be removed.',
        'core-storage-delete-results.removed_file': 'Removed {{size}}.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/delete-results.json',
      hi: 'packages/core-storage/locales/hi/delete-results.json',
      ar: 'packages/core-storage/locales/ar/delete-results.json',
      zh: 'packages/core-storage/locales/zh/delete-results.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-storage-delete-results.${key}`];
    return i18n.t(`core-storage-delete-results.${key}`, values, fallback);
  },
};
Zogograph.cards.register(StorageDeleteResultsCard);
