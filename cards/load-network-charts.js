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
        'core-load-network-charts.load_title': 'Load average',
        'core-load-network-charts.load_help':
          'Not a percentage: it is how many processes wanted to run at once. Compare it to the core count — at one per core the machine is busy, above that work is queueing.',
        'core-load-network-charts.network_title': 'Network',
        'core-load-network-charts.network_help':
          'Bytes a second in and out, summed over the real interfaces. Loopback is not counted.',
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
        'core-load-network-charts.load_title': 'Promedio de carga',
        'core-load-network-charts.load_help':
          'No es un porcentaje: es cuántos procesos querían ejecutarse a la vez. Compárelo con el número de núcleos: a uno por núcleo la máquina está ocupada; por encima, el trabajo hace cola.',
        'core-load-network-charts.network_title': 'Red',
        'core-load-network-charts.network_help':
          'Bytes por segundo de entrada y salida, sumados sobre las interfaces reales. No se cuenta loopback.',
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
        'core-load-network-charts.load_title': 'लोड औसत',
        'core-load-network-charts.load_help':
          'प्रतिशत नहीं: एक साथ चलना चाहने वाले प्रोसेस की संख्या। इसे कोर गिनती से तुलना करें—हर कोर पर एक हो तो मशीन व्यस्त है, उससे ऊपर काम कतार में है।',
        'core-load-network-charts.network_title': 'नेटवर्क',
        'core-load-network-charts.network_help':
          'वास्तविक इंटरफेस पर जोड़े गए प्रति सेकंड अंदर और बाहर बाइट। Loopback नहीं गिना जाता।',
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
        'core-load-network-charts.load_title': 'متوسط الحمل',
        'core-load-network-charts.load_help':
          'ليس نسبة مئوية: إنه عدد العمليات التي أرادت التشغيل معًا. قارنه بعدد الأنوية — عند واحدة لكل نواة تكون الآلة مشغولة، وفوق ذلك ينتظر العمل في طابور.',
        'core-load-network-charts.network_title': 'الشبكة',
        'core-load-network-charts.network_help':
          'البايتات الواردة والصادرة في الثانية، مجمعة عبر الواجهات الحقيقية. لا تُحسب واجهة loopback.',
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
        'core-load-network-charts.load_title': '负载平均值',
        'core-load-network-charts.load_help':
          '这不是百分比：它表示同时想运行的进程数量。将它与核心数比较——每核心一个时机器正忙，超过时工作正在排队。',
        'core-load-network-charts.network_title': '网络',
        'core-load-network-charts.network_help':
          '在实际接口上汇总的每秒入站和出站字节数。不计入回环接口。',
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
  text(key, helpers) {
    return helpers.i18n.t(
      `core-load-network-charts.${key}`,
      {},
      this.manifest.catalogs.en[`core-load-network-charts.${key}`],
    );
  },
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
