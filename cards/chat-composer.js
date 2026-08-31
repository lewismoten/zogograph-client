/* Core card package: the persistent assistant prompt composer. */
window.ZogographChatComposerCard = {
  manifest: Object.freeze({
    id: 'core-chat-composer',
    locales: ['core-chat-composer.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-composer.prompt_placeholder': 'What is going wrong?',
        'core-chat-composer.ask': 'Ask',
      }),
      es: Object.freeze({
        'core-chat-composer.prompt_placeholder': '¿Qué está fallando?',
        'core-chat-composer.ask': 'Preguntar',
      }),
      hi: Object.freeze({
        'core-chat-composer.prompt_placeholder': 'क्या गलत हो रहा है?',
        'core-chat-composer.ask': 'पूछें',
      }),
      ar: Object.freeze({
        'core-chat-composer.prompt_placeholder': 'ما الذي يحدث بشكل خاطئ؟',
        'core-chat-composer.ask': 'اسأل',
      }),
      zh: Object.freeze({
        'core-chat-composer.prompt_placeholder': '出了什么问题？',
        'core-chat-composer.ask': '询问',
      }),
    }),
  }),
  render(_input, helpers) {
    return {
      placeholder: helpers.i18n.t(
        'core-chat-composer.prompt_placeholder',
        {},
        'What is going wrong?',
      ),
      ask: helpers.i18n.t('core-chat-composer.ask', {}, 'Ask'),
    };
  },
};
