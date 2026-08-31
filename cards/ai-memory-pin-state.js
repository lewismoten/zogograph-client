/* Core card package: saved-memory pin-state explanation. */
window.ZogographAiMemoryPinStateCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-pin-state',
    locales: ['core-ai-memory-pin-state.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-pin-state.pinned': 'Pinned — never dropped',
        'core-ai-memory-pin-state.pin': 'Pin it',
      }),
      es: Object.freeze({
        'core-ai-memory-pin-state.pinned': 'Fijado: nunca se descarta',
        'core-ai-memory-pin-state.pin': 'Fijarla',
      }),
      hi: Object.freeze({
        'core-ai-memory-pin-state.pinned':
          'पिन किया गया — कभी नहीं हटाया जाएगा',
        'core-ai-memory-pin-state.pin': 'पिन करें',
      }),
      ar: Object.freeze({
        'core-ai-memory-pin-state.pinned': 'مثبّت — لن يُسقط أبدًا',
        'core-ai-memory-pin-state.pin': 'ثبّتها',
      }),
      zh: Object.freeze({
        'core-ai-memory-pin-state.pinned': '已固定——永不丢弃',
        'core-ai-memory-pin-state.pin': '置顶',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-memory-pin-state.${input.key || 'pinned'}`,
      {},
      this.manifest.catalogs.en[
        `core-ai-memory-pin-state.${input.key || 'pinned'}`
      ],
    );
  },
};
