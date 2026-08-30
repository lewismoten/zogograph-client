/* Core card package: return to the live end of a chat log. */
window.ZogographChatCatchUpCard = {
  manifest: Object.freeze({
    id: 'core-chat-catch-up',
    locales: ['core-chat-catch-up.'],
    helpers: ['reset', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-catch-up.busy':
          '{{assistant}} is still going — jump to the end ↓',
        'core-chat-catch-up.idle': 'Jump to the end ↓',
      }),
      es: Object.freeze({
        'core-chat-catch-up.busy':
          '{{assistant}} sigue trabajando — ir al final ↓',
        'core-chat-catch-up.idle': 'Ir al final ↓',
      }),
      hi: Object.freeze({
        'core-chat-catch-up.busy':
          '{{assistant}} अभी भी काम कर रहा है — अंत पर जाएँ ↓',
        'core-chat-catch-up.idle': 'अंत पर जाएँ ↓',
      }),
      ar: Object.freeze({
        'core-chat-catch-up.busy':
          '{{assistant}} ما زال يعمل — انتقل إلى النهاية ↓',
        'core-chat-catch-up.idle': 'انتقل إلى النهاية ↓',
      }),
      zh: Object.freeze({
        'core-chat-catch-up.busy': '{{assistant}} 仍在处理中 — 跳转至末尾 ↓',
        'core-chat-catch-up.idle': '跳转至末尾 ↓',
      }),
    }),
  }),
  render(input, helpers) {
    let button = input.button;
    if (!button) {
      button = document.createElement('button');
      button.id = 'chatCatchUp';
      button.className = 'chat-catchup';
      button.type = 'button';
      button.addEventListener('click', helpers.reset);
      input.log.parentElement.insertBefore(button, input.log.nextSibling);
    }
    const behind =
      input.pinnedUp &&
      input.log.scrollHeight - input.log.scrollTop - input.log.clientHeight >
        60;
    button.hidden = !behind;
    if (behind) {
      const busy = [
        'thinking',
        'waiting_permission',
        'waiting_browser',
      ].includes(input.session.state);
      button.textContent = helpers.i18n.t(
        busy ? 'core-chat-catch-up.busy' : 'core-chat-catch-up.idle',
        { assistant: input.assistant },
        busy
          ? '{{assistant}} is still going — jump to the end ↓'
          : 'Jump to the end ↓',
      );
    }
    return null;
  },
};
