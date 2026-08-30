/* Core card package: empty-chat message prompt. */
window.ZogographChatPlaceholderCard = {
  manifest: Object.freeze({
    id: 'core-chat-placeholder',
    locales: ['core-chat-placeholder.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-placeholder.prompt': 'What is going wrong?',
      }),
      es: Object.freeze({
        'core-chat-placeholder.prompt': '¿Qué está saliendo mal?',
      }),
      hi: Object.freeze({
        'core-chat-placeholder.prompt': 'क्या गलत हो रहा है?',
      }),
      ar: Object.freeze({
        'core-chat-placeholder.prompt': 'ما الذي يحدث بشكل خاطئ؟',
      }),
      zh: Object.freeze({ 'core-chat-placeholder.prompt': '出了什么问题？' }),
    }),
  }),
  render(input, helpers) {
    if (input.box && input.box.value === '')
      input.box.placeholder = helpers.i18n.t(
        'core-chat-placeholder.prompt',
        {},
        'What is going wrong?',
      );
    return null;
  },
};
