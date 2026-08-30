/* Core card package: one compact tool-call line in a chat transcript. */
window.ZogographChatToolCallCard = {
  manifest: Object.freeze({
    id: 'core-chat-tool-call',
    locales: ['core-chat-tool-call.'],
    helpers: ['toolPhrase', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-chat-tool-call.prefix': '⚙ {{phrase}}' }),
      es: Object.freeze({ 'core-chat-tool-call.prefix': '⚙ {{phrase}}' }),
      hi: Object.freeze({ 'core-chat-tool-call.prefix': '⚙ {{phrase}}' }),
      ar: Object.freeze({ 'core-chat-tool-call.prefix': '⚙ {{phrase}}' }),
      zh: Object.freeze({ 'core-chat-tool-call.prefix': '⚙ {{phrase}}' }),
    }),
  }),
  render(input, helpers) {
    const line = document.createElement('div');
    line.className = 'chat-tool';
    line.textContent = helpers.i18n.t(
      'core-chat-tool-call.prefix',
      { phrase: helpers.toolPhrase(input) },
      '⚙ {{phrase}}',
    );
    line.title = JSON.stringify(input.arguments, null, 2);
    return line;
  },
};
