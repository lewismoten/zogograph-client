/* Core card package: extend the specific limit that paused a chat. */
window.ZogographChatExtendLimitCard = {
  manifest: Object.freeze({
    id: 'core-chat-extend-limit',
    locales: ['core-chat-extend-limit.'],
    helpers: ['extend', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-extend-limit.time': 'Give it longer',
        'core-chat-extend-limit.spend': 'Let it spend more',
        'core-chat-extend-limit.turns': 'Give it more turns',
        'core-chat-extend-limit.default': 'Raise the limit',
        'core-chat-extend-limit.help':
          'Doubles that one limit for this conversation only. Future conversation settings are not changed.',
      }),
      es: Object.freeze({
        'core-chat-extend-limit.time': 'Darle más tiempo',
        'core-chat-extend-limit.spend': 'Dejar que gaste más',
        'core-chat-extend-limit.turns': 'Darle más turnos',
        'core-chat-extend-limit.default': 'Aumentar el límite',
        'core-chat-extend-limit.help':
          'Duplica ese límite solo para esta conversación. No cambia la configuración futura.',
      }),
      hi: Object.freeze({
        'core-chat-extend-limit.time': 'इसे अधिक समय दें',
        'core-chat-extend-limit.spend': 'इसे अधिक खर्च करने दें',
        'core-chat-extend-limit.turns': 'इसे अधिक टर्न दें',
        'core-chat-extend-limit.default': 'सीमा बढ़ाएँ',
        'core-chat-extend-limit.help':
          'केवल इस बातचीत के लिए वह सीमा दोगुनी करता है।',
      }),
      ar: Object.freeze({
        'core-chat-extend-limit.time': 'امنحه وقتًا أطول',
        'core-chat-extend-limit.spend': 'اسمح له بإنفاق المزيد',
        'core-chat-extend-limit.turns': 'امنحه جولات أكثر',
        'core-chat-extend-limit.default': 'ارفع الحد',
        'core-chat-extend-limit.help': 'يضاعف ذلك الحد لهذه المحادثة فقط.',
      }),
      zh: Object.freeze({
        'core-chat-extend-limit.time': '给它更多时间',
        'core-chat-extend-limit.spend': '允许它花费更多',
        'core-chat-extend-limit.turns': '给它更多轮次',
        'core-chat-extend-limit.default': '提高限制',
        'core-chat-extend-limit.help':
          '仅对此对话将该限制加倍。不会更改未来对话设置。',
      }),
    }),
  }),
  render(input, helpers) {
    if (!input.which) return null;
    const labels = { time: 'time', spend: 'spend', turns: 'turns' };
    const button = document.createElement('button');
    button.className = 'link';
    button.textContent = helpers.i18n.t(
      `core-chat-extend-limit.${labels[input.which] || 'default'}`,
      {},
      'Raise the limit',
    );
    button.title = helpers.i18n.t(
      'core-chat-extend-limit.help',
      {},
      'Doubles that one limit for this conversation only. Future conversation settings are not changed.',
    );
    button.addEventListener('click', helpers.extend);
    return button;
  },
};
