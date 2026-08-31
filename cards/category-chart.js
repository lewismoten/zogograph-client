/* Core card package: watched storage categories over time. */
window.ZogographCategoryChart = {
  manifest: Object.freeze({
    id: 'core-category-chart',
    locales: ['core-category-chart.'],
    helpers: ['canvas', 'drawLineChart', 'bytesAxis', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-category-chart.title': 'Categories over time',
        'core-category-chart.help':
          'Totals from each completed scan. {{error_log}} files are counted on their own, wherever they turn up — one of them can be the whole problem.',
        'core-category-chart.empty': 'No scan has finished in this range yet',
      }),
      es: Object.freeze({
        'core-category-chart.title': 'Categorías a lo largo del tiempo',
        'core-category-chart.help':
          'Totales de cada análisis completado. Los archivos {{error_log}} se cuentan por separado, dondequiera que aparezcan: uno puede ser todo el problema.',
        'core-category-chart.empty':
          'Aún no ha terminado ningún análisis en este intervalo',
      }),
      hi: Object.freeze({
        'core-category-chart.title': 'समय के साथ श्रेणियाँ',
        'core-category-chart.help':
          'हर पूर्ण स्कैन का कुल। {{error_log}} फ़ाइलें जहाँ भी मिलें अलग गिनी जाती हैं — उनमें से एक ही पूरा कारण हो सकती हैं।',
        'core-category-chart.empty':
          'इस दायरे में अभी कोई स्कैन पूरा नहीं हुआ है',
      }),
      ar: Object.freeze({
        'core-category-chart.title': 'الفئات عبر الزمن',
        'core-category-chart.help':
          'إجماليات من كل فحص مكتمل. تُحسب ملفات {{error_log}} وحدها أينما ظهرت — وقد يكون واحد منها المشكلة كلها.',
        'core-category-chart.empty': 'لم ينته أي فحص في هذا النطاق بعد',
      }),
      zh: Object.freeze({
        'core-category-chart.title': '随时间变化的类别',
        'core-category-chart.help':
          '每次完成扫描的总计。{{error_log}} 文件无论出现在哪里都会单独计数——其中一个就可能是全部问题。',
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
