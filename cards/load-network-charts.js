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
      }),
      es: Object.freeze({
        'core-load-network-charts.ready': 'Gráficos de carga y red',
      }),
      hi: Object.freeze({
        'core-load-network-charts.ready': 'लोड और नेटवर्क चार्ट',
      }),
      ar: Object.freeze({
        'core-load-network-charts.ready': 'مخططات الحمل والشبكة',
      }),
      zh: Object.freeze({ 'core-load-network-charts.ready': '负载和网络图表' }),
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
