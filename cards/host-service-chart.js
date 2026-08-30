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
        'core-host-service-chart.empty':
          'Not enough service history to draw yet',
        'core-host-service-chart.counter_help':
          'Counters differentiated into a rate; a gap means the service restarted.',
      }),
      es: Object.freeze({
        'core-host-service-chart.empty':
          'No hay suficiente historial de servicios para dibujar todavía',
        'core-host-service-chart.counter_help':
          'Los contadores se diferencian en una tasa; una brecha significa que el servicio se reinició.',
      }),
      hi: Object.freeze({
        'core-host-service-chart.empty':
          'अभी चित्र बनाने के लिए पर्याप्त सेवा इतिहास नहीं है',
        'core-host-service-chart.counter_help':
          'काउंटरों को दर में बदला जाता है; अंतर का अर्थ है कि सेवा फिर शुरू हुई।',
      }),
      ar: Object.freeze({
        'core-host-service-chart.empty': 'لا يكفي سجل الخدمات للرسم بعد',
        'core-host-service-chart.counter_help':
          'تُحوّل العدادات إلى معدل؛ وتعني الفجوة أن الخدمة أعيد تشغيلها.',
      }),
      zh: Object.freeze({
        'core-host-service-chart.empty': '服务历史不足，暂时无法绘制',
        'core-host-service-chart.counter_help':
          '计数器会转换为速率；间隔表示服务已重启。',
      }),
    }),
  }),
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
