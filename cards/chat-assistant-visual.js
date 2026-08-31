/* Core card package: a chart rendered from assistant-supplied values. */
window.ZogographChatAssistantVisualCard = {
  manifest: Object.freeze({
    id: 'core-chat-assistant-visual',
    locales: ['core-chat-assistant-visual.'],
    helpers: ['charts', 'draw', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-assistant-visual.title': 'Assistant visual',
      }),
      es: Object.freeze({
        'core-chat-assistant-visual.title': 'Visual del asistente',
      }),
      hi: Object.freeze({
        'core-chat-assistant-visual.title': 'सहायक का दृश्य',
      }),
      ar: Object.freeze({ 'core-chat-assistant-visual.title': 'مرئي المساعد' }),
      zh: Object.freeze({ 'core-chat-assistant-visual.title': '助手图表' }),
    }),
  }),
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
          'chart.error',
          { error: String(error.message || error) },
          'Could not draw this visual: {{error}}',
        );
      }
    });
    return card;
  },
};
