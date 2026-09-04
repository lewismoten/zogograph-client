/* Core card package: the shell supplies the CPU-specific empty-state helper. */
const CpuBusyChartCard = {
  manifest: Object.freeze({
    id: 'core-cpu-busy-chart',
    locales: ['core-cpu-busy-chart.'],
    owner: 'core-cpu',
    helpers: ['chart', 'drawLineChart', 'percentAxis', 'empty', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-busy-chart.title': 'Busy, and what it was busy with',
        'core-cpu-busy-chart.help':
          'The total busy line is split into the states that make it up. The monitor tree and the remaining workload are also shown: together they explain the total, while alerts continue to use total host CPU. Percentages are of every core added together, so 100% is the whole machine occupied rather than one core of it.',
        'core-cpu-busy-chart.empty': 'No CPU readings.',
        'core-cpu-busy-chart.busy': 'Busy',
        'core-cpu-busy-chart.monitoring_tree': 'Monitoring tree',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'Workload (excl. monitor)',
        'core-cpu-busy-chart.userland': 'Userland',
        'core-cpu-busy-chart.kernel': 'Kernel',
        'core-cpu-busy-chart.waiting_for_storage': 'Waiting for storage',
        'core-cpu-busy-chart.stolen_by_host': 'Stolen by the host',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-cpu/locales/es/busy-chart.json',
      hi: 'packages/core-cpu/locales/hi/busy-chart.json',
      ar: 'packages/core-cpu/locales/ar/busy-chart.json',
      zh: 'packages/core-cpu/locales/zh/busy-chart.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-busy-chart.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-busy-chart.${key}`],
    );
  },
  render(data, helpers) {
    const { chart, drawLineChart, percentAxis, empty, i18n } = helpers;
    if (!chart) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    drawLineChart(
      chart,
      data.series || [],
      [
        { key: 'busy', label: t('core-cpu-busy-chart.busy', {}, 'Busy') },
        {
          key: 'monitoring_cpu_percent',
          label: t(
            'core-cpu-busy-chart.monitoring_tree',
            {},
            'Monitoring tree',
          ),
        },
        {
          key: 'workload_cpu_percent',
          label: t(
            'core-cpu-busy-chart.workload_excluding_monitor',
            {},
            'Workload (excl. monitor)',
          ),
        },
        {
          key: 'user',
          label: t('core-cpu-busy-chart.userland', {}, 'Userland'),
        },
        {
          key: 'system',
          label: t('core-cpu-busy-chart.kernel', {}, 'Kernel'),
        },
        {
          key: 'iowait',
          label: t(
            'core-cpu-busy-chart.waiting_for_storage',
            {},
            'Waiting for storage',
          ),
        },
        {
          key: 'steal',
          label: t(
            'core-cpu-busy-chart.stolen_by_host',
            {},
            'Stolen by the host',
          ),
        },
      ],
      {
        axis: percentAxis,
        zeroBased: true,
        max: 100,
        empty:
          empty(data) || t('core-cpu-busy-chart.empty', {}, 'No CPU readings.'),
      },
    );
  },
};
Zogograph.cards.register(CpuBusyChartCard);
