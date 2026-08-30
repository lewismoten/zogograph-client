/* Core card package: warning that an enabled tool is not offered to the model. */
window.ZogographAiToolAvailabilityCard = {
  manifest: Object.freeze({
    id: 'core-ai-tool-availability',
    locales: ['core-ai-tool-availability.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-tool-availability.not_in_use': 'not in use',
      }),
      es: Object.freeze({
        'core-ai-tool-availability.not_in_use': 'no está en uso',
      }),
      hi: Object.freeze({
        'core-ai-tool-availability.not_in_use': 'उपयोग में नहीं',
      }),
      ar: Object.freeze({
        'core-ai-tool-availability.not_in_use': 'غير مستخدمة',
      }),
      zh: Object.freeze({ 'core-ai-tool-availability.not_in_use': '未在使用' }),
    }),
  }),
  render(input, helpers) {
    const warning = document.createElement('div');
    warning.className = 'band-warn small';
    warning.textContent = helpers.i18n.t(
      'core-ai-tool-availability.not_in_use',
      {},
      'not in use',
    );
    warning.title = input.reason;
    return warning;
  },
};
