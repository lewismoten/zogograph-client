/* Core card package: shared storage loading state. */
window.ZogographStorageMeasuringCard = {
  manifest: Object.freeze({
    id: 'core-storage-measuring',
    locales: ['core-storage-measuring.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-storage-measuring.label': 'Measuring…' }),
      es: Object.freeze({ 'core-storage-measuring.label': 'Midiendo…' }),
      hi: Object.freeze({ 'core-storage-measuring.label': 'मापा जा रहा है…' }),
      ar: Object.freeze({ 'core-storage-measuring.label': 'جارٍ القياس…' }),
      zh: Object.freeze({ 'core-storage-measuring.label': '正在测量…' }),
    }),
  }),
  render(_input, { i18n }) {
    return i18n.t('core-storage-measuring.label', {}, 'Measuring…');
  },
};
