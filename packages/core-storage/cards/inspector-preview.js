/* Built-in storage package: image preview section for an inspected path. */
const StorageInspectorPreviewCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-preview',
    locales: ['core-storage-inspector-preview.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-preview.title': 'Preview',
        'core-storage-inspector-preview.unavailable':
          'The preview could not be loaded.',
        'core-storage-inspector-preview.dimensions':
          '{{format}}, {{width, number}}×{{height, number}} pixels, {{size}} sent in full and scaled here.',
        'core-storage-inspector-preview.size': '{{format}}, {{size}}.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/inspector-preview.json',
      hi: 'packages/core-storage/locales/hi/inspector-preview.json',
      ar: 'packages/core-storage/locales/ar/inspector-preview.json',
      zh: 'packages/core-storage/locales/zh/inspector-preview.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-inspector-preview.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-inspector-preview.${key}`],
    );
  },
};
Zogograph.cards.register(StorageInspectorPreviewCard);
