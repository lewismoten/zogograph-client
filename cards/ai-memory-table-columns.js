/* Core card package: descriptive column headings for saved memories. */
window.ZogographAiMemoryTableColumnsCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-table-columns',
    locales: ['core-ai-memory-table-columns.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-table-columns.kind': 'Kind',
        'core-ai-memory-table-columns.about': 'About',
        'core-ai-memory-table-columns.remembers': 'What it remembers',
        'core-ai-memory-table-columns.written': 'Written',
      }),
      es: Object.freeze({
        'core-ai-memory-table-columns.kind': 'Tipo',
        'core-ai-memory-table-columns.about': 'Acerca de',
        'core-ai-memory-table-columns.remembers': 'Qué recuerda',
        'core-ai-memory-table-columns.written': 'Escrito',
      }),
      hi: Object.freeze({
        'core-ai-memory-table-columns.kind': 'प्रकार',
        'core-ai-memory-table-columns.about': 'विषय',
        'core-ai-memory-table-columns.remembers': 'यह क्या याद रखता है',
        'core-ai-memory-table-columns.written': 'लिखा गया',
      }),
      ar: Object.freeze({
        'core-ai-memory-table-columns.kind': 'النوع',
        'core-ai-memory-table-columns.about': 'حول',
        'core-ai-memory-table-columns.remembers': 'ما يتذكره',
        'core-ai-memory-table-columns.written': 'كُتب',
      }),
      zh: Object.freeze({
        'core-ai-memory-table-columns.kind': '类型',
        'core-ai-memory-table-columns.about': '关于',
        'core-ai-memory-table-columns.remembers': '记住的内容',
        'core-ai-memory-table-columns.written': '写入时间',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-memory-table-columns.${input.key}`,
      {},
      input.fallback,
    );
  },
};
