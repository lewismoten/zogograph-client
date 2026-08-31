/* Core card package: the shell supplies the CPU-specific empty-state helper. */
window.ZogographCpuBusyChartCard = {
  manifest: Object.freeze({
    id: 'core-cpu-busy-chart',
    locales: ['core-cpu-busy-chart.'],
    helpers: ['chart', 'drawLineChart', 'percentAxis', 'empty', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
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
      es: Object.freeze({
        'core-cpu-busy-chart.empty': 'No hay lecturas de CPU.',
        'core-cpu-busy-chart.busy': 'Ocupado',
        'core-cpu-busy-chart.monitoring_tree': 'Árbol de supervisión',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'Carga de trabajo (sin monitor)',
        'core-cpu-busy-chart.userland': 'Espacio de usuario',
        'core-cpu-busy-chart.kernel': 'Kernel',
        'core-cpu-busy-chart.waiting_for_storage': 'Esperando almacenamiento',
        'core-cpu-busy-chart.stolen_by_host': 'Robado por el host',
      }),
      hi: Object.freeze({
        'core-cpu-busy-chart.empty': 'कोई CPU रीडिंग नहीं है।',
        'core-cpu-busy-chart.busy': 'व्यस्त',
        'core-cpu-busy-chart.monitoring_tree': 'निगरानी ट्री',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'कार्यभार (मॉनिटर को छोड़कर)',
        'core-cpu-busy-chart.userland': 'यूज़र स्पेस',
        'core-cpu-busy-chart.kernel': 'कर्नेल',
        'core-cpu-busy-chart.waiting_for_storage': 'स्टोरेज की प्रतीक्षा',
        'core-cpu-busy-chart.stolen_by_host': 'होस्ट द्वारा लिया गया',
      }),
      ar: Object.freeze({
        'core-cpu-busy-chart.empty': 'لا توجد قراءات للمعالج.',
        'core-cpu-busy-chart.busy': 'مشغول',
        'core-cpu-busy-chart.monitoring_tree': 'شجرة المراقبة',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'عبء العمل (دون المراقب)',
        'core-cpu-busy-chart.userland': 'مساحة المستخدم',
        'core-cpu-busy-chart.kernel': 'النواة',
        'core-cpu-busy-chart.waiting_for_storage': 'ينتظر التخزين',
        'core-cpu-busy-chart.stolen_by_host': 'استولى عليه المضيف',
      }),
      zh: Object.freeze({
        'core-cpu-busy-chart.empty': '没有 CPU 读数。',
        'core-cpu-busy-chart.busy': '繁忙',
        'core-cpu-busy-chart.monitoring_tree': '监控树',
        'core-cpu-busy-chart.workload_excluding_monitor':
          '工作负载（不含监控）',
        'core-cpu-busy-chart.userland': '用户空间',
        'core-cpu-busy-chart.kernel': '内核',
        'core-cpu-busy-chart.waiting_for_storage': '正在等待存储',
        'core-cpu-busy-chart.stolen_by_host': '被主机占用',
      }),
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
        { key: 'system', label: t('core-cpu-busy-chart.kernel', {}, 'Kernel') },
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
