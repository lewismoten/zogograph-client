/* Core card package: marker for a compacted portion of a conversation. */
window.ZogographChatSummaryMarkerCard = {
  manifest: Object.freeze({
    id: 'core-chat-summary-marker',
    locales: ['core-chat-summary-marker.'],
    helpers: ['openSummary', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-summary-marker.count':
          '— everything above here was summarised ({{messages}} messages) —',
        'core-chat-summary-marker.marker':
          '— everything above here was summarised —',
        'core-chat-summary-marker.help':
          'Still yours to read. From here on the model receives the summary instead. Click to see it.',
      }),
      es: Object.freeze({
        'core-chat-summary-marker.count':
          '— todo lo anterior se resumió ({{messages}} mensajes) —',
        'core-chat-summary-marker.marker': '— todo lo anterior se resumió —',
        'core-chat-summary-marker.help':
          'Sigue siendo suyo para leerlo. Desde aquí el modelo recibe el resumen. Haga clic para verlo.',
      }),
      hi: Object.freeze({
        'core-chat-summary-marker.count':
          '— ऊपर की सभी बातें सारांशित की गईं ({{messages}} संदेश) —',
        'core-chat-summary-marker.marker':
          '— ऊपर की सभी बातें सारांशित की गईं —',
        'core-chat-summary-marker.help':
          'यह अभी भी पढ़ने के लिए आपका है। यहाँ से मॉडल को सारांश मिलता है। देखने के लिए क्लिक करें।',
      }),
      ar: Object.freeze({
        'core-chat-summary-marker.count':
          '— لُخّص كل ما سبق هنا ({{messages}} رسالة) —',
        'core-chat-summary-marker.marker': '— لُخّص كل ما سبق هنا —',
        'core-chat-summary-marker.help':
          'ما زال لك لقراءته. من هنا يتلقى النموذج الملخص بدلًا منه. انقر لرؤيته.',
      }),
      zh: Object.freeze({
        'core-chat-summary-marker.count':
          '— 上方内容已摘要（{{messages}} 条消息）—',
        'core-chat-summary-marker.marker': '— 上方内容已摘要 —',
        'core-chat-summary-marker.help':
          '您仍可阅读这些内容。模型从这里开始接收摘要。点击查看。',
      }),
    }),
  }),
  render(input, helpers) {
    const { message, session } = input;
    const { openSummary, i18n } = helpers;
    const covers = message.covers ?? {};
    const wrap = document.createElement('div');
    wrap.className = 'chat-summary';
    const head = document.createElement('button');
    head.type = 'button';
    head.className = 'chat-summary-head';
    head.textContent = i18n.t(
      covers.messages
        ? 'core-chat-summary-marker.count'
        : 'core-chat-summary-marker.marker',
      { messages: covers.messages },
      covers.messages
        ? '— everything above here was summarised ({{messages}} messages) —'
        : '— everything above here was summarised —',
    );
    head.title = i18n.t(
      'core-chat-summary-marker.help',
      {},
      'Still yours to read. From here on the model receives the summary instead. Click to see it.',
    );
    head.addEventListener('click', () => openSummary(message, session));
    wrap.appendChild(head);
    return wrap;
  },
};
