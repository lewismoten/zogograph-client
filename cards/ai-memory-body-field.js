/* Core card package: explanatory copy for a saved memory's body. */
window.ZogographAiMemoryBodyFieldCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-body-field',
    locales: ['core-ai-memory-body-field.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-body-field.help':
          'Written for somebody with none of the conversation it came from — no “as we discussed”, and say what it was based on.',
      }),
      es: Object.freeze({
        'core-ai-memory-body-field.help':
          'Escrito para alguien que no conoce la conversación de la que proviene: no diga «como comentamos» e indique en qué se basó.',
      }),
      hi: Object.freeze({
        'core-ai-memory-body-field.help':
          'उस व्यक्ति के लिए लिखें जिसे इसकी मूल बातचीत की कोई जानकारी नहीं है — “जैसा हमने चर्चा की” न लिखें और बताएं कि यह किस पर आधारित था।',
      }),
      ar: Object.freeze({
        'core-ai-memory-body-field.help':
          'اكتبها لشخص لا يعرف شيئًا عن المحادثة التي جاءت منها — لا تقل «كما ناقشنا»، واذكر ما استندت إليه.',
      }),
      zh: Object.freeze({
        'core-ai-memory-body-field.help':
          '要为不了解其来源对话的人而写——不要说“如我们讨论过的”，并说明它基于什么。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      'core-ai-memory-body-field.help',
      {},
      'Written for somebody with none of the conversation it came from — no “as we discussed”, and say what it was based on.',
    );
  },
};
