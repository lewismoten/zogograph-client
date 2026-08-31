/* Core card package: load average and network traffic charts. */
window.ZogographLoadNetworkCharts = {
  manifest: Object.freeze({
    id: 'core-load-network-charts',
    locales: ['core-load-network-charts.'],
    helpers: [
      'loadCanvas',
      'networkCanvas',
      'uptime',
      'drawLineChart',
      'outageBands',
      'tickPrecision',
      'rateAxis',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-load-network-charts.ready': 'Load and network charts',
        'core-load-network-charts.load_one_minute': '1 min',
        'core-load-network-charts.load_five_minutes': '5 min',
        'core-load-network-charts.load_fifteen_minutes': '15 min',
        'core-load-network-charts.network_in': 'In',
        'core-load-network-charts.network_out': 'Out',
        'core-load-network-charts.network_unreadable':
          'No network counters here — /proc/net/dev is not readable',
      }),
      es: Object.freeze({
        'core-load-network-charts.ready': 'Gráficos de carga y red',
        'core-load-network-charts.load_one_minute': '1 min',
        'core-load-network-charts.load_five_minutes': '5 min',
        'core-load-network-charts.load_fifteen_minutes': '15 min',
        'core-load-network-charts.network_in': 'Entrada',
        'core-load-network-charts.network_out': 'Salida',
        'core-load-network-charts.network_unreadable':
          'No hay contadores de red: /proc/net/dev no se puede leer',
      }),
      hi: Object.freeze({
        'core-load-network-charts.ready': 'लोड और नेटवर्क चार्ट',
        'core-load-network-charts.load_one_minute': '1 मिनट',
        'core-load-network-charts.load_five_minutes': '5 मिनट',
        'core-load-network-charts.load_fifteen_minutes': '15 मिनट',
        'core-load-network-charts.network_in': 'आवक',
        'core-load-network-charts.network_out': 'जावक',
        'core-load-network-charts.network_unreadable':
          'यहाँ नेटवर्क काउंटर नहीं हैं — /proc/net/dev पढ़ने योग्य नहीं है',
      }),
      ar: Object.freeze({
        'core-load-network-charts.ready': 'مخططات الحمل والشبكة',
        'core-load-network-charts.load_one_minute': 'دقيقة واحدة',
        'core-load-network-charts.load_five_minutes': '5 دقائق',
        'core-load-network-charts.load_fifteen_minutes': '15 دقيقة',
        'core-load-network-charts.network_in': 'وارد',
        'core-load-network-charts.network_out': 'صادر',
        'core-load-network-charts.network_unreadable':
          'لا توجد عدادات شبكة هنا — لا يمكن قراءة /proc/net/dev',
      }),
      zh: Object.freeze({
        'core-load-network-charts.ready': '负载和网络图表',
        'core-load-network-charts.load_one_minute': '1 分钟',
        'core-load-network-charts.load_five_minutes': '5 分钟',
        'core-load-network-charts.load_fifteen_minutes': '15 分钟',
        'core-load-network-charts.network_in': '入站',
        'core-load-network-charts.network_out': '出站',
        'core-load-network-charts.network_unreadable':
          '此处没有网络计数器：无法读取 /proc/net/dev',
      }),
    }),
  }),
  render(rows, helpers) {
    const {
      loadCanvas,
      networkCanvas,
      uptime,
      drawLineChart,
      outageBands,
      tickPrecision,
      rateAxis,
      i18n,
    } = helpers;
    if (!loadCanvas || !networkCanvas) return;
    const bands = outageBands({ analysis: uptime });
    drawLineChart(
      loadCanvas,
      rows,
      [
        {
          key: 'load1',
          label: i18n.t(
            'core-load-network-charts.load_one_minute',
            {},
            '1 min',
          ),
        },
        {
          key: 'load5',
          label: i18n.t(
            'core-load-network-charts.load_five_minutes',
            {},
            '5 min',
          ),
        },
        {
          key: 'load15',
          label: i18n.t(
            'core-load-network-charts.load_fifteen_minutes',
            {},
            '15 min',
          ),
        },
      ],
      {
        axis: (step) => ({
          scale: 1,
          unit: '',
          format: (value) => value.toFixed(tickPrecision(step)),
        }),
        zeroBased: true,
        outages: bands,
      },
    );
    drawLineChart(
      networkCanvas,
      rows,
      [
        {
          key: 'net_rx_rate',
          label: i18n.t('core-load-network-charts.network_in', {}, 'In'),
        },
        {
          key: 'net_tx_rate',
          label: i18n.t('core-load-network-charts.network_out', {}, 'Out'),
        },
      ],
      {
        axis: rateAxis,
        zeroBased: true,
        outages: bands,
        empty: i18n.t(
          'core-load-network-charts.network_unreadable',
          {},
          'No network counters here — /proc/net/dev is not readable',
        ),
      },
    );
  },
};
