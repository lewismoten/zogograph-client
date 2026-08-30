/* Core card package: CPU-state summaries share the shell's chart and labels. */
window.ZogographCpuTimeCard = {
  manifest: Object.freeze({
    id: 'core-cpu-time',
    locales: ['core-cpu-time.'],
    helpers: ['setCard', 'bandOf', 'cpuStateLabel', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-time.average_over_range':
          '{{percent}}% on average over this range',
      }),
      es: Object.freeze({
        'core-cpu-time.average_over_range':
          '{{percent}}% de promedio en este intervalo',
      }),
      hi: Object.freeze({
        'core-cpu-time.average_over_range': 'इस अवधि में औसतन {{percent}}%',
      }),
      ar: Object.freeze({
        'core-cpu-time.average_over_range':
          'متوسط {{percent}}% خلال هذا النطاق',
      }),
      zh: Object.freeze({
        'core-cpu-time.average_over_range': '此范围内平均 {{percent}}%',
      }),
    }),
  }),
  render(input, helpers) {
    const { now, states, series } = input;
    const { setCard, bandOf, cpuStateLabel, i18n } = helpers;
    const bands = {
      steal: [5, 20],
      iowait: [15, 35],
      softirq: [10, 25],
      irq: [5, 15],
    };
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
  },
};
