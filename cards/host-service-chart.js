/* Core card package: the shell supplies the selected chart primitives. */
window.ZogographHostServiceChart = {
  manifest: Object.freeze({
    id: 'core-host-service-chart',
    locales: ['core-host-service-chart.'],
    helpers: [
      'chart',
      'note',
      'drawLineChart',
      'bytesAxis',
      'countAxis',
      'rateAxis',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-service-chart.title': 'Services over time',
        'core-host-service-chart.help':
          "One line per service, from the host collector's own longer-lived record. This is the panel that answers “and what was it in June?”",
        'core-host-service-chart.empty':
          'Not enough service history to draw yet',
        'core-host-service-chart.counter_help':
          'Counters differentiated into a rate; a gap means the service restarted.',
      }),
      es: Object.freeze({
        'core-host-service-chart.title': 'Servicios a lo largo del tiempo',
        'core-host-service-chart.help':
          'Una línea por servicio, del registro duradero del recopilador del host. Este panel responde: «¿y cómo era en junio?»',
        'core-host-service-chart.empty':
          'No hay suficiente historial de servicios para dibujar todavía',
        'core-host-service-chart.counter_help':
          'Los contadores se diferencian en una tasa; una brecha significa que el servicio se reinició.',
      }),
      hi: Object.freeze({
        'core-host-service-chart.title': 'समय के साथ सेवाएँ',
        'core-host-service-chart.help':
          'होस्ट कलेक्टर के लंबे समय तक चलने वाले रिकॉर्ड से हर सेवा के लिए एक पंक्ति। यह पैनल बताता है कि जून में स्थिति क्या थी।',
        'core-host-service-chart.empty':
          'अभी चित्र बनाने के लिए पर्याप्त सेवा इतिहास नहीं है',
        'core-host-service-chart.counter_help':
          'काउंटरों को दर में बदला जाता है; अंतर का अर्थ है कि सेवा फिर शुरू हुई।',
      }),
      ar: Object.freeze({
        'core-host-service-chart.title': 'الخدمات عبر الزمن',
        'core-host-service-chart.help':
          'سطر واحد لكل خدمة من سجل جامع المضيف الأطول عمرًا. تجيب هذه اللوحة عن سؤال: كيف كانت في يونيو؟',
        'core-host-service-chart.empty': 'لا يكفي سجل الخدمات للرسم بعد',
        'core-host-service-chart.counter_help':
          'تُحوّل العدادات إلى معدل؛ وتعني الفجوة أن الخدمة أعيد تشغيلها.',
      }),
      zh: Object.freeze({
        'core-host-service-chart.title': '服务随时间变化',
        'core-host-service-chart.help':
          '主机采集器长期记录中，每项服务各占一行。此面板可查看六月的情况。',
        'core-host-service-chart.empty': '服务历史不足，暂时无法绘制',
        'core-host-service-chart.counter_help':
          '计数器会转换为速率；间隔表示服务已重启。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-service-chart.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-service-chart.${key}`],
    );
  },
  render(input, helpers) {
    const { series, charted, metric } = input;
    const { chart, note, drawLineChart, bytesAxis, countAxis, rateAxis, i18n } =
      helpers;
    const axis =
      metric === 'procs' || metric === 'threads'
        ? countAxis
        : metric === 'read' || metric === 'write'
          ? rateAxis
          : bytesAxis;
    drawLineChart(
      chart,
      series,
      charted.map((name) => ({ key: name, label: name })),
      {
        axis,
        zeroBased: true,
        empty: i18n.t(
          'core-host-service-chart.empty',
          {},
          'Not enough service history to draw yet',
        ),
      },
    );
    if (note)
      note.textContent =
        metric === 'read' || metric === 'write'
          ? i18n.t(
              'core-host-service-chart.counter_help',
              {},
              'Counters differentiated into a rate; a gap means the service restarted.',
            )
          : '';
  },
};
