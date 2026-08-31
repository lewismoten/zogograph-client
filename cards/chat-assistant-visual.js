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
      }),
      es: Object.freeze({
        'core-chat-assistant-visual.title': 'Visual del asistente',
        'core-chat-assistant-visual.error':
          'No se pudo dibujar este elemento visual: {{error}}',
      }),
      hi: Object.freeze({
        'core-chat-assistant-visual.title': 'सहायक का दृश्य',
        'core-chat-assistant-visual.error':
          'यह दृश्य नहीं बनाया जा सका: {{error}}',
      }),
      ar: Object.freeze({
        'core-chat-assistant-visual.title': 'مرئي المساعد',
        'core-chat-assistant-visual.error':
          'تعذر رسم هذا العرض المرئي: {{error}}',
      }),
      zh: Object.freeze({
        'core-chat-assistant-visual.title': '助手图表',
        'core-chat-assistant-visual.error': '无法绘制此可视化内容：{{error}}',
      }),
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
          'core-chat-assistant-visual.error',
          { error: String(error.message || error) },
          'Could not draw this visual: {{error}}',
        );
      }
    });
    return card;
  },
};
