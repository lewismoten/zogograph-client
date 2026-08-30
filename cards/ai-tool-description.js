/* Core card package: exact AI-tool description dialog control. */
window.ZogographAiToolDescriptionCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-description',
    locales: ['core-ai-tool-description.'],
    helpers: ['showListDialog', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-description.action': 'What it is told',
        'core-ai-tool-description.title':
          'The exact description the model is given for this tool',
        'core-ai-tool-description.help':
          'This is what the model reads before deciding whether to use it. The “do not use this when…” half is what stops a confident wrong call.',
      }),
      es: Object.freeze({
        'core-ai-tool-description.action': 'Lo que se le dice',
        'core-ai-tool-description.title':
          'La descripción exacta que recibe el modelo',
        'core-ai-tool-description.help':
          'Esto es lo que el modelo lee antes de decidir si usarla.',
      }),
      hi: Object.freeze({
        'core-ai-tool-description.action': 'इसे क्या बताया जाता है',
        'core-ai-tool-description.title': 'मॉडल को दिया गया सटीक विवरण',
        'core-ai-tool-description.help':
          'इस्तेमाल तय करने से पहले मॉडल इसे पढ़ता है।',
      }),
      ar: Object.freeze({
        'core-ai-tool-description.action': 'ما يُقال لها',
        'core-ai-tool-description.title': 'الوصف الدقيق المعطى للنموذج',
        'core-ai-tool-description.help':
          'هذا ما يقرأه النموذج قبل أن يقرر استخدام الأداة.',
      }),
      zh: Object.freeze({
        'core-ai-tool-description.action': '模型收到的说明',
        'core-ai-tool-description.title': '提供给模型的准确工具说明',
        'core-ai-tool-description.help':
          '模型会在决定是否使用工具前阅读此说明。',
      }),
    }),
  }),
  render(input, helpers) {
    const { showListDialog, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'link';
    button.textContent = t(
      'core-ai-tool-description.action',
      'What it is told',
    );
    button.title = t(
      'core-ai-tool-description.title',
      'The exact description the model is given for this tool',
    );
    button.addEventListener('click', () => {
      const pre = document.createElement('pre');
      pre.className = 'paste';
      pre.textContent = input.tool.description;
      showListDialog(
        input.tool.name,
        [pre],
        t(
          'core-ai-tool-description.help',
          'This is what the model reads before deciding whether to use it. The “do not use this when…” half is what stops a confident wrong call.',
        ),
      );
    });
    return button;
  },
};
