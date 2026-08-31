/* Core card package: accessible names for the persistent chat shell. */
window.ZogographChatShellAccessibilityCard = {
  manifest: Object.freeze({
    id: 'core-chat-shell-accessibility',
    locales: ['core-chat-shell-accessibility.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-shell-accessibility.panel_label':
          'The troubleshooting assistant',
        'core-chat-shell-accessibility.drag_window':
          'Drag to move this chat window',
      }),
      es: Object.freeze({
        'core-chat-shell-accessibility.panel_label':
          'El asistente de resolución de problemas',
        'core-chat-shell-accessibility.drag_window':
          'Arrastre para mover esta ventana de chat',
      }),
      hi: Object.freeze({
        'core-chat-shell-accessibility.panel_label': 'समस्या-निवारण सहायक',
        'core-chat-shell-accessibility.drag_window':
          'इस चैट विंडो को स्थानांतरित करने के लिए खींचें',
      }),
      ar: Object.freeze({
        'core-chat-shell-accessibility.panel_label': 'مساعد استكشاف المشكلات',
        'core-chat-shell-accessibility.drag_window':
          'اسحب لتحريك نافذة الدردشة هذه',
      }),
      zh: Object.freeze({
        'core-chat-shell-accessibility.panel_label': '故障排查助手',
        'core-chat-shell-accessibility.drag_window': '拖动以移动此聊天窗口',
      }),
    }),
  }),
  render(_input, helpers) {
    return {
      panelLabel: helpers.i18n.t(
        'core-chat-shell-accessibility.panel_label',
        {},
        'The troubleshooting assistant',
      ),
      dragWindow: helpers.i18n.t(
        'core-chat-shell-accessibility.drag_window',
        {},
        'Drag to move this chat window',
      ),
    };
  },
};
