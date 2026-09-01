/* Core card package: shared line and pie chart empty states and grouping labels. */
window.ZogographChartCommonCard = {
  manifest: Object.freeze({
    id: 'core-chart-common',
    locales: ['core-chart-common.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chart-common.nothing_collected_range':
          'Nothing collected for this range yet',
        'core-chart-common.nothing_to_show_yet': 'Nothing to show yet',
        'core-chart-common.everything_else': 'everything else',
        'core-chart-common.everything_else_count':
          'Everything else ({{count, number}} services)',
      }),
      es: Object.freeze({
        'core-chart-common.nothing_collected_range':
          'Aún no se recopilaron datos para este intervalo',
        'core-chart-common.nothing_to_show_yet': 'Aún no hay nada que mostrar',
        'core-chart-common.everything_else': 'todo lo demás',
        'core-chart-common.everything_else_count':
          'Todo lo demás ({{count, number}} servicios)',
      }),
      hi: Object.freeze({
        'core-chart-common.nothing_collected_range':
          'इस अवधि के लिए अभी तक कोई डेटा एकत्र नहीं किया गया है',
        'core-chart-common.nothing_to_show_yet':
          'अभी दिखाने के लिए कुछ नहीं है',
        'core-chart-common.everything_else': 'बाकी सब',
        'core-chart-common.everything_else_count':
          'बाकी सब ({{count, number}} सेवाएँ)',
      }),
      ar: Object.freeze({
        'core-chart-common.nothing_collected_range':
          'لم تُجمع بيانات لهذا النطاق الزمني بعد',
        'core-chart-common.nothing_to_show_yet': 'لا يوجد شيء لعرضه بعد',
        'core-chart-common.everything_else': 'كل ما عدا ذلك',
        'core-chart-common.everything_else_count':
          'كل ما عدا ذلك ({{count, number}} خدمات)',
      }),
      zh: Object.freeze({
        'core-chart-common.nothing_collected_range': '此时间范围尚未收集到数据',
        'core-chart-common.nothing_to_show_yet': '目前没有可显示的内容',
        'core-chart-common.everything_else': '其余所有内容',
        'core-chart-common.everything_else_count':
          '其余内容（{{count, number}} 个服务）',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-chart-common.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
