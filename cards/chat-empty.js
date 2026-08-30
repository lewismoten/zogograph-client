/* Core card package: guidance before the first chat message. */
window.ZogographChatEmptyCard = {
  manifest: Object.freeze({
    id: 'core-chat-empty',
    locales: ['core-chat-empty.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-empty.guidance':
          'Describe what is going wrong and {{assistant}} will look into it. It can read this machine’s measurements and logs, and can take you to the page that shows what it found.',
      }),
      es: Object.freeze({
        'core-chat-empty.guidance':
          'Describe qué está fallando y {{assistant}} lo investigará. Puede leer las mediciones y registros de esta máquina y llevarte a la página con lo que encontró.',
      }),
      hi: Object.freeze({
        'core-chat-empty.guidance':
          'बताएँ कि क्या गलत हो रहा है और {{assistant}} इसकी जाँच करेगा। यह इस मशीन के माप और लॉग पढ़ सकता है।',
      }),
      ar: Object.freeze({
        'core-chat-empty.guidance':
          'صِف ما يحدث بشكل خاطئ وسيتحقق {{assistant}} منه. يمكنه قراءة قياسات وسجلات هذا الجهاز.',
      }),
      zh: Object.freeze({
        'core-chat-empty.guidance':
          '请说明出了什么问题，{{assistant}} 会进行检查。它可以读取此机器的测量数据和日志。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.note(
      helpers.i18n.t(
        'core-chat-empty.guidance',
        { assistant: input.assistant },
        'Describe what is going wrong and {{assistant}} will look into it. It can read this machine’s measurements and logs, and can take you to the page that shows what it found.',
      ),
    );
  },
};
