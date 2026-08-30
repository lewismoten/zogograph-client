/* Core card package: unavailable chat explanation and setup link. */
window.ZogographChatUnavailableCard = {
  manifest: Object.freeze({
    id: 'core-chat-unavailable',
    locales: ['core-chat-unavailable.'],
    helpers: ['note', 'openSetup', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-unavailable.setup':
          'It is set up under System → {{assistant}}.',
        'core-chat-unavailable.action': 'Set it up',
      }),
      es: Object.freeze({
        'core-chat-unavailable.setup':
          'Se configura en Sistema → {{assistant}}.',
        'core-chat-unavailable.action': 'Configúralo',
      }),
      hi: Object.freeze({
        'core-chat-unavailable.setup':
          'इसे सिस्टम → {{assistant}} में सेट किया जाता है।',
        'core-chat-unavailable.action': 'इसे सेट करें',
      }),
      ar: Object.freeze({
        'core-chat-unavailable.setup': 'يُعد من النظام ← {{assistant}}.',
        'core-chat-unavailable.action': 'أعدّه',
      }),
      zh: Object.freeze({
        'core-chat-unavailable.setup': '请在系统 → {{assistant}} 下完成设置。',
        'core-chat-unavailable.action': '前往设置',
      }),
    }),
  }),
  render(input, helpers) {
    const { note, openSetup, i18n } = helpers;
    const t = (key, fallback) =>
      i18n.t(key, { assistant: input.assistant }, fallback);
    const wrap = document.createDocumentFragment();
    wrap.append(
      note(
        `${input.why} ${t('core-chat-unavailable.setup', 'It is set up under System → {{assistant}}.')}`,
      ),
    );
    const go = document.createElement('button');
    go.className = 'link';
    go.textContent = t('core-chat-unavailable.action', 'Set it up');
    go.addEventListener('click', openSetup);
    wrap.append(go);
    return wrap;
  },
};
