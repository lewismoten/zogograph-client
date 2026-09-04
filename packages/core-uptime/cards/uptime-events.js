/* Core card package: the shell supplies row formatting and the event data. */
const UptimeEventsCard = {
  manifest: Object.freeze({
    id: 'core-uptime-events',
    locales: ['core-uptime-events.'],
    owner: 'core-uptime',
    helpers: [
      'target',
      'renderTable',
      'whenSpan',
      'humanDuration',
      'ui',
      'i18n',
    ],
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
        'core-uptime-events.reading_gap': 'Reading gap',
        'core-uptime-events.restart_explanation':
          'Boot time moved, so the machine did restart. It came back at {{booted}}; it went down at some point after the last reading, so this is an upper bound rather than a measurement.',
        'core-uptime-events.gap_collector_not_running':
          'Boot time was unchanged either side, so the machine ran straight through this — it was up, and the collector was not running.',
        'core-uptime-events.gap_before_tracking':
          'These readings predate boot-time recording, so whether the machine restarted here cannot be told. Not an outage — just a stretch that cannot be classified. Gaps from now on will say which.',
        'core-uptime-events.gap_unknown':
          'No boot time either side to compare, so whether the machine stayed up through this cannot be told from these readings.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-uptime/locales/es/uptime-events.json',
      hi: 'packages/core-uptime/locales/hi/uptime-events.json',
      ar: 'packages/core-uptime/locales/ar/uptime-events.json',
      zh: 'packages/core-uptime/locales/zh/uptime-events.json',
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-uptime-events.${key}`,
      values,
      this.manifest.catalogs.en[`core-uptime-events.${key}`],
    );
  },
  render(bands, helpers) {
    const { target, renderTable, whenSpan, humanDuration, ui, i18n } = helpers;
    if (!target) return;
    if (!bands.length) {
      target.replaceChildren(
        ui.empty(
          i18n.t(
            'core-uptime-events.empty',
            {},
            'Every reading in this window is present and the machine never restarted. Nothing to explain.',
          ),
        ),
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
Zogograph.cards.register(UptimeEventsCard);
