/* Core card package: headings for primary assistant settings pages. */
window.ZogographAiSettingsPageHeadingCard = {
  manifest: Object.freeze({
    id: 'core-ai-settings-page-heading',
    locales: ['core-ai-settings-page-heading.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-settings-page-heading.identity': 'Identity',
        'core-ai-settings-page-heading.provider': 'Provider connection',
        'core-ai-settings-page-heading.model': 'Which model',
      }),
      es: Object.freeze({
        'core-ai-settings-page-heading.identity': 'Identidad',
        'core-ai-settings-page-heading.provider': 'Conexión del proveedor',
        'core-ai-settings-page-heading.model': 'Qué modelo',
      }),
      hi: Object.freeze({
        'core-ai-settings-page-heading.identity': 'पहचान',
        'core-ai-settings-page-heading.provider': 'प्रदाता कनेक्शन',
        'core-ai-settings-page-heading.model': 'कौन सा मॉडल',
      }),
      ar: Object.freeze({
        'core-ai-settings-page-heading.identity': 'الهوية',
        'core-ai-settings-page-heading.provider': 'اتصال المزوّد',
        'core-ai-settings-page-heading.model': 'أي نموذج',
      }),
      zh: Object.freeze({
        'core-ai-settings-page-heading.identity': '身份',
        'core-ai-settings-page-heading.provider': '提供商连接',
        'core-ai-settings-page-heading.model': '选择哪个模型',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-settings-page-heading.${input.page}`,
      {},
      input.fallback,
    );
  },
};
