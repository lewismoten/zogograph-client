/* Core card package: labels shared by dashboard category and type selectors. */
window.ZogographFilterControlsCard = {
  manifest: Object.freeze({
    id: 'core-filter-controls',
    locales: ['core-filter-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-filter-controls.all': 'All',
        'core-filter-controls.category': 'Category',
        'core-filter-controls.kind': 'Kind',
        'core-filter-controls.show': 'Show',
      }),
      es: Object.freeze({
        'core-filter-controls.all': 'Todos',
        'core-filter-controls.category': 'Categoría',
        'core-filter-controls.kind': 'Tipo',
        'core-filter-controls.show': 'Mostrar',
      }),
      hi: Object.freeze({
        'core-filter-controls.all': 'सभी',
        'core-filter-controls.category': 'श्रेणी',
        'core-filter-controls.kind': 'प्रकार',
        'core-filter-controls.show': 'दिखाएँ',
      }),
      ar: Object.freeze({
        'core-filter-controls.all': 'الكل',
        'core-filter-controls.category': 'الفئة',
        'core-filter-controls.kind': 'النوع',
        'core-filter-controls.show': 'اعرض',
      }),
      zh: Object.freeze({
        'core-filter-controls.all': '全部',
        'core-filter-controls.category': '类别',
        'core-filter-controls.kind': '类型',
        'core-filter-controls.show': '显示',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-filter-controls.${input.key}`,
      {},
      input.fallback,
    );
  },
};
