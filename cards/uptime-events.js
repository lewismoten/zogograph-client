/* Core card package: the shell supplies row formatting and the event data. */
window.ZogographUptimeEventsCard = {
  manifest: Object.freeze({
    id: 'core-uptime-events',
    locales: ['core-uptime-events.'],
    helpers: ['target', 'renderTable', 'whenSpan', 'humanDuration', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-uptime-events.empty':
          'Every reading in this window is present and the machine never restarted. Nothing to explain.',
      }),
      es: Object.freeze({
        'core-uptime-events.empty':
          'Todas las lecturas de esta ventana están presentes y la máquina nunca se reinició. No hay nada que explicar.',
      }),
      hi: Object.freeze({
        'core-uptime-events.empty':
          'इस अवधि की हर रीडिंग मौजूद है और मशीन कभी रीस्टार्ट नहीं हुई। समझाने के लिए कुछ नहीं।',
      }),
      ar: Object.freeze({
        'core-uptime-events.empty':
          'كل قراءة في هذه النافذة موجودة ولم يُعد تشغيل الجهاز. لا شيء لتفسيره.',
      }),
      zh: Object.freeze({
        'core-uptime-events.empty':
          '此窗口中的每个读数都存在，并且机器从未重启。无需解释。',
      }),
    }),
  }),
  render(bands, helpers) {
    const { target, renderTable, whenSpan, humanDuration, i18n } = helpers;
    if (!target) return;
    if (!bands.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: i18n.t(
            'core-uptime-events.empty',
            {},
            'Every reading in this window is present and the machine never restarted. Nothing to explain.',
          ),
        }),
      );
      return;
    }
    const restarted = i18n.t('uptime.restarted', {}, 'Restarted');
    renderTable(target.id, bands, [
      {
        label: i18n.t('table.what', {}, 'What'),
        render: (row) => {
          const span = document.createElement('span');
          span.textContent = row.kind;
          span.className = row.kind === restarted ? 'grew' : 'muted';
          return span;
        },
      },
      {
        label: i18n.t('uptime.table_from', {}, 'From'),
        render: (row) => whenSpan(row.from),
      },
      {
        label: i18n.t('uptime.table_until', {}, 'Until'),
        render: (row) => whenSpan(row.to),
      },
      {
        label: i18n.t('uptime.table_for', {}, 'For'),
        className: 'size',
        render: (row) =>
          (row.kind === restarted ? '≤ ' : '') + humanDuration(row.seconds),
      },
      {
        label: i18n.t('uptime.table_meaning', {}, 'What it means'),
        className: 'name',
        render: (row) => row.note,
      },
    ]);
  },
};
