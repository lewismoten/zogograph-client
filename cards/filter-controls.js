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
        'core-filter-controls.any': 'any',
        'core-filter-controls.clear': 'Clear the filters',
      }),
      es: Object.freeze({
        'core-filter-controls.all': 'Todos',
        'core-filter-controls.category': 'Categoría',
        'core-filter-controls.kind': 'Tipo',
        'core-filter-controls.show': 'Mostrar',
        'core-filter-controls.any': 'cualquiera',
        'core-filter-controls.clear': 'Borrar los filtros',
      }),
      hi: Object.freeze({
        'core-filter-controls.all': 'सभी',
        'core-filter-controls.category': 'श्रेणी',
        'core-filter-controls.kind': 'प्रकार',
        'core-filter-controls.show': 'दिखाएँ',
        'core-filter-controls.any': 'कोई भी',
        'core-filter-controls.clear': 'फ़िल्टर साफ़ करें',
      }),
      ar: Object.freeze({
        'core-filter-controls.all': 'الكل',
        'core-filter-controls.category': 'الفئة',
        'core-filter-controls.kind': 'النوع',
        'core-filter-controls.show': 'اعرض',
        'core-filter-controls.any': 'أيّ',
        'core-filter-controls.clear': 'امسح عوامل التصفية',
      }),
      zh: Object.freeze({
        'core-filter-controls.all': '全部',
        'core-filter-controls.category': '类别',
        'core-filter-controls.kind': '类型',
        'core-filter-controls.show': '显示',
        'core-filter-controls.any': '任意',
        'core-filter-controls.clear': '清除筛选条件',
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
