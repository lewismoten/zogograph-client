/* Core card package: title for an untitled saved conversation. */
window.ZogographChatTranscriptTitleCard = {
  manifest: Object.freeze({
    id: 'core-chat-transcript-title',
    locales: ['core-chat-transcript-title.'],
    helpers: ['whenLabel', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-transcript-title.fallback': 'Conversation of {{when}}',
      }),
      es: Object.freeze({
        'core-chat-transcript-title.fallback': 'Conversación del {{when}}',
      }),
      hi: Object.freeze({
        'core-chat-transcript-title.fallback': '{{when}} की बातचीत',
      }),
      ar: Object.freeze({
        'core-chat-transcript-title.fallback': 'محادثة {{when}}',
      }),
      zh: Object.freeze({
        'core-chat-transcript-title.fallback': '{{when}} 的对话',
      }),
    }),
  }),

  render(input, helpers) {
    const { whenLabel, i18n } = helpers;
    return (
      input.title ||
      i18n.t(
        'core-chat-transcript-title.fallback',
        { when: whenLabel(input.startedAt) },
        'Conversation of {{when}}',
      )
    );
  },
};
