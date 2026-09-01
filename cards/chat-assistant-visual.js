/* Core card package: a chart rendered from assistant-supplied values. */
window.ZogographChatAssistantVisualCard = {
  manifest: Object.freeze({
    id: 'core-chat-assistant-visual',
    locales: ['core-chat-assistant-visual.'],
    helpers: ['charts', 'draw', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-assistant-visual.title': 'Assistant visual',
        'core-chat-assistant-visual.error':
          'Could not draw this visual: {{error}}',
        'core-chat-assistant-visual.item': 'item',
        'core-chat-assistant-visual.pie_data_error':
          'Pie data needs numeric slices.',
        'core-chat-assistant-visual.gauge_data_error':
          'Gauge data needs a value and a positive maximum.',
        'core-chat-assistant-visual.series': 'series',
        'core-chat-assistant-visual.line_data_error':
          'Line data needs series and points.',
      }),
      es: Object.freeze({
        'core-chat-assistant-visual.title': 'Visual del asistente',
        'core-chat-assistant-visual.error':
          'No se pudo dibujar este elemento visual: {{error}}',
        'core-chat-assistant-visual.item': 'elemento',
        'core-chat-assistant-visual.pie_data_error':
          'Los datos circulares necesitan sectores numéricos.',
        'core-chat-assistant-visual.gauge_data_error':
          'Los datos del indicador necesitan un valor y un máximo positivo.',
        'core-chat-assistant-visual.series': 'serie',
        'core-chat-assistant-visual.line_data_error':
          'Los datos de línea necesitan series y puntos.',
      }),
      hi: Object.freeze({
        'core-chat-assistant-visual.title': 'सहायक का दृश्य',
        'core-chat-assistant-visual.error':
          'यह दृश्य नहीं बनाया जा सका: {{error}}',
        'core-chat-assistant-visual.item': 'वस्तु',
        'core-chat-assistant-visual.pie_data_error':
          'पाई डेटा में संख्यात्मक खंड होने चाहिए।',
        'core-chat-assistant-visual.gauge_data_error':
          'गेज डेटा में एक मान और धनात्मक अधिकतम होना चाहिए।',
        'core-chat-assistant-visual.series': 'श्रृंखला',
        'core-chat-assistant-visual.line_data_error':
          'लाइन डेटा में श्रृंखला और बिंदु होने चाहिए।',
      }),
      ar: Object.freeze({
        'core-chat-assistant-visual.title': 'مرئي المساعد',
        'core-chat-assistant-visual.error':
          'تعذر رسم هذا العرض المرئي: {{error}}',
        'core-chat-assistant-visual.item': 'عنصر',
        'core-chat-assistant-visual.pie_data_error':
          'تحتاج بيانات المخطط الدائري إلى شرائح رقمية.',
        'core-chat-assistant-visual.gauge_data_error':
          'تحتاج بيانات المقياس إلى قيمة وحد أقصى موجب.',
        'core-chat-assistant-visual.series': 'سلسلة',
        'core-chat-assistant-visual.line_data_error':
          'تحتاج بيانات الخط إلى سلاسل ونقاط.',
      }),
      zh: Object.freeze({
        'core-chat-assistant-visual.title': '助手图表',
        'core-chat-assistant-visual.error': '无法绘制此可视化内容：{{error}}',
        'core-chat-assistant-visual.item': '项目',
        'core-chat-assistant-visual.pie_data_error': '饼图数据需要数值切片。',
        'core-chat-assistant-visual.gauge_data_error':
          '仪表数据需要一个值和正的最大值。',
        'core-chat-assistant-visual.series': '系列',
        'core-chat-assistant-visual.line_data_error':
          '折线数据需要系列和数据点。',
      }),
    }),
  }),
  text(key, helpers, values = {}) {
    const id = `core-chat-assistant-visual.${key}`;
    return helpers.i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
  render(message, helpers) {
    const cached = helpers.charts.get(message.id);
    if (cached) return cached;
    const card = document.createElement('div');
    card.className = 'chat-chart';
    const head = document.createElement('div');
    head.className = 'chat-chart-head';
    head.textContent =
      message.arguments.title ||
      helpers.i18n.t(
        'core-chat-assistant-visual.title',
        {},
        'Assistant visual',
      );
    const canvas = document.createElement('canvas');
    canvas.className = 'chat-chart-canvas';
    const caption = document.createElement('div');
    caption.className = 'muted small';
    caption.textContent = message.arguments.why || '';
    card.append(head, canvas, caption);
    helpers.charts.set(message.id, card);
    requestAnimationFrame(() => {
      try {
        helpers.draw(canvas, message.arguments);
      } catch (error) {
        caption.textContent = helpers.i18n.t(
          'core-chat-assistant-visual.error',
          { error: String(error.message || error) },
          'Could not draw this visual: {{error}}',
        );
      }
    });
    return card;
  },
};
