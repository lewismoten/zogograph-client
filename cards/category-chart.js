/* Core card package: watched storage categories over time. */
window.ZogographCategoryChart = {
  manifest: Object.freeze({
    id: 'core-category-chart',
    locales: ['core-category-chart.'],
    helpers: ['canvas', 'drawLineChart', 'bytesAxis', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-category-chart.title': 'Categories over time',
        'core-category-chart.empty': 'No scan has finished in this range yet',
      }),
      es: Object.freeze({
        'core-category-chart.title': 'Categorías a lo largo del tiempo',
        'core-category-chart.empty':
          'Aún no ha terminado ningún análisis en este intervalo',
      }),
      hi: Object.freeze({
        'core-category-chart.title': 'समय के साथ श्रेणियाँ',
        'core-category-chart.empty':
          'इस दायरे में अभी कोई स्कैन पूरा नहीं हुआ है',
      }),
      ar: Object.freeze({
        'core-category-chart.title': 'الفئات عبر الزمن',
        'core-category-chart.empty': 'لم ينته أي فحص في هذا النطاق بعد',
      }),
      zh: Object.freeze({
        'core-category-chart.title': '随时间变化的类别',
        'core-category-chart.empty': '此范围内尚无完成的扫描',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-category-chart.${key}`,
      {},
      this.manifest.catalogs.en[`core-category-chart.${key}`],
    );
  },
  render(input, helpers) {
    const { canvas, drawLineChart, bytesAxis, i18n } = helpers;
    const { rows, categories } = input;
    if (!canvas) return;
    drawLineChart(
      canvas,
      rows,
      (categories ?? []).map((name) => ({
        key: `${name}_bytes`,
        label: name.charAt(0).toUpperCase() + name.slice(1),
      })),
      {
        xKey: 'started_at',
        axis: bytesAxis,
        zeroBased: true,
        empty: i18n.t(
          'core-category-chart.empty',
          {},
          'No scan has finished in this range yet',
        ),
      },
    );
  },
};
