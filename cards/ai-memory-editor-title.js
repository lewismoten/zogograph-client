/* Core card package: title for adding or editing a saved assistant memory. */
window.ZogographAiMemoryEditorTitleCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-editor-title',
    locales: ['core-ai-memory-editor-title.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-editor-title.edit': 'Edit a memory',
        'core-ai-memory-editor-title.write': 'Write a memory',
      }),
      es: Object.freeze({
        'core-ai-memory-editor-title.edit': 'Editar una memoria',
        'core-ai-memory-editor-title.write': 'Escribir una memoria',
      }),
      hi: Object.freeze({
        'core-ai-memory-editor-title.edit': 'मेमोरी संपादित करें',
        'core-ai-memory-editor-title.write': 'मेमोरी लिखें',
      }),
      ar: Object.freeze({
        'core-ai-memory-editor-title.edit': 'تحرير ذاكرة',
        'core-ai-memory-editor-title.write': 'كتابة ذاكرة',
      }),
      zh: Object.freeze({
        'core-ai-memory-editor-title.edit': '编辑一条记忆',
        'core-ai-memory-editor-title.write': '写一条记忆',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-memory-editor-title.${input.editing ? 'edit' : 'write'}`,
      {},
      input.editing ? 'Edit a memory' : 'Write a memory',
    );
  },
};
