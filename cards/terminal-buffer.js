/* Core card package: terminal transcript rendering and scroll restoration. */
window.ZogographTerminalBufferCard = {
  manifest: Object.freeze({
    id: 'core-terminal-buffer',
    locales: ['core-terminal-buffer.'],
    helpers: ['target', 'count', 'terminalEntry', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-buffer.folded':
          '{{count}} not drawn. They are still in this session — open it under Sessions to read them.',
      }),
      es: Object.freeze({
        'core-terminal-buffer.folded':
          '{{count}} no se muestran. Siguen en esta sesión; ábrala en Sesiones para leerlos.',
      }),
      hi: Object.freeze({
        'core-terminal-buffer.folded':
          '{{count}} नहीं दिखाए गए हैं। वे इसी सत्र में हैं — उन्हें पढ़ने के लिए Sessions में खोलें।',
      }),
      ar: Object.freeze({
        'core-terminal-buffer.folded':
          'لم يُعرض {{count}}. ما زالت في هذه الجلسة — افتحها ضمن الجلسات لقراءتها.',
      }),
      zh: Object.freeze({
        'core-terminal-buffer.folded':
          '未显示 {{count}}。它们仍在此会话中，请在“会话”中打开阅读。',
      }),
    }),
  }),

  render(input, helpers) {
    const { target, count, terminalEntry, i18n } = helpers;
    if (!target) return;
    const { entries, dropped, pinned, savedScrollTop } = input;
    const fragment = document.createDocumentFragment();
    if (dropped > 0) {
      const said = document.createElement('div');
      said.className = 'term-folded';
      said.textContent = i18n.t(
        'core-terminal-buffer.folded',
        { count: count(dropped, 'earlier command') },
        '{{count}} not drawn. They are still in this session — open it under Sessions to read them.',
      );
      fragment.appendChild(said);
    }
    for (const entry of entries) fragment.appendChild(terminalEntry(entry));

    const hadContent = target.childElementCount > 0;
    const wasPinned =
      hadContent &&
      target.scrollHeight - target.scrollTop - target.clientHeight < 40;
    const wasAt = target.scrollTop;
    target.replaceChildren(fragment);
    if (wasPinned || pinned) target.scrollTop = target.scrollHeight;
    else if (hadContent) target.scrollTop = wasAt;
    else if (savedScrollTop !== null) target.scrollTop = savedScrollTop;
    else target.scrollTop = target.scrollHeight;
  },
};
