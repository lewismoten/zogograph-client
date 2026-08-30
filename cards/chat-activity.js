/* Core card package: transient activity line in an active chat. */
window.ZogographChatActivityCard = {
  manifest: Object.freeze({
    id: 'core-chat-activity',
    locales: ['core-chat-activity.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-activity.thinking': 'Thinking…',
        'core-chat-activity.browser': 'Doing something in this window…',
      }),
      es: Object.freeze({
        'core-chat-activity.thinking': 'Pensando…',
        'core-chat-activity.browser': 'Haciendo algo en esta ventana…',
      }),
      hi: Object.freeze({
        'core-chat-activity.thinking': 'सोच रहा है…',
        'core-chat-activity.browser': 'इस विंडो में कुछ कर रहा है…',
      }),
      ar: Object.freeze({
        'core-chat-activity.thinking': 'يفكّر…',
        'core-chat-activity.browser': 'ينفذ شيئًا في هذه النافذة…',
      }),
      zh: Object.freeze({
        'core-chat-activity.thinking': '正在思考…',
        'core-chat-activity.browser': '正在此窗口中执行操作…',
      }),
    }),
  }),
  render(input, helpers) {
    const key =
      input.state === 'thinking'
        ? 'thinking'
        : input.state === 'waiting_browser'
          ? 'browser'
          : '';
    if (!key) return null;
    const line = document.createElement('span');
    line.className = 'chat-thinking';
    line.textContent = helpers.i18n.t(`core-chat-activity.${key}`, {}, '');
    return line;
  },
};
