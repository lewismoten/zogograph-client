/* Core card package: field labels for saved-memory editor. */
window.ZogographAiMemoryEditorLabelsCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-editor-labels',
    locales: ['core-ai-memory-editor-labels.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-editor-labels.kind': 'Kind',
        'core-ai-memory-editor-labels.about': 'About',
        'core-ai-memory-editor-labels.remembers': 'What it remembers',
      }),
      es: Object.freeze({
        'core-ai-memory-editor-labels.kind': 'Tipo',
        'core-ai-memory-editor-labels.about': 'Acerca de',
        'core-ai-memory-editor-labels.remembers': 'Qué recuerda',
      }),
      hi: Object.freeze({
        'core-ai-memory-editor-labels.kind': 'प्रकार',
        'core-ai-memory-editor-labels.about': 'विषय',
        'core-ai-memory-editor-labels.remembers': 'यह क्या याद रखता है',
      }),
      ar: Object.freeze({
        'core-ai-memory-editor-labels.kind': 'النوع',
        'core-ai-memory-editor-labels.about': 'حول',
        'core-ai-memory-editor-labels.remembers': 'ما يتذكره',
      }),
      zh: Object.freeze({
        'core-ai-memory-editor-labels.kind': '类型',
        'core-ai-memory-editor-labels.about': '关于',
        'core-ai-memory-editor-labels.remembers': '记住的内容',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-memory-editor-labels.${input.key}`,
      {},
      input.fallback,
    );
  },
};
