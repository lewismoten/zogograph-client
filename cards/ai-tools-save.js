/* Core card package: primary save action for AI tool settings. */
window.ZogographAiToolsSaveCard = {
  manifest: Object.freeze({
    id: 'core-ai-tools-save',
    locales: ['core-ai-tools-save.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tools-save.action': 'Save these settings',
        'core-ai-tools-save.help':
          "Saves everything on all of {{assistant}}'s pages, including the tools on the other tabs.",
      }),
      es: Object.freeze({
        'core-ai-tools-save.action': 'Guardar estos ajustes',
        'core-ai-tools-save.help':
          'Guarda todo en las páginas de {{assistant}}, incluidas las herramientas.',
      }),
      hi: Object.freeze({
        'core-ai-tools-save.action': 'ये सेटिंग सहेजें',
        'core-ai-tools-save.help':
          '{{assistant}} के सभी पृष्ठों की सेटिंग सहेजता है।',
      }),
      ar: Object.freeze({
        'core-ai-tools-save.action': 'احفظ هذه الإعدادات',
        'core-ai-tools-save.help':
          'يحفظ كل إعدادات صفحات {{assistant}}، بما فيها الأدوات.',
      }),
      zh: Object.freeze({
        'core-ai-tools-save.action': '保存这些设置',
        'core-ai-tools-save.help':
          '保存 {{assistant}} 所有页面上的设置，包括其他标签页中的工具。',
      }),
    }),
  }),
  render(input, helpers) {
    const t = (key, vars, fallback) => helpers.i18n.t(key, vars, fallback);
    const save = document.createElement('button');
    save.type = 'submit';
    save.disabled = !input.writable;
    save.textContent = t(
      'core-ai-tools-save.action',
      {},
      'Save these settings',
    );
    const help = document.createElement('span');
    help.className = 'muted small';
    help.textContent = t(
      'core-ai-tools-save.help',
      { assistant: input.assistant },
      "Saves everything on all of {{assistant}}'s pages, including the tools on the other tabs.",
    );
    return [save, help];
  },
};
