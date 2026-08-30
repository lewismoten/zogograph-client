window.ZogographReportsStatusCard = {
  manifest: Object.freeze({
    id: 'core-reports-status',
    locales: ['core-reports-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-reports-status.none': 'No schedules are on.' }),
      es: Object.freeze({
        'core-reports-status.none': 'No hay horarios activos.',
      }),
      hi: Object.freeze({
        'core-reports-status.none': 'कोई शेड्यूल चालू नहीं है।',
      }),
      ar: Object.freeze({
        'core-reports-status.none': 'لا توجد جداول مفعّلة.',
      }),
      zh: Object.freeze({ 'core-reports-status.none': '没有启用的计划。' }),
    }),
  }),
  render(data, helpers) {
    const on = Object.entries(data.on)
      .filter(([, yes]) => yes)
      .map(([key]) => key);
    const said = document.createElement('p');
    said.className = 'muted small';
    said.textContent = on.length
      ? helpers.i18n.t('reports.schedules_on', { schedules: on.join(', ') })
      : helpers.i18n.t('core-reports-status.none', {}, 'No schedules are on.');
    return said;
  },
};
