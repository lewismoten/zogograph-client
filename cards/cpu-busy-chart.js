/* Core card package: the shell supplies the CPU-specific empty-state helper. */
window.ZogographCpuBusyChartCard = {
  manifest: Object.freeze({
    id: 'core-cpu-busy-chart',
    locales: ['core-cpu-busy-chart.'],
    helpers: ['chart', 'drawLineChart', 'percentAxis', 'empty', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-cpu-busy-chart.empty': 'No CPU readings.' }),
      es: Object.freeze({
        'core-cpu-busy-chart.empty': 'No hay lecturas de CPU.',
      }),
      hi: Object.freeze({
        'core-cpu-busy-chart.empty': 'कोई CPU रीडिंग नहीं है।',
      }),
      ar: Object.freeze({
        'core-cpu-busy-chart.empty': 'لا توجد قراءات للمعالج.',
      }),
      zh: Object.freeze({ 'core-cpu-busy-chart.empty': '没有 CPU 读数。' }),
    }),
  }),

  render(data, helpers) {
    const { chart, drawLineChart, percentAxis, empty, i18n } = helpers;
    if (!chart) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    drawLineChart(
      chart,
      data.series || [],
      [
        { key: 'busy', label: t('cpu.busy', {}, 'Busy') },
        {
          key: 'monitoring_cpu_percent',
          label: t('cpu.monitoring_tree', {}, 'Monitoring tree'),
        },
        {
          key: 'workload_cpu_percent',
          label: t(
            'cpu.workload_excluding_monitor',
            {},
            'Workload (excl. monitor)',
          ),
        },
        { key: 'user', label: t('cpu.userland', {}, 'Userland') },
        { key: 'system', label: t('cpu.kernel', {}, 'Kernel') },
        {
          key: 'iowait',
          label: t('cpu.waiting_for_storage', {}, 'Waiting for storage'),
        },
        {
          key: 'steal',
          label: t('cpu.stolen_by_host', {}, 'Stolen by the host'),
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
