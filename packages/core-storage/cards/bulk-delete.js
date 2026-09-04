/* Built-in storage package: selected-path deletion controls and notices. */
const StorageBulkDeleteCard = {
  manifest: Object.freeze({
    id: 'core-storage-bulk-delete',
    locales: ['core-storage-bulk-delete.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-bulk-delete.select_row': 'Select {{name}}',
        'core-storage-bulk-delete.delete_selected': 'Delete selected',
        'core-storage-bulk-delete.delete_selected_count':
          'Delete {{count, number}} selected',
        'core-storage-bulk-delete.delete_permanently':
          'Delete {{count, number}} permanently?',
        'core-storage-bulk-delete.delete_more': '…and {{count, number}} more',
        'core-storage-bulk-delete.delete_size': 'That is {{bytes}}',
        'core-storage-bulk-delete.delete_recursive':
          'and {{count, number}} will be emptied recursively',
        'core-storage-bulk-delete.cannot_undo': 'This cannot be undone.',
        'core-storage-bulk-delete.removed':
          'Removed {{count, number}}, freeing {{bytes}}.',
        'core-storage-bulk-delete.remove_failed':
          '{{count, number}} could not be removed: {{detail}}',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/bulk-delete.json',
      hi: 'packages/core-storage/locales/hi/bulk-delete.json',
      ar: 'packages/core-storage/locales/ar/bulk-delete.json',
      zh: 'packages/core-storage/locales/zh/bulk-delete.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-storage-bulk-delete.${key}`];
    return i18n.t(`core-storage-bulk-delete.${key}`, values, fallback);
  },
};
Zogograph.cards.register(StorageBulkDeleteCard);
