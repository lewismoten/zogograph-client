window.ZogographReportsStatusCard = {
  manifest: Object.freeze({
    id: 'core-reports-status',
    locales: ['core-reports-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-reports-status.none': 'No schedules are on.',
        'core-reports-status.on': 'Schedules on: {{schedules}}',
      }),
      es: Object.freeze({
        'core-reports-status.none': 'No hay horarios activos.',
        'core-reports-status.on': 'Horarios activos: {{schedules}}',
      }),
      hi: Object.freeze({
        'core-reports-status.none': 'कोई शेड्यूल चालू नहीं है।',
        'core-reports-status.on': 'चालू शेड्यूल: {{schedules}}',
      }),
      ar: Object.freeze({
        'core-reports-status.none': 'لا توجد جداول مفعّلة.',
        'core-reports-status.on': 'الجداول المفعّلة: {{schedules}}',
      }),
      zh: Object.freeze({
        'core-reports-status.none': '没有启用的计划。',
        'core-reports-status.on': '已启用的计划：{{schedules}}',
      }),
    }),
  }),
  render(data, helpers) {
    const on = Object.entries(data.on)
      .filter(([, yes]) => yes)
      .map(([key]) => key);
    const said = document.createElement('p');
    said.className = 'muted small';
    said.textContent = on.length
      ? helpers.i18n.t('core-reports-status.on', { schedules: on.join(', ') })
      : helpers.i18n.t('core-reports-status.none', {}, 'No schedules are on.');
    return said;
  },
};
