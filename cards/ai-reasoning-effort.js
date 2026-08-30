/* Core card package: provider reasoning-effort control. */
window.ZogographAiReasoningEffortCard = {
  manifest: Object.freeze({
    id: 'core-ai-reasoning-effort',
    locales: ['core-ai-reasoning-effort.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-reasoning-effort.label': 'How hard to think',
        'core-ai-reasoning-effort.help':
          'Reasoning models charge for thinking as output tokens. Higher is useful for knotty problems and wasted on simple questions.',
      }),
      es: Object.freeze({
        'core-ai-reasoning-effort.label': 'Cuánto razonar',
        'core-ai-reasoning-effort.help':
          'Los modelos de razonamiento cobran sus tokens de pensamiento como salida.',
      }),
      hi: Object.freeze({
        'core-ai-reasoning-effort.label': 'कितना सोचना है',
        'core-ai-reasoning-effort.help':
          'रीज़निंग मॉडल सोचने के आउटपुट टोकन का शुल्क लेते हैं।',
      }),
      ar: Object.freeze({
        'core-ai-reasoning-effort.label': 'مقدار التفكير',
        'core-ai-reasoning-effort.help':
          'تتقاضى نماذج الاستدلال تكلفة رموز التفكير كمخرجات.',
      }),
      zh: Object.freeze({
        'core-ai-reasoning-effort.label': '思考强度',
        'core-ai-reasoning-effort.help': '推理模型会将思考计为输出令牌。',
      }),
    }),
  }),
  render(input, helpers) {
    const { fieldRow, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    const effort = document.createElement('select');
    effort.id = 'aiEffort';
    for (const [value, label] of input.options) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = label;
      option.selected = (input.value || '') === value;
      effort.appendChild(option);
    }
    return fieldRow(
      t('core-ai-reasoning-effort.label', 'How hard to think'),
      effort,
      t(
        'core-ai-reasoning-effort.help',
        'Reasoning models charge for thinking as output tokens. Higher is useful for knotty problems and wasted on simple questions.',
      ),
    );
  },
};
