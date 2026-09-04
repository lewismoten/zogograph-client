const GaugeStatusCard = {
  manifest: Object.freeze({
    id: 'core-gauge-status',
    locales: ['core-gauge-status.'],
    owner: 'core-ui',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-gauge-status.no_ceiling': 'no ceiling to measure against',
        'core-gauge-status.not_measured': 'not measured',
        'core-gauge-status.monitoring': 'Monitoring: {{value}}',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-ui/locales/es/gauge-status.json',
      hi: 'packages/core-ui/locales/hi/gauge-status.json',
      ar: 'packages/core-ui/locales/ar/gauge-status.json',
      zh: 'packages/core-ui/locales/zh/gauge-status.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-gauge-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
Zogograph.cards.register(GaugeStatusCard);
