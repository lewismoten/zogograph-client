/* Core card package: one tool's permission-gate cell. */
window.ZogographAiToolPermissionCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-permission',
    locales: ['core-ai-tool-permission.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-permission.asks': 'it asks you anyway',
        'core-ai-tool-permission.asks_title':
          'This tool exists to put a question to you. There is nothing it could do that you would want warning of.',
        'core-ai-tool-permission.recommended': 'recommended',
      }),
      es: Object.freeze({
        'core-ai-tool-permission.asks': 'ya le pregunta',
        'core-ai-tool-permission.asks_title':
          'Esta herramienta existe para hacerle una pregunta.',
        'core-ai-tool-permission.recommended': 'recomendado',
      }),
      hi: Object.freeze({
        'core-ai-tool-permission.asks': 'यह आपसे वैसे भी पूछता है',
        'core-ai-tool-permission.asks_title':
          'यह टूल आपसे प्रश्न पूछने के लिए है।',
        'core-ai-tool-permission.recommended': 'अनुशंसित',
      }),
      ar: Object.freeze({
        'core-ai-tool-permission.asks': 'يسألك على أي حال',
        'core-ai-tool-permission.asks_title':
          'هذه الأداة موجودة لتطرح عليك سؤالًا.',
        'core-ai-tool-permission.recommended': 'موصى به',
      }),
      zh: Object.freeze({
        'core-ai-tool-permission.asks': '它本来就会询问您',
        'core-ai-tool-permission.asks_title': '此工具的作用就是向您提问。',
        'core-ai-tool-permission.recommended': '建议',
      }),
    }),
  }),
  render(input, helpers) {
    const { tool, writable } = input;
    const t = (key, fallback) => helpers.i18n.t(key, {}, fallback);
    const wrap = document.createDocumentFragment();
    if (tool.asks_by_design) {
      const text = document.createElement('span');
      text.className = 'muted small';
      text.textContent = t(
        'core-ai-tool-permission.asks',
        'it asks you anyway',
      );
      text.title = t(
        'core-ai-tool-permission.asks_title',
        'This tool exists to put a question to you. There is nothing it could do that you would want warning of.',
      );
      wrap.appendChild(text);
      return wrap;
    }
    const ask = document.createElement('input');
    ask.type = 'checkbox';
    ask.className = 'tool-ask';
    ask.dataset.tool = tool.name;
    ask.checked = tool.permission;
    ask.disabled = !writable;
    wrap.appendChild(ask);
    if (tool.recommends_permission) {
      const text = document.createElement('span');
      text.className = 'muted small';
      text.textContent = ` ${t('core-ai-tool-permission.recommended', 'recommended')}`;
      wrap.appendChild(text);
    }
    return wrap;
  },
};
