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
        'core-chat-composer.add': 'Add',
        'core-chat-composer.add_help':
          'It is working. This goes in now and it reads it on its next turn.',
      }),
      es: Object.freeze({
        'core-chat-composer.prompt_placeholder': '¿Qué está fallando?',
        'core-chat-composer.ask': 'Preguntar',
        'core-chat-composer.add': 'Añadir',
        'core-chat-composer.add_help':
          'Está trabajando. Esto se incorpora ahora y lo leerá en su próximo turno.',
      }),
      hi: Object.freeze({
        'core-chat-composer.prompt_placeholder': 'क्या गलत हो रहा है?',
        'core-chat-composer.ask': 'पूछें',
        'core-chat-composer.add': 'जोड़ें',
        'core-chat-composer.add_help':
          'यह काम कर रहा है। यह अभी शामिल हो जाता है और अगली बारी में इसे पढ़ा जाएगा।',
      }),
      ar: Object.freeze({
        'core-chat-composer.prompt_placeholder': 'ما الذي يحدث بشكل خاطئ؟',
        'core-chat-composer.ask': 'اسأل',
        'core-chat-composer.add': 'إضافة',
        'core-chat-composer.add_help':
          'إنه يعمل الآن. سيُضاف هذا الآن ويقرأه في دوره التالي.',
      }),
      zh: Object.freeze({
        'core-chat-composer.prompt_placeholder': '出了什么问题？',
        'core-chat-composer.ask': '询问',
        'core-chat-composer.add': '添加',
        'core-chat-composer.add_help':
          '它正在工作。这会立即加入，并会在下一轮阅读。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-chat-composer.${key}`,
      {},
      this.manifest.catalogs.en[`core-chat-composer.${key}`],
    );
  },
  render(_input, helpers) {
    return {
      placeholder: this.text('prompt_placeholder', helpers),
      ask: this.text('ask', helpers),
    };
  },
};
