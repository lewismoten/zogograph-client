/* Core card package: enable or disable the configured AI assistant. */
window.ZogographAiEnableToggleCard = {
  manifest: Object.freeze({
    id: 'core-ai-enable-toggle',
    locales: ['core-ai-enable-toggle.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-enable-toggle.label': 'Use {{assistant}}',
        'core-ai-enable-toggle.help':
          'Off means {{assistant}} is unavailable and nothing is ever sent anywhere. Turning it on does not send anything by itself — only asking it something does.',
      }),
      es: Object.freeze({
        'core-ai-enable-toggle.label': 'Usar {{assistant}}',
        'core-ai-enable-toggle.help':
          'Al desactivarlo, {{assistant}} no estará disponible y no se enviará nada. Activarlo por sí solo no envía nada.',
      }),
      hi: Object.freeze({
        'core-ai-enable-toggle.label': '{{assistant}} का उपयोग करें',
        'core-ai-enable-toggle.help':
          'बंद होने पर {{assistant}} उपलब्ध नहीं है और कुछ भी कहीं नहीं भेजा जाता। इसे चालू करना अपने आप कुछ नहीं भेजता।',
      }),
      ar: Object.freeze({
        'core-ai-enable-toggle.label': 'استخدم {{assistant}}',
        'core-ai-enable-toggle.help':
          'عند إيقافه لا يكون {{assistant}} متاحًا ولا يُرسل شيء إلى أي مكان. تشغيله لا يرسل شيئًا بمفرده.',
      }),
      zh: Object.freeze({
        'core-ai-enable-toggle.label': '使用 {{assistant}}',
        'core-ai-enable-toggle.help':
          '关闭后，{{assistant}} 不可用，且不会向任何位置发送内容。开启本身不会发送任何内容。',
      }),
    }),
  }),
  render(input, helpers) {
    const { fieldRow, i18n } = helpers;
    const t = (key, fallback) =>
      i18n.t(key, { assistant: input.assistant }, fallback);
    const box = document.createElement('input');
    box.type = 'checkbox';
    box.id = 'aiEnabled';
    box.checked = input.enabled;
    return fieldRow(
      t('core-ai-enable-toggle.label', `Use ${input.assistant}`),
      box,
      t(
        'core-ai-enable-toggle.help',
        `Off means ${input.assistant} is unavailable and nothing is ever sent anywhere. Turning it on does not send anything by itself — only asking it something does.`,
      ),
    );
  },
};
