/* Core card package: saved-memory pin-state explanation. */
window.ZogographAiMemoryPinStateCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-pin-state',
    locales: ['core-ai-memory-pin-state.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-pin-state.pinned': 'Pinned — never dropped',
      }),
      es: Object.freeze({
        'core-ai-memory-pin-state.pinned': 'Fijado: nunca se descarta',
      }),
      hi: Object.freeze({
        'core-ai-memory-pin-state.pinned':
          'पिन किया गया — कभी नहीं हटाया जाएगा',
      }),
      ar: Object.freeze({
        'core-ai-memory-pin-state.pinned': 'مثبّت — لن يُسقط أبدًا',
      }),
      zh: Object.freeze({
        'core-ai-memory-pin-state.pinned': '已固定——永不丢弃',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      'core-ai-memory-pin-state.pinned',
      {},
      'Pinned — never dropped',
    );
  },
};
