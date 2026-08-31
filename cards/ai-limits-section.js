/* Core card package: heading for conversation and shared assistant limits. */
window.ZogographAiLimitsSectionCard = {
  manifest: Object.freeze({
    id: 'core-ai-limits-section',
    locales: ['core-ai-limits-section.'],
    helpers: ['section', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-limits-section.title': 'Conversation and shared limits',
      }),
      es: Object.freeze({
        'core-ai-limits-section.title': 'Límites de conversación y compartidos',
      }),
      hi: Object.freeze({
        'core-ai-limits-section.title': 'बातचीत और साझा सीमाएँ',
      }),
      ar: Object.freeze({
        'core-ai-limits-section.title': 'حدود المحادثة والحدود المشتركة',
      }),
      zh: Object.freeze({ 'core-ai-limits-section.title': '对话和共享限制' }),
    }),
  }),
  render(input, helpers) {
    return helpers.section(
      helpers.i18n.t(
        'core-ai-limits-section.title',
        {},
        'Conversation and shared limits',
      ),
      input.content,
    );
  },
};
