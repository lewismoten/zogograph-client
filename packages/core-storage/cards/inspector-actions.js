/* Built-in storage package: action panel for an inspected storage path. */
const StorageInspectorActionsCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-actions',
    locales: ['core-storage-inspector-actions.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-actions.delete_directory':
          'Delete this directory and everything in it',
        'core-storage-inspector-actions.delete_file': 'Delete this file',
        'core-storage-inspector-actions.what_you_can_do':
          'What you can do with it',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/inspector-actions.json',
      hi: 'packages/core-storage/locales/hi/inspector-actions.json',
      ar: 'packages/core-storage/locales/ar/inspector-actions.json',
      zh: 'packages/core-storage/locales/zh/inspector-actions.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-inspector-actions.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-inspector-actions.${key}`],
    );
  },
};
Zogograph.cards.register(StorageInspectorActionsCard);
