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
        'core-uptime-events.restarted': 'Restarted',
        'core-uptime-events.what': 'What',
        'core-uptime-events.from': 'From',
        'core-uptime-events.until': 'Until',
        'core-uptime-events.for': 'For',
        'core-uptime-events.meaning': 'What it means',
      }),
      es: Object.freeze({
        'core-uptime-events.empty':
          'Todas las lecturas de esta ventana están presentes y la máquina nunca se reinició. No hay nada que explicar.',
        'core-uptime-events.restarted': 'Reiniciado',
        'core-uptime-events.what': 'Qué',
        'core-uptime-events.from': 'Desde',
        'core-uptime-events.until': 'Hasta',
        'core-uptime-events.for': 'Durante',
        'core-uptime-events.meaning': 'Qué significa',
      }),
      hi: Object.freeze({
        'core-uptime-events.empty':
          'इस अवधि की हर रीडिंग मौजूद है और मशीन कभी रीस्टार्ट नहीं हुई। समझाने के लिए कुछ नहीं।',
        'core-uptime-events.restarted': 'रीस्टार्ट हुआ',
        'core-uptime-events.what': 'क्या',
        'core-uptime-events.from': 'से',
        'core-uptime-events.until': 'तक',
        'core-uptime-events.for': 'अवधि',
        'core-uptime-events.meaning': 'इसका अर्थ',
      }),
      ar: Object.freeze({
        'core-uptime-events.empty':
          'كل قراءة في هذه النافذة موجودة ولم يُعد تشغيل الجهاز. لا شيء لتفسيره.',
        'core-uptime-events.restarted': 'أُعيد تشغيله',
        'core-uptime-events.what': 'ما',
        'core-uptime-events.from': 'من',
        'core-uptime-events.until': 'حتى',
        'core-uptime-events.for': 'لمدة',
        'core-uptime-events.meaning': 'ما معناه',
      }),
      zh: Object.freeze({
        'core-uptime-events.empty':
          '此窗口中的每个读数都存在，并且机器从未重启。无需解释。',
        'core-uptime-events.restarted': '已重启',
        'core-uptime-events.what': '内容',
        'core-uptime-events.from': '从',
        'core-uptime-events.until': '到',
        'core-uptime-events.for': '持续',
        'core-uptime-events.meaning': '含义',
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
    const restarted = i18n.t('core-uptime-events.restarted', {}, 'Restarted');
    renderTable(target.id, bands, [
      {
        label: i18n.t('core-uptime-events.what', {}, 'What'),
        render: (row) => {
          const span = document.createElement('span');
          span.textContent = row.kind;
          span.className = row.kind === restarted ? 'grew' : 'muted';
          return span;
        },
      },
      {
        label: i18n.t('core-uptime-events.from', {}, 'From'),
        render: (row) => whenSpan(row.from),
      },
      {
        label: i18n.t('core-uptime-events.until', {}, 'Until'),
        render: (row) => whenSpan(row.to),
      },
      {
        label: i18n.t('core-uptime-events.for', {}, 'For'),
        className: 'size',
        render: (row) =>
          (row.kind === restarted ? '≤ ' : '') + humanDuration(row.seconds),
      },
      {
        label: i18n.t('core-uptime-events.meaning', {}, 'What it means'),
        className: 'name',
        render: (row) => row.note,
      },
    ]);
  },
};
