/* Core card package: shared relative-time and range formatting. */
window.ZogographTimeFormattingCard = {
  manifest: Object.freeze({
    id: 'core-time-formatting',
    locales: ['core-time-formatting.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-time-formatting.range_to': 'to',
        'core-time-formatting.ago': '{{duration}} ago',
        'core-time-formatting.date_with_duration_ago':
          '{{date}} — {{duration}} ago',
        'core-time-formatting.duration_value': '{{value, unit}}',
      }),
      es: Object.freeze({
        'core-time-formatting.range_to': 'a',
        'core-time-formatting.ago': 'hace {{duration}}',
        'core-time-formatting.date_with_duration_ago':
          '{{date}} — hace {{duration}}',
        'core-time-formatting.duration_value': '{{value, unit}}',
      }),
      hi: Object.freeze({
        'core-time-formatting.range_to': 'से',
        'core-time-formatting.ago': '{{duration}} पहले',
        'core-time-formatting.date_with_duration_ago':
          '{{date}} — {{duration}} पहले',
        'core-time-formatting.duration_value': '{{value, unit}}',
      }),
      ar: Object.freeze({
        'core-time-formatting.range_to': 'إلى',
        'core-time-formatting.ago': 'منذ {{duration}}',
        'core-time-formatting.date_with_duration_ago':
          '{{date}} — منذ {{duration}}',
        'core-time-formatting.duration_value': '{{value, unit}}',
      }),
      zh: Object.freeze({
        'core-time-formatting.range_to': '至',
        'core-time-formatting.ago': '{{duration}}前',
        'core-time-formatting.date_with_duration_ago':
          '{{date}} — {{duration}}前',
        'core-time-formatting.duration_value': '{{value, unit}}',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-time-formatting.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
