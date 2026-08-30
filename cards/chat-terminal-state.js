/* Core card package: terminal chat state message. */
window.ZogographChatTerminalStateCard = {
  manifest: Object.freeze({
    id: 'core-chat-terminal-state',
    locales: ['core-chat-terminal-state.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-terminal-state.stopped': 'Stopped — {{reason}}.',
        'core-chat-terminal-state.ended': 'Finished — {{reason}}.',
        'core-chat-terminal-state.failed': 'Gave up — {{reason}}.',
      }),
      es: Object.freeze({
        'core-chat-terminal-state.stopped': 'Detenido — {{reason}}.',
        'core-chat-terminal-state.ended': 'Finalizado — {{reason}}.',
        'core-chat-terminal-state.failed': 'Se rindió — {{reason}}.',
      }),
      hi: Object.freeze({
        'core-chat-terminal-state.stopped': 'रुक गया — {{reason}}.',
        'core-chat-terminal-state.ended': 'समाप्त — {{reason}}.',
        'core-chat-terminal-state.failed': 'हार मान ली — {{reason}}.',
      }),
      ar: Object.freeze({
        'core-chat-terminal-state.stopped': 'توقف — {{reason}}.',
        'core-chat-terminal-state.ended': 'انتهى — {{reason}}.',
        'core-chat-terminal-state.failed': 'تعذّر المتابعة — {{reason}}.',
      }),
      zh: Object.freeze({
        'core-chat-terminal-state.stopped': '已停止 — {{reason}}。',
        'core-chat-terminal-state.ended': '已完成 — {{reason}}。',
        'core-chat-terminal-state.failed': '已放弃 — {{reason}}。',
      }),
    }),
  }),
  render(input, helpers) {
    if (!['stopped', 'ended', 'failed'].includes(input.state)) return null;
    const defaults = {
      stopped: 'by you',
      ended: 'a limit was reached',
      failed: 'the model could not be reached',
    };
    const line = document.createElement('span');
    line.className = 'muted';
    line.textContent = helpers.i18n.t(
      `core-chat-terminal-state.${input.state}`,
      { reason: input.reason || defaults[input.state] },
      '',
    );
    return line;
  },
};
