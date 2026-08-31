/* Core card package: completion message after saving assistant settings. */
window.ZogographAiSettingsSavedCard = {
  manifest: Object.freeze({
    id: 'core-ai-settings-saved',
    locales: ['core-ai-settings-saved.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-ai-settings-saved.message': 'Saved.' }),
      es: Object.freeze({ 'core-ai-settings-saved.message': 'Guardado.' }),
      hi: Object.freeze({ 'core-ai-settings-saved.message': 'सहेजा गया।' }),
      ar: Object.freeze({ 'core-ai-settings-saved.message': 'حُفظ.' }),
      zh: Object.freeze({ 'core-ai-settings-saved.message': '已保存。' }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t('core-ai-settings-saved.message', {}, 'Saved.');
  },
};
