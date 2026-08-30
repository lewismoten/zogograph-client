/* Core card package: CPU-state summaries share the shell's chart and labels. */
window.ZogographCpuTimeCard = {
  manifest: Object.freeze({
    id: 'core-cpu-time',
    locales: ['core-cpu-time.'],
    helpers: [
      'setCard',
      'note',
      'tableTarget',
      'renderTable',
      'bandOf',
      'cpuStateLabel',
      'cpuStateMeaning',
      'verdict',
      'emptyMessage',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-time.average_over_range':
          '{{percent}}% on average over this range',
        'core-cpu-time.state': 'State',
        'core-cpu-time.in_proc_stat': 'In /proc/stat',
        'core-cpu-time.now': 'Now',
        'core-cpu-time.what_it_means': 'What it means',
      }),
      es: Object.freeze({
        'core-cpu-time.average_over_range':
          '{{percent}}% de promedio en este intervalo',
        'core-cpu-time.state': 'Estado',
        'core-cpu-time.in_proc_stat': 'En /proc/stat',
        'core-cpu-time.now': 'Ahora',
        'core-cpu-time.what_it_means': 'Qué significa',
      }),
      hi: Object.freeze({
        'core-cpu-time.average_over_range': 'इस अवधि में औसतन {{percent}}%',
        'core-cpu-time.state': 'स्थिति',
        'core-cpu-time.in_proc_stat': '/proc/stat में',
        'core-cpu-time.now': 'अभी',
        'core-cpu-time.what_it_means': 'इसका अर्थ',
      }),
      ar: Object.freeze({
        'core-cpu-time.average_over_range':
          'متوسط {{percent}}% خلال هذا النطاق',
        'core-cpu-time.state': 'الحالة',
        'core-cpu-time.in_proc_stat': 'في /proc/stat',
        'core-cpu-time.now': 'الآن',
        'core-cpu-time.what_it_means': 'ما يعنيه',
      }),
      zh: Object.freeze({
        'core-cpu-time.average_over_range': '此范围内平均 {{percent}}%',
        'core-cpu-time.state': '状态',
        'core-cpu-time.in_proc_stat': '在 /proc/stat 中',
        'core-cpu-time.now': '现在',
        'core-cpu-time.what_it_means': '含义',
      }),
    }),
  }),
  render(input, helpers) {
    const { now, states, series, cores } = input;
    const {
      setCard,
      note,
      tableTarget,
      renderTable,
      bandOf,
      cpuStateLabel,
      cpuStateMeaning,
      verdict,
      emptyMessage,
      i18n,
    } = helpers;
    const bands = {
      steal: [5, 20],
      iowait: [15, 35],
      softirq: [10, 25],
      irq: [5, 15],
    };
    if (!now) {
      if (note) note.textContent = emptyMessage;
    } else {
      for (const [key, meta] of Object.entries(states)) {
        const value = Number(now[key]);
        const values = series
          .map((row) => Number(row[key]))
          .filter(Number.isFinite);
        const average = values.length
          ? values.reduce((sum, one) => sum + one, 0) / values.length
          : null;
        const band = bands[key]
          ? bandOf(value, bands[key][0], bands[key][1])
          : null;
        setCard(
          cpuStateLabel(meta),
          Number.isFinite(value) ? `${value.toFixed(1)}%` : '—',
          average == null
            ? ''
            : i18n.t(
                'core-cpu-time.average_over_range',
                { percent: average.toFixed(1) },
                '{{percent}}% on average over this range',
              ),
          band === 'crit' ? 'crit' : band === 'warn' ? 'warn' : '',
        );
      }
    }
    if (now && note) note.textContent = verdict(now, cores);
    if (!tableTarget) return;
    renderTable(
      tableTarget.id,
      Object.entries(states).map(([key, meta]) => ({
        key,
        label: cpuStateLabel(meta),
        now: now ? Number(now[key]) : null,
        what: cpuStateMeaning(meta),
      })),
      [
        {
          label: i18n.t('core-cpu-time.state', {}, 'State'),
          render: (row) => row.label,
        },
        {
          label: i18n.t('core-cpu-time.in_proc_stat', {}, 'In /proc/stat'),
          className: 'cmd',
          render: (row) => row.key,
        },
        {
          label: i18n.t('core-cpu-time.now', {}, 'Now'),
          className: 'size',
          render: (row) =>
            row.now == null || !Number.isFinite(row.now)
              ? '—'
              : `${row.now.toFixed(1)}%`,
        },
        {
          label: i18n.t('core-cpu-time.what_it_means', {}, 'What it means'),
          render: (row) => row.what,
        },
      ],
    );
  },
};
