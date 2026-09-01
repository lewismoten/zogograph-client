/* Core card package: gauge availability and monitoring labels. */
window.ZogographGaugeStatusCard = {
  manifest: Object.freeze({
    id: 'core-gauge-status',
    locales: ['core-gauge-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-gauge-status.no_ceiling': 'no ceiling to measure against',
        'core-gauge-status.not_measured': 'not measured',
        'core-gauge-status.monitoring': 'Monitoring: {{value}}',
      }),
      es: Object.freeze({
        'core-gauge-status.no_ceiling': 'sin límite con el que comparar',
        'core-gauge-status.not_measured': 'sin medir',
        'core-gauge-status.monitoring': 'Supervisión: {{value}}',
      }),
      hi: Object.freeze({
        'core-gauge-status.no_ceiling': 'मापने के लिए कोई सीमा नहीं',
        'core-gauge-status.not_measured': 'अभी नहीं मापा गया',
        'core-gauge-status.monitoring': 'निगरानी: {{value}}',
      }),
      ar: Object.freeze({
        'core-gauge-status.no_ceiling': 'لا يوجد حد للمقارنة',
        'core-gauge-status.not_measured': 'لم يُقَس بعد',
        'core-gauge-status.monitoring': 'المراقبة: {{value}}',
      }),
      zh: Object.freeze({
        'core-gauge-status.no_ceiling': '没有可供比较的上限',
        'core-gauge-status.not_measured': '尚未测量',
        'core-gauge-status.monitoring': '监控：{{value}}',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-gauge-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
