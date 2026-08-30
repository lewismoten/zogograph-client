/* Core card package: restart a completed chat session. */
window.ZogographChatRestartCard = {
  manifest: Object.freeze({
    id: 'core-chat-restart',
    locales: ['core-chat-restart.'],
    helpers: ['startChat', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-chat-restart.action': 'Start a new one' }),
      es: Object.freeze({ 'core-chat-restart.action': 'Iniciar una nueva' }),
      hi: Object.freeze({ 'core-chat-restart.action': 'नई बातचीत शुरू करें' }),
      ar: Object.freeze({ 'core-chat-restart.action': 'ابدأ واحدة جديدة' }),
      zh: Object.freeze({ 'core-chat-restart.action': '开始新的对话' }),
    }),
  }),
  render(input, helpers) {
    if (!['stopped', 'ended', 'failed'].includes(input.state)) return null;
    const button = document.createElement('button');
    button.className = 'link';
    button.textContent = helpers.i18n.t(
      'core-chat-restart.action',
      {},
      'Start a new one',
    );
    button.addEventListener('click', helpers.startChat);
    return button;
  },
};
