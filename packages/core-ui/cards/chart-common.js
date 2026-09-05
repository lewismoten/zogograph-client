/* Shared chart copy primitive. */
const ChartCommonCard = {
  manifest: Object.freeze({
    id: 'core-chart-common',
    locales: ['core-chart-common.'],
    owner: 'core-ui',
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
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-ui/locales/es/chart-common.json',
      hi: 'packages/core-ui/locales/hi/chart-common.json',
      ar: 'packages/core-ui/locales/ar/chart-common.json',
      zh: 'packages/core-ui/locales/zh/chart-common.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-chart-common.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
Zogograph.cards.register(ChartCommonCard);
