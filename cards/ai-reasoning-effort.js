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
        'core-ai-reasoning-effort.default': "the provider's default",
        'core-ai-reasoning-effort.minimal': 'minimal',
        'core-ai-reasoning-effort.low': 'low',
        'core-ai-reasoning-effort.medium': 'medium',
        'core-ai-reasoning-effort.high': 'high',
      }),
      es: Object.freeze({
        'core-ai-reasoning-effort.label': 'Cuánto razonar',
        'core-ai-reasoning-effort.help':
          'Los modelos de razonamiento cobran sus tokens de pensamiento como salida.',
        'core-ai-reasoning-effort.default':
          'el valor predeterminado del proveedor',
        'core-ai-reasoning-effort.minimal': 'mínimo',
        'core-ai-reasoning-effort.low': 'bajo',
        'core-ai-reasoning-effort.medium': 'medio',
        'core-ai-reasoning-effort.high': 'alto',
      }),
      hi: Object.freeze({
        'core-ai-reasoning-effort.label': 'कितना सोचना है',
        'core-ai-reasoning-effort.help':
          'रीज़निंग मॉडल सोचने के आउटपुट टोकन का शुल्क लेते हैं।',
        'core-ai-reasoning-effort.default': 'प्रदाता का डिफ़ॉल्ट',
        'core-ai-reasoning-effort.minimal': 'न्यूनतम',
        'core-ai-reasoning-effort.low': 'कम',
        'core-ai-reasoning-effort.medium': 'मध्यम',
        'core-ai-reasoning-effort.high': 'उच्च',
      }),
      ar: Object.freeze({
        'core-ai-reasoning-effort.label': 'مقدار التفكير',
        'core-ai-reasoning-effort.help':
          'تتقاضى نماذج الاستدلال تكلفة رموز التفكير كمخرجات.',
        'core-ai-reasoning-effort.default': 'إعداد الموفّر الافتراضي',
        'core-ai-reasoning-effort.minimal': 'ضئيل',
        'core-ai-reasoning-effort.low': 'منخفض',
        'core-ai-reasoning-effort.medium': 'متوسط',
        'core-ai-reasoning-effort.high': 'مرتفع',
      }),
      zh: Object.freeze({
        'core-ai-reasoning-effort.label': '思考强度',
        'core-ai-reasoning-effort.help': '推理模型会将思考计为输出令牌。',
        'core-ai-reasoning-effort.default': '提供商默认值',
        'core-ai-reasoning-effort.minimal': '最低',
        'core-ai-reasoning-effort.low': '低',
        'core-ai-reasoning-effort.medium': '中',
        'core-ai-reasoning-effort.high': '高',
      }),
    }),
  }),
  render(input, helpers) {
    const { fieldRow, i18n } = helpers;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    const effort = document.createElement('select');
    effort.id = 'aiEffort';
    for (const [value, key, fallback] of [
      ['', 'default', "the provider's default"],
      ['minimal', 'minimal', 'minimal'],
      ['low', 'low', 'low'],
      ['medium', 'medium', 'medium'],
      ['high', 'high', 'high'],
    ]) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = t(`core-ai-reasoning-effort.${key}`, fallback);
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
