/* Core card package: saved-memory provenance and usage table cells. */
window.ZogographAiMemoryProvenanceCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-provenance',
    locales: ['core-ai-memory-provenance.'],
    helpers: ['ago', 'humanCount', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-provenance.you_wrote': 'You wrote this.',
        'core-ai-memory-provenance.in_conversation':
          'Written by the assistant in conversation {{id}}.',
        'core-ai-memory-provenance.by_assistant': 'Written by the assistant.',
        'core-ai-memory-provenance.used': 'Used',
        'core-ai-memory-provenance.never': 'never',
      }),
      es: Object.freeze({
        'core-ai-memory-provenance.you_wrote': 'Usted escribió esto.',
        'core-ai-memory-provenance.in_conversation':
          'Lo escribió el asistente en la conversación {{id}}.',
        'core-ai-memory-provenance.by_assistant': 'Lo escribió el asistente.',
        'core-ai-memory-provenance.used': 'Usado',
        'core-ai-memory-provenance.never': 'nunca',
      }),
      hi: Object.freeze({
        'core-ai-memory-provenance.you_wrote': 'आपने इसे लिखा था।',
        'core-ai-memory-provenance.in_conversation':
          'इसे सहायक ने बातचीत {{id}} में लिखा था।',
        'core-ai-memory-provenance.by_assistant': 'इसे सहायक ने लिखा था।',
        'core-ai-memory-provenance.used': 'उपयोग',
        'core-ai-memory-provenance.never': 'कभी नहीं',
      }),
      ar: Object.freeze({
        'core-ai-memory-provenance.you_wrote': 'لقد كتبت هذا.',
        'core-ai-memory-provenance.in_conversation':
          'كتبه المساعد في المحادثة {{id}}.',
        'core-ai-memory-provenance.by_assistant': 'كتبه المساعد.',
        'core-ai-memory-provenance.used': 'استُخدم',
        'core-ai-memory-provenance.never': 'أبدًا',
      }),
      zh: Object.freeze({
        'core-ai-memory-provenance.you_wrote': '这是您写的。',
        'core-ai-memory-provenance.in_conversation':
          '这是助手在对话 {{id}} 中写的。',
        'core-ai-memory-provenance.by_assistant': '这是助手写的。',
        'core-ai-memory-provenance.used': '使用次数',
        'core-ai-memory-provenance.never': '从未',
      }),
    }),
  }),
  render(input, helpers) {
    const t = (key, values, fallback) =>
      helpers.i18n.t(`core-ai-memory-provenance.${key}`, values, fallback);
    if (input.kind === 'written') {
      const span = document.createElement('span');
      span.textContent = helpers.ago(input.row.updated_at);
      span.title =
        input.row.written_by === 'you'
          ? t('you_wrote', {}, 'You wrote this.')
          : input.row.source_session
            ? t(
                'in_conversation',
                { id: input.row.source_session },
                'Written by the assistant in conversation {{id}}.',
              )
            : t('by_assistant', {}, 'Written by the assistant.');
      return span;
    }
    return input.kind === 'used-label'
      ? t('used', {}, 'Used')
      : input.row.used
        ? `${helpers.humanCount(input.row.used)}×`
        : t('never', {}, 'never');
  },
};
