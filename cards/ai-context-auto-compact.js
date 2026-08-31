/* Core card package: automatic conversation summarisation switch. */
window.ZogographAiContextAutoCompactCard = {
  manifest: Object.freeze({
    id: 'core-ai-context-auto-compact',
    locales: ['core-ai-context-auto-compact.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-context-auto-compact.label': 'Summarise automatically',
        'core-ai-context-auto-compact.help':
          'On, it happens on the next turn after the mark is passed. Off, the ring in the chat window goes amber and waits for you to click it — and the conversation will eventually be refused by the provider if you never do.',
      }),
      es: Object.freeze({
        'core-ai-context-auto-compact.label': 'Resumir automáticamente',
        'core-ai-context-auto-compact.help':
          'Al activarlo, ocurre en el siguiente turno tras superar la marca. Al desactivarlo, el anillo espera a que haga clic y el proveedor acabará rechazando la conversación.',
      }),
      hi: Object.freeze({
        'core-ai-context-auto-compact.label': 'स्वचालित रूप से सारांश बनाएँ',
        'core-ai-context-auto-compact.help':
          'चालू होने पर सीमा पार होने के अगले टर्न में यह होता है। बंद होने पर चैट का घेरा क्लिक की प्रतीक्षा करता है और प्रदाता अंततः बातचीत अस्वीकार कर देगा।',
      }),
      ar: Object.freeze({
        'core-ai-context-auto-compact.label': 'لخّص تلقائيًا',
        'core-ai-context-auto-compact.help':
          'عند تشغيله يحدث ذلك في الدور التالي بعد تجاوز العلامة. عند إيقافه تنتظر حلقة المحادثة نقرك وسيَرفض المزوّد المحادثة في النهاية.',
      }),
      zh: Object.freeze({
        'core-ai-context-auto-compact.label': '自动总结',
        'core-ai-context-auto-compact.help':
          '开启后，会在超过标记后的下一轮执行。关闭后，聊天中的圆环会等待您点击，提供商最终将拒绝该对话。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'checkbox';
    input.id = 'aiCompactAuto';
    input.checked = data.compact_automatically !== false;
    return helpers.fieldRow(
      helpers.i18n.t(
        'core-ai-context-auto-compact.label',
        {},
        'Summarise automatically',
      ),
      input,
      helpers.i18n.t(
        'core-ai-context-auto-compact.help',
        {},
        'On, it happens on the next turn after the mark is passed. Off, the ring in the chat window goes amber and waits for you to click it — and the conversation will eventually be refused by the provider if you never do.',
      ),
    );
  },
};
