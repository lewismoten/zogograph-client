/* Core card package: image preview section for an inspected path. */
window.ZogographStorageInspectorPreviewCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-preview',
    locales: ['core-storage-inspector-preview.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-preview.title': 'Preview',
        'core-storage-inspector-preview.dimensions':
          '{{format}}, {{width, number}}×{{height, number}} pixels, {{size}} sent in full and scaled here.',
        'core-storage-inspector-preview.size': '{{format}}, {{size}}.',
      }),
      es: Object.freeze({
        'core-storage-inspector-preview.title': 'Vista previa',
        'core-storage-inspector-preview.dimensions':
          '{{format}}, {{width, number}}×{{height, number}} píxeles; se enviaron {{size}} completos y se escalaron aquí.',
        'core-storage-inspector-preview.size': '{{format}}, {{size}}.',
      }),
      hi: Object.freeze({
        'core-storage-inspector-preview.title': 'पूर्वावलोकन',
        'core-storage-inspector-preview.dimensions':
          '{{format}}, {{width, number}}×{{height, number}} पिक्सेल, {{size}} पूरा भेजा गया और यहाँ स्केल किया गया।',
        'core-storage-inspector-preview.size': '{{format}}, {{size}}।',
      }),
      ar: Object.freeze({
        'core-storage-inspector-preview.title': 'معاينة',
        'core-storage-inspector-preview.dimensions':
          '{{format}}، {{width, number}}×{{height, number}} بكسل، أُرسل {{size}} كاملًا وحُجّم هنا.',
        'core-storage-inspector-preview.size': '{{format}}، {{size}}.',
      }),
      zh: Object.freeze({
        'core-storage-inspector-preview.title': '预览',
        'core-storage-inspector-preview.dimensions':
          '{{format}}，{{width, number}}×{{height, number}} 像素，完整发送 {{size}} 并在此缩放。',
        'core-storage-inspector-preview.size': '{{format}}，{{size}}。',
      }),
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
