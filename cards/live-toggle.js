/* Core card package: label for the dashboard's live refresh control. */
window.ZogographLiveToggleCard = {
  manifest: Object.freeze({
    id: 'core-live-toggle',
    locales: ['core-live-toggle.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-live-toggle.label': 'Live' }),
      es: Object.freeze({ 'core-live-toggle.label': 'En vivo' }),
      hi: Object.freeze({ 'core-live-toggle.label': 'लाइव' }),
      ar: Object.freeze({ 'core-live-toggle.label': 'مباشر' }),
      zh: Object.freeze({ 'core-live-toggle.label': '实时' }),
    }),
  }),
  render(_input, helpers) {
    return helpers.i18n.t('core-live-toggle.label', {}, 'Live');
  },
};
