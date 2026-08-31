/* Core card package: section wrapper for conversation context settings. */
window.ZogographAiContextSectionCard = {
  manifest: Object.freeze({
    id: 'core-ai-context-section',
    locales: ['core-ai-context-section.'],
    helpers: ['section', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-context-section.title': 'How much it can hold',
      }),
      es: Object.freeze({
        'core-ai-context-section.title': 'Cuánto puede contener',
      }),
      hi: Object.freeze({
        'core-ai-context-section.title': 'यह कितना रख सकता है',
      }),
      ar: Object.freeze({
        'core-ai-context-section.title': 'مقدار ما يستطيع الاحتفاظ به',
      }),
      zh: Object.freeze({
        'core-ai-context-section.title': '它能容纳多少内容',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.section(
      helpers.i18n.t(
        'core-ai-context-section.title',
        {},
        'How much it can hold',
      ),
      input.content,
    );
  },
};
