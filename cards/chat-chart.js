/* Core card package: a built-in dashboard chart shown in conversation. */
window.ZogographChatChartCard = {
  manifest: Object.freeze({
    id: 'core-chat-chart',
    locales: ['core-chat-chart.'],
    helpers: [
      'charts',
      'humanSpan',
      'renderChart',
      'viewLabel',
      'openView',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-chart.loading': 'Loading chart…',
        'core-chat-chart.open_on_page': 'Open it on {{page}}',
        'core-chat-chart.no_data': 'Nothing was recorded in this period.',
        'core-chat-chart.could_not_draw': 'That chart could not be drawn',
        'core-chat-chart.error': 'Could not draw this visual: {{error}}',
      }),
      es: Object.freeze({
        'core-chat-chart.loading': 'Cargando gráfico…',
        'core-chat-chart.open_on_page': 'Abrir en {{page}}',
        'core-chat-chart.no_data': 'No se registró nada en este período.',
        'core-chat-chart.could_not_draw': 'No se pudo dibujar ese gráfico',
        'core-chat-chart.error':
          'No se pudo dibujar este elemento visual: {{error}}',
      }),
      hi: Object.freeze({
        'core-chat-chart.loading': 'चार्ट लोड हो रहा है…',
        'core-chat-chart.open_on_page': 'इसे {{page}} पर खोलें',
        'core-chat-chart.no_data': 'इस अवधि में कुछ भी दर्ज नहीं हुआ।',
        'core-chat-chart.could_not_draw': 'वह चार्ट नहीं बनाया जा सका',
        'core-chat-chart.error': 'यह दृश्य नहीं बनाया जा सका: {{error}}',
      }),
      ar: Object.freeze({
        'core-chat-chart.loading': 'جارٍ تحميل المخطط…',
        'core-chat-chart.open_on_page': 'افتحه في {{page}}',
        'core-chat-chart.no_data': 'لم يُسجَّل شيء خلال هذه الفترة.',
        'core-chat-chart.could_not_draw': 'تعذر رسم هذا المخطط',
        'core-chat-chart.error': 'تعذر رسم هذا العرض المرئي: {{error}}',
      }),
      zh: Object.freeze({
        'core-chat-chart.loading': '正在加载图表…',
        'core-chat-chart.open_on_page': '在 {{page}} 中打开',
        'core-chat-chart.no_data': '此时间段内没有记录任何内容。',
        'core-chat-chart.could_not_draw': '无法绘制该图表',
        'core-chat-chart.error': '无法绘制此可视化内容：{{error}}',
      }),
    }),
  }),
  render(message, helpers) {
    const cached = helpers.charts.get(message.id);
    if (cached) return cached;
    const card = document.createElement('div');
    card.className = 'chat-chart';
    card.dataset.message = String(message.id);
    const head = document.createElement('div');
    head.className = 'chat-chart-head';
    head.textContent = helpers.i18n.t(
      'core-chat-chart.loading',
      {},
      'Loading chart…',
    );
    const canvas = document.createElement('canvas');
    canvas.className = 'chat-chart-canvas';
    const caption = document.createElement('div');
    caption.className = 'muted small';
    caption.textContent = message.arguments.why || '';
    card.append(head, canvas, caption);
    helpers.charts.set(message.id, card);
    requestAnimationFrame(async () => {
      try {
        const drawn = await helpers.renderChart(
          canvas,
          message.arguments.chart,
          Number(message.arguments.hours) || 24,
        );
        head.textContent = `${drawn.name} · ${helpers.humanSpan(Number(message.arguments.hours) * 3600, true)}`;
        if (drawn.page && helpers.viewLabel(drawn.page)) {
          const go = document.createElement('button');
          go.className = 'link small';
          go.textContent = helpers.i18n.t(
            'core-chat-chart.open_on_page',
            { page: helpers.viewLabel(drawn.page) },
            'Open it on {{page}}',
          );
          go.addEventListener('click', () => helpers.openView(drawn.page));
          card.appendChild(go);
        }
        if (!drawn.points)
          caption.textContent =
            helpers.i18n.t(
              'core-chat-chart.no_data',
              {},
              'Nothing was recorded in this period.',
            ) +
            ' ' +
            (caption.textContent || '');
      } catch (error) {
        head.textContent = helpers.i18n.t(
          'core-chat-chart.could_not_draw',
          {},
          'That chart could not be drawn',
        );
        caption.textContent = helpers.i18n.t(
          'core-chat-chart.error',
          { error: String(error.message || error) },
          'Could not draw this visual: {{error}}',
        );
      }
    });
    return card;
  },
};
