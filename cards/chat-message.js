/* Core card package: a regular user or assistant conversation message. */
window.ZogographChatMessageCard = {
  manifest: Object.freeze({
    id: 'core-chat-message',
    locales: ['core-chat-message.'],
    helpers: ['renderMarkdown', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-message.assistant': 'Assistant message',
        'core-chat-message.user': 'Your message',
      }),
      es: Object.freeze({
        'core-chat-message.assistant': 'Mensaje del asistente',
        'core-chat-message.user': 'Su mensaje',
      }),
      hi: Object.freeze({
        'core-chat-message.assistant': 'सहायक का संदेश',
        'core-chat-message.user': 'आपका संदेश',
      }),
      ar: Object.freeze({
        'core-chat-message.assistant': 'رسالة المساعد',
        'core-chat-message.user': 'رسالتك',
      }),
      zh: Object.freeze({
        'core-chat-message.assistant': '助手消息',
        'core-chat-message.user': '您的消息',
      }),
    }),
  }),
  render(message, helpers) {
    const bubble = document.createElement('div');
    bubble.className = `chat-line chat-${message.role}`;
    bubble.setAttribute(
      'aria-label',
      helpers.i18n.t(
        message.role === 'assistant'
          ? 'core-chat-message.assistant'
          : 'core-chat-message.user',
        {},
        message.role === 'assistant' ? 'Assistant message' : 'Your message',
      ),
    );
    if (message.role === 'assistant')
      bubble.appendChild(helpers.renderMarkdown(message.content));
    else bubble.textContent = message.content;
    return bubble;
  },
};
