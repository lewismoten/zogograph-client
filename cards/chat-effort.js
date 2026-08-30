/* Core card package: per-conversation reasoning-effort selector. */
window.ZogographChatEffortCard = {
  manifest: Object.freeze({
    id: 'core-chat-effort',
    locales: ['core-chat-effort.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-effort.title':
          'Thinking: {{value}}. The setting allows up to {{ceiling}}; this turns it down for this conversation only.',
      }),
      es: Object.freeze({
        'core-chat-effort.title':
          'Razonamiento: {{value}}. La configuración permite hasta {{ceiling}}; esto solo lo reduce para esta conversación.',
      }),
      hi: Object.freeze({
        'core-chat-effort.title':
          'सोच: {{value}}। सेटिंग {{ceiling}} तक अनुमति देती है; यह केवल इस बातचीत के लिए कम करता है।',
      }),
      ar: Object.freeze({
        'core-chat-effort.title':
          'التفكير: {{value}}. يسمح الإعداد حتى {{ceiling}}؛ وهذا يخفضه لهذه المحادثة فقط.',
      }),
      zh: Object.freeze({
        'core-chat-effort.title':
          '思考：{{value}}。设置最高允许 {{ceiling}}；这仅对此对话降低强度。',
      }),
    }),
  }),
  render(input, helpers) {
    const { picker, session } = input;
    const ceiling = session.effort_ceiling ?? '';
    const levels = session.effort_levels ?? [];
    if (ceiling === '' || !levels.length) {
      picker.hidden = true;
      return null;
    }
    picker.hidden = false;
    const upTo = levels.slice(0, levels.indexOf(ceiling) + 1);
    if (picker.dataset.built !== upTo.join(',')) {
      picker.replaceChildren();
      for (const level of upTo) {
        const option = document.createElement('option');
        option.value = level;
        option.textContent = level;
        picker.appendChild(option);
      }
      picker.dataset.built = upTo.join(',');
    }
    picker.value = session.effort || ceiling;
    picker.title = helpers.i18n.t(
      'core-chat-effort.title',
      { value: picker.value, ceiling },
      'Thinking: {{value}}. The setting allows up to {{ceiling}}; this turns it down for this conversation only.',
    );
    return null;
  },
};
