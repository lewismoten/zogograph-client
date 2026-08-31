/* Core card package: static close and reasoning controls for the persistent chat shell. */
window.ZogographChatShellControlsCard = {
  manifest: Object.freeze({
    id: 'core-chat-shell-controls',
    locales: ['core-chat-shell-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-shell-controls.effort_title': 'How hard to think',
        'core-chat-shell-controls.hide_title':
          'Hide this — the conversation carries on',
        'core-chat-shell-controls.close': 'Close chat',
      }),
      es: Object.freeze({
        'core-chat-shell-controls.effort_title': 'Cuánto razonar',
        'core-chat-shell-controls.hide_title':
          'Ocultar esto; la conversación continúa',
        'core-chat-shell-controls.close': 'Cerrar chat',
      }),
      hi: Object.freeze({
        'core-chat-shell-controls.effort_title': 'कितना विचार करना है',
        'core-chat-shell-controls.hide_title':
          'इसे छिपाएँ — बातचीत जारी रहती है',
        'core-chat-shell-controls.close': 'चैट बंद करें',
      }),
      ar: Object.freeze({
        'core-chat-shell-controls.effort_title': 'مدى عمق التفكير',
        'core-chat-shell-controls.hide_title': 'أخفِ هذا — تستمر المحادثة',
        'core-chat-shell-controls.close': 'أغلق الدردشة',
      }),
      zh: Object.freeze({
        'core-chat-shell-controls.effort_title': '思考强度',
        'core-chat-shell-controls.hide_title': '隐藏此窗口——对话会继续',
        'core-chat-shell-controls.close': '关闭聊天',
      }),
    }),
  }),
  render(_input, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-chat-shell-controls.${key}`, {}, fallback);
    return {
      effortTitle: t('effort_title', 'How hard to think'),
      hideTitle: t('hide_title', 'Hide this — the conversation carries on'),
      close: t('close', 'Close chat'),
    };
  },
};
