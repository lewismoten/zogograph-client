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
      en: Object.freeze({ 'core-chat-chart.loading': 'Loading chart…' }),
      es: Object.freeze({ 'core-chat-chart.loading': 'Cargando gráfico…' }),
      hi: Object.freeze({ 'core-chat-chart.loading': 'चार्ट लोड हो रहा है…' }),
      ar: Object.freeze({ 'core-chat-chart.loading': 'جارٍ تحميل المخطط…' }),
      zh: Object.freeze({ 'core-chat-chart.loading': '正在加载图表…' }),
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
            'chart.open_on_page',
            { page: helpers.viewLabel(drawn.page) },
            'Open it on {{page}}',
          );
          go.addEventListener('click', () => helpers.openView(drawn.page));
          card.appendChild(go);
        }
        if (!drawn.points)
          caption.textContent =
            helpers.i18n.t(
              'chart.no_data',
              {},
              'Nothing was recorded in this period.',
            ) +
            ' ' +
            (caption.textContent || '');
      } catch (error) {
        head.textContent = helpers.i18n.t(
          'chart.could_not_draw',
          {},
          'That chart could not be drawn',
        );
        caption.textContent = helpers.i18n.t(
          'chart.error',
          { error: String(error.message || error) },
          'Could not draw this visual: {{error}}',
        );
      }
    });
    return card;
  },
};
